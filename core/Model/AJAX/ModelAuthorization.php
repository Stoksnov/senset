<?php

    namespace Model\AJAX;

    use Essences\User;

    final class ModelAuthorization extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            User::login($this->paramsInput['phone'], $this->paramsInput['password']);
        }
    }