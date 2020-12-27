<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerOwnerRentMin extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::OWNER_RENT, self::TYPE_MIN);
        }
    }