<?php

    namespace Essences\AccessManager;

    use Exception\ExceptionRouter;

    final class FactoryAccessorPay
    {

        private const RENTER = 1;
        private const BUYER = 2;
        private const OWNER_RENT = 3;
        private const OWNER_BUY = 4;

        public static function build(int $rleId): InterfaceAccessManager
        {
            switch($rleId)
            {
                case self::RENTER:
                    return new AccessManagerRenterMin;
                case self::BUYER:
                    return new AccessManagerBuyerMin;
                case self::OWNER_RENT:
                    return new AccessManagerOwnerRentMin;
                case self::OWNER_BUY:
                    return new AccessManagerOwnerBuyMin;
            }

            throw new ExceptionRouter;
        }
    }