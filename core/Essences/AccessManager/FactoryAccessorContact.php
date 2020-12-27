<?php

    namespace Essences\AccessManager;

    use Exception\ExceptionRouter;

    final class FactoryAccessorContact
    {

        public static function build(string $type): InterfaceAccessManager
        {
            switch($type)
            {
                case 'Аренда':
                    return new AccessManagerRenterMin;
                case 'Продажа':
                    return new AccessManagerBuyerMin;
            }

            throw new ExceptionRouter;
        }
    }