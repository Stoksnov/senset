<?php

    namespace Model\Page;

    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\AccessManager\AccessManagerRenterMin;
    use Essences\AccessManager\FactoryAccessorPay;
    use Essences\DirectorObject\DirectorObjectList;
    use Essences\Filter\FilterEstates;
    use Essences\ObjectEstate;
    use Essences\User;

    final class ModelFind extends AbstractModelPage
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->generateCheckedFilter();
            $this->generateInformationObjects();
            $this->generateIsPayed();
        }

        private function generateCheckedFilter(): void
        {
            $this->paramsOutput = array_merge($this->paramsInput, $this->paramsOutput);

            $params = ['district', 'rooms'];

            foreach ($params as $param)
            {
                if(!array_key_exists($param, $this->paramsInput))
                {
                    $this->paramsOutput[$param] = [];
                }
            }
        }

        private function generateInformationObjects(): void
        {
            $filter = new FilterEstates($this->paramsInput);
            $filter->run();

            $objects = $filter->getLimit(0, 36);

            foreach($objects as &$object)
            {
                $object = ObjectEstate::getInstanceData($object);

                $director = new DirectorObjectList($object);
                $director->change();
            }

            $this->paramsOutput['objects'] = $objects;
            $this->paramsOutput['countSearched'] = $filter->getCount();
            $this->paramsOutput['countViewed'] = 0;
        }

        private function generateIsPayed(): void
        {
            $accessor = new AccessManagerAuthorization;

            if($accessor->isCheck())
            {
                $accessor = FactoryAccessorPay::build(User::getInstance()->getRoleId());
            }

            $this->paramsOutput['payed'] = $accessor->isCheck();
        }
    }