<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerBuyerMax extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::BUYER, self::TYPE_MAX);
        }
    }