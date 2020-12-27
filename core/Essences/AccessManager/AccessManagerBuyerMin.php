<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerBuyerMin extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::BUYER, self::TYPE_MIN);
        }
    }