<?php

    namespace Model\AJAX;

    use Essences\DirectorObject\DirectorObjectList;
    use Essences\ObjectEstate;
    use Exception\ExceptionValidation;

    final class ModelMapObjects extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $objects = $this->getObjectsDB();

            $this->generateInformationObjects($objects);
        }

        private function getObjectsDB(): array
        {
            $objects = \R::getAll('SELECT * FROM crm_objects WHERE ID IN ('.$this->paramsInput['objectId'].')');

            if(empty($objects))
            {
                throw new ExceptionValidation('Неверный параметр objectId');
            }

            return $objects;
        }

        private function generateInformationObjects(array &$objects): void
        {
            foreach($objects as &$object)
            {
                $object = ObjectEstate::getInstanceData($object);

                $director = new DirectorObjectList($object);
                $director->change();
            }

            $this->paramsOutput['objects'] = $objects;
        }
    }