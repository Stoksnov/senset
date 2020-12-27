<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerRenterMiddle extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::RENTER, self::TYPE_MIDDLE);
        }
    }