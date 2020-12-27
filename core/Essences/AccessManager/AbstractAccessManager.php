<?php

    namespace Essences\AccessManager;

    use Essences\User;

    abstract class AbstractAccessManager implements InterfaceAccessManager
    {

        abstract public function check(): void;

        final public function isCheck(): bool
        {
            return User::getInstance()->getAuth();
        }
    }