<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerOwnerBuyMiddle extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::OWNER_BUY, self::TYPE_MIDDLE);
        }
    }