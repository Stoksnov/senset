<?php

    namespace Essences\Validation;

    final class ValidationChangePassword extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('passwordOld')
                ->isNotEmpty()
                ->strlenMin(6)
                ->strlenMax(128)
                ->setMessage('Неверный формат старого пароля');

            $this->validator
                ->setParam('password')
                ->isNotEmpty()
                ->strlenMin(6)
                ->strlenMax(128)
                ->setMessage('Неверный формат нового пароля');

            $this->validator
                ->setParam('password2')
                ->isParams([$this->validator->getParam('password')])
                ->setMessage('Пароли не совпадают');
        }
    }