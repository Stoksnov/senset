<?php

    namespace Essences\AccessManager;

    use Essences\User;

    final class AccessManagerNotAuthorization extends AbstractAccessManager
    {

        public function check(): void
        {
            User::getInstance()->checkNotAuthorization();
        }
    }