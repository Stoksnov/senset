<?php

    namespace Essences\AccessManager;

    use Essences\Tariff;
    use Essences\User;
    use Exception\ExceptionAccess;
    use Exception\ExceptionAuthorization;

    abstract class AbstractAccessManagerPay implements InterfaceAccessManager
    {

        protected const RENTER = 1;
        protected const BUYER = 2;
        protected const OWNER_RENT = 3;
        protected const OWNER_BUY = 4;

        protected const TYPE_MIN = 1;
        protected const TYPE_MIDDLE = 2;
        protected const TYPE_MAX = 3;

        final public function check(): void
        {
            if(!User::getInstance()->getAuth())
            {
                throw new ExceptionAuthorization;
            }

            if(!$this->isCheck())
            {
                throw new ExceptionAccess;
            }
        }

        final public function isCheck(): bool
        {
            $tariff = $this->getTariff();

            return User::getInstance()->checkPay($tariff);
        }

        abstract protected function getTariff(): Tariff;
    }