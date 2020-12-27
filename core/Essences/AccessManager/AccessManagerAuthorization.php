<?php

    namespace Essences\AccessManager;

    use Essences\User;

    final class AccessManagerAuthorization extends AbstractAccessManager
    {

        public function check(): void
        {
            User::getInstance()->checkAuthorization();
        }
    }