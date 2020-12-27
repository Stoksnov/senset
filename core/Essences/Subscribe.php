<?php

    namespace Essences;

    use Exception\ExceptionUpdateDB;

    final class Subscribe
    {

        private $id;
        private $tariff;
        private $dateBegin;
        private $dateEnd;

        private function __construct(int $id, Tariff $tariff, string $dateBegin, string $dateEnd)
        {
            $this->id = $id;
            $this->tariff = $tariff;
            $this->dateBegin = $dateBegin;
            $this->dateEnd = $dateEnd;
        }

        public static function getInstanceActive(int $userId): ?array
        {
            $subscribesActive = [];
            $subscribes = static::getActiveSubscribes($userId);

            foreach($subscribes as $subscribe)
            {
                $tariff = \R::findOne('zaselite_tariffs', 'ID = ?', [$subscribe['tariff_id']]);

                $subscribesActive[] = new self($subscribe['ID'], new Tariff($tariff->roleId, $tariff->type, $tariff->name, $tariff->price, $subscribe['tariff_id']), $subscribe['date_begin'], $subscribe['date_end']);
            }

            return $subscribesActive;
        }

        private static function getActiveSubscribes(int $userId): ?array
        {
            $subscribe = \R::find('zaselite_subscribers', 'user_id = ? AND date_end > ?', [$userId, \R::isoDateTime()]);

            return $subscribe;
        }

        public static function getInstanceStories(int $userId): array
        {
            $stories = \R::findOne('zaselite_stories', 'user_id = ?', [$userId]);

            if(empty($stories))
            {
                return [];
            }

            $result = [];

            foreach($stories as $story)
            {
                $tariff = \R::findOne('zaselite_tariffs', 'ID = ?', [$story->tariff_id]);

                $result[] = new self(0, new Tariff($tariff->roleId, $tariff->type, $tariff->name, $tariff->price, $story->tariff_id), $story->date_pay, '');
            }

            return $result;
        }

        public static function saveDB(int $userId, Tariff $tariff): void
        {
            $subscribes = static::getActiveSubscribes($userId);

            foreach($subscribes as $subscribe)
            {
                $activeTariff = $subscribe->getTariff();

                if($activeTariff->getRoleId == $tariff->getRoleId() && $activeTariff->getType() != $tariff->getType())
                {
                    static::deleteOldDB($userId, $activeTariff->getId());
                    static::insertDB($userId, $tariff->getId());
                    return;
                }
                elseif($activeTariff->getRoleId == $tariff->getRoleId() && $activeTariff->getType() == $tariff->getType())
                {
                    static::updateDB($subscribe);
                    return;
                }
            }
        }

        private static function deleteOldDB(int $userId, int $tariffId): void
        {
            \R::exec('DELETE FROM zaselite_subscribers WHERE user_id = ? AND tariff_id = ?', [$userId, $tariffId]);
        }

        private static function insertDB(int $userId, int $tariffId): void
        {
            $subscribe = \R::xdispense('zaselite_subscribers');

            $subscribe->user_id = $userId;
            $subscribe->tariff_id = $tariffId;

            $time = time();
            $subscribe->date_begin = \R::isoDateTime($time);
            $subscribe->date_end = \R::isoDateTime($time + 30 * 24 * 60 * 60);

            $id = \R::store($subscribe);

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        private static function updateDB(array $subscribe): void
        {
            $subscribe['date_end'] = \R::isoDateTime(strtotime($subscribe['date_end']) + 30 * 24 * 60 * 60);

            $id = \R::store(\R::convertToBean('zaselite_subscribers', $subscribe));

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        public static function insertStoryDB(int $userId, int $tariffId): void
        {
            $story = \R::xdispense('zaselite_stories');

            $story->user_id = $userId;
            $story->tariff_id = $tariffId;
            $story->date_pay = \R::isoDateTime();

            $id = \R::store($story);

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        public function getId(): int
        {
            return $this->id;
        }

        public function getTariff(): Tariff
        {
            return $this->tariff;
        }

        public function getDateBegin(): string
        {
            return $this->dateBegin;
        }

        public function getDateEnd(): string
        {
            return $this->dateEnd;
        }

        private function __clone()
        {

        }
    }