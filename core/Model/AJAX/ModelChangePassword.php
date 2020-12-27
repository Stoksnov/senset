<?php

    namespace Model\AJAX;

    use Essences\User;

    final class ModelChangePassword extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->updateDB();
        }

        private function updateDB(): void
        {
            $password = $this->paramsInput['password'];
            $passwordOld = $this->paramsInput['passwordOld'];

            User::getInstance()->updatePasswordSession($password, $passwordOld);
        }
    }