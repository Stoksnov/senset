<?php

    namespace Model\AJAX;

    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\AccessManager\FactoryAccessorPay;
    use Essences\DirectorObject\DirectorObjectList;
    use Essences\Filter\FilterEstates;
    use Essences\ObjectEstate;
    use Essences\User;

    final class ModelFind extends AbstractModelAJAX
    {

        private const COUNT_VIEW = 36;

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->generateInformationObjects();
            $this->generateIsPayed();
        }

        private function generateInformationObjects(): void
        {
            $filter = new FilterEstates($this->paramsInput);
            $filter->run();

            $objects = $filter->getLimit($this->paramsInput['countViewed'], self::COUNT_VIEW);

            foreach($objects as &$object)
            {
                $object = ObjectEstate::getInstanceData($object);

                $director = new DirectorObjectList($object);
                $director->change();
            }

            $this->paramsOutput['objects'] = $objects;
            $this->paramsOutput['countSearched'] = $filter->getCount();
            $this->paramsOutput['countViewed'] = $this->paramsInput['countViewed'];
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