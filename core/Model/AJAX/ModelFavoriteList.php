<?php

    namespace Model\AJAX;

    use Essences\AccessManager\AccessManagerRenterMin;
    use Essences\DirectorObject\DirectorObjectList;
    use Essences\Filter\FilterEstates;
    use Essences\ObjectEstate;
    use Essences\User;

    final class ModelFavoriteList extends AbstractModelAJAX
    {

        private const COUNT_VIEW = 36;

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->generateInformationObjects();
        }

        private function generateInformationObjects(): void
        {
            $objects = $this->getObjectsDB();

            foreach($objects as &$object)
            {
                $object = ObjectEstate::getInstanceData($object);

                $director = new DirectorObjectList($object);
                $director->change();

                $object->setOption('favorite', true);
            }

            $this->paramsOutput['objects'] = $objects;
            $this->paramsOutput['countViewed'] = $this->paramsInput['countViewed'];
        }

        private function getObjectsDB(): array
        {
            $objectsId = $this->getObjectsId();

            if(empty($objectsId))
            {
                return [];
            }

            return $this->getObjectsInfo($objectsId);
        }

        private function getObjectsId(): array
        {
            return \R::getCol('SELECT object_id FROM zaselite_favorites WHERE user_id = ? LIMIT ?, ?',
                [User::getInstance()->getId(), $this->paramsInput['countViewed'], self::COUNT_VIEW]);
        }

        private function getObjectsInfo(array $objectId): array
        {
            return \R::getAll('SELECT * FROM crm_objects WHERE ID IN ('.implode(', ', $objectId).')');
        }
    }