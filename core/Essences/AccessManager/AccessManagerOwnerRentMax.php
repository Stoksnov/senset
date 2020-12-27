<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerOwnerRentMax extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::OWNER_RENT, self::TYPE_MAX);
        }
    }