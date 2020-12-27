<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerRenterMin extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::RENTER, self::TYPE_MIN);
        }
    }