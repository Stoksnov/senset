<?php

    namespace Essences\Validation;

    final class ValidationAuthorization extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('phone')
                ->isNotEmpty()
                ->isPhone()
                ->setMessage('Неверный формат телефона');

            $this->validator
                ->setParam('password')
                ->isNotEmpty()
                ->strlenMin(6)
                ->strlenMax(128)
                ->setMessage('Неверный формат пароля');
        }
    }