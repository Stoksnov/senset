<?php

    namespace Model\AJAX;

    use Essences\User;

    final class ModelFavorite extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->updateDB();
        }

        private function updateDB(): void
        {
            $userId = User::getInstance()->getId();
            $objectId = $this->paramsInput['objectId'];

            if(\R::count('zaselite_favorites', 'user_id = ? AND object_id = ?', [$userId, $objectId]) == 0)
            {
                \R::exec('INSERT INTO zaselite_favorites (user_id, object_id) VALUES (?, ?)', [$userId, $objectId]);
            }
            else
            {
                \R::exec('DELETE FROM zaselite_favorites WHERE user_id = ? AND object_id = ?', [$userId, $objectId]);
            }
        }
    }