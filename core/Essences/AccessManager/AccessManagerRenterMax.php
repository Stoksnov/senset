<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerRenterMax extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::RENTER, self::TYPE_MAX);
        }
    }