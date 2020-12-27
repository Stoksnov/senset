<?php

    namespace Model\AJAX;

    use Essences\DirectorObject\DirectorObjectList;
    use Essences\ObjectEstate;
    use Essences\User;

    final class ModelHistoryViews extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $objectsId = $this->getObjectsId();
            $objects = $this->getObjectsDB($objectsId);

            $this->generateInformationObjects($objects);
        }

        private function getObjectsId(): array
        {
            $where = $this->getSQLWhere();

            return \R::getCol("SELECT object_id FROM zaselite_views WHERE {$where['text']}",  $where['bindings']);
        }

        private function getSQLWhere(): array
        {
            $bindings = [];

            $text = "user_id = ?";
            $bindings[] = User::getInstance()->getId();

            $day = $this->paramsInput['day'];

            if($day == 'today')
            {
                $text .= ' AND DATE(date) >= ?';
                $bindings[] = \R::isoDate();
            }
            elseif($day == 'yesterday')
            {
                $text .= ' AND DATE(date) < ?';
                $bindings[] = \R::isoDate();

                $text .= ' AND DATE(date) >= ?';
                $bindings[] = \R::isoDate(time() - 24 * 60 * 60);
            }

            return ['text' => $text, 'bindings' => $bindings];
        }

        private function getObjectsDB(array &$objectsId): array
        {
            if(empty($objectsId))
            {
                return [];
            }

            return \R::getAll('SELECT * FROM crm_objects WHERE ID IN ('.implode(', ', $objectsId).')');
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