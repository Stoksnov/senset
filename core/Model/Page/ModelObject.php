<?php

    namespace Model\Page;

    use Essences\DirectorObject\DirectorObjectList;
    use Essences\DirectorObject\DirectorObjectPage;
    use Essences\ObjectEstate;
    use Essences\User;
    use Exception\ExceptionUpdateDB;

    final class ModelObject extends AbstractModelPage
    {

        private $object;

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->generateInformationObject();
            $this->generateLastCommentModerator();
            $this->generateSimilarObjects();

            if(User::getInstance()->getAuth())
            {
                $this->saveViews();
            }
        }

        private function generateInformationObject(): void
        {
            $objectId = $this->paramsInput[1];

            $object = ObjectEstate::getInstanceId($objectId);
            $this->object = $object;

            $director = new DirectorObjectPage($object);
            $director->change();

            $this->paramsOutput['object'] = $object;
        }

        private function generateLastCommentModerator(): void
        {
            $objectId = $this->object->getProperty('ID');
            $comment = \R::getRow("SELECT * FROM moderators_comments WHERE object_id = '".$objectId."' ORDER BY ID DESC LIMIT 1");

            if(empty($comment))
            {
                return;
            }

            $comment['date_add'] = date('d.m.Y H:i', strtotime($comment['date_add']));

            $this->paramsOutput['comment'] = $comment;
        }

        private function generateSimilarObjects(): void
        {
            $objects = $this->getSimilarObjectsDB();

            foreach($objects as &$object)
            {
                $object = ObjectEstate::getInstanceData($object);

                $director = new DirectorObjectList($object);
                $director->change();
            }

            $this->paramsOutput['objects'] = $objects;
        }

        private function getSimilarObjectsDB(): array
        {
            $params = [$this->object->getProperty('city'), $this->object->getProperty('type'), $this->object->getProperty('rooms')];
            return \R::getAll("SELECT * FROM crm_objects WHERE city = ? AND type = ? AND rooms = ? LIMIT 0, 3", $params);
        }

        private function saveViews(): void
        {
            $objectId = $this->object->getProperty('ID');
            $userId = User::getInstance()->getId();

            if(\R::count('zaselite_views', 'user_id = ? AND object_id = ?', [$userId, $objectId]) == 0)
            {
                $this->insertViewDB();
            }
            else
            {
                $this->updateViewDB();
            }
        }

        private function insertViewDB(): void
        {
            $view = \R::xdispense('zaselite_views');

            $view->user_id = User::getInstance()->getId();
            $view->object_id = $this->object->getProperty('ID');
            $view->date = \R::isoDateTime();

            $id = \R::store($view);

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        private function updateViewDB(): void
        {
            $objectId = $this->object->getProperty('ID');
            $userId = User::getInstance()->getId();

            $view = \R::getRow('SELECT * FROM zaselite_views WHERE user_id = ? AND object_id = ?', [$userId, $objectId]);

            $view['date'] = \R::isoDateTime();

            $id = \R::store(\R::convertToBean('zaselite_views', $view));

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }
    }