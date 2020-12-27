<?php

    namespace Essences\GeneratorStatuses;

    use Essences\ObjectEstate;

    final class AbstractGeneratorStatusesBots extends AbstractGeneratorStatuses
    {

        private const RECALL = 4;
        private const NOCALL = 8;

        public function run(ObjectEstate $object): void
        {
            $state = $object->getProperty('state');

            if($state == self::RECALL)
            {
                $globalStatus = 'На проверке оператором';
            }
            elseif($state == self::NOCALL)
            {
                $globalStatus = 'На проверке ботом';
            }
            else
            {
                parent::next($object);
                return;
            }

            $object->setOption('globalStatus', $globalStatus);
        }
    }