<?php

    namespace Essences\GeneratorStatuses;

    use Essences\ObjectEstate;

    final class AbstractGeneratorStatusesCheck extends AbstractGeneratorStatuses
    {

        private const ACTUAL = 1;
        private const COOPERATE = 2;

        public function run(ObjectEstate $object): void
        {
            $state = $object->getProperty('state');

            if($state == self::ACTUAL || $state == self::COOPERATE)
            {
                $globalStatus = 'Проверено';
                $date = 'date_check';
            }
            else
            {
                $globalStatus = 'Добавлено';
                $date = 'date_add';
            }

            $object->setOption('globalStatus', $globalStatus);
            $object->setOption('daysHavePassed', $this->daysHavePassed($object->getProperty($date)));
        }


        private function daysHavePassed(string $date): int
        {
            $now = time();
            $date = strtotime($date);

            return (int) floor(($now - $date) / 86400);
        }
    }