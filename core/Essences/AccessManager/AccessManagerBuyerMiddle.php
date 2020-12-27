<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerBuyerMiddle extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::BUYER, self::TYPE_MIDDLE);
        }
    }