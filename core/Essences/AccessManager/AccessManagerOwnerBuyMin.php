<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;

    final class AccessManagerOwnerBuyMin extends AbstractAccessManagerPay
    {

        protected function getTariff(): Tariff
        {
            return new Tariff(self::OWNER_BUY, self::TYPE_MIN);
        }
    }