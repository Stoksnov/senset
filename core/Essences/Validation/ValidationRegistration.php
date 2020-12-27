<?php

    namespace Essences\Validation;

    final class ValidationRegistration extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('roleId')
                ->isNotEmpty()
                ->isParams(['1', '2'])
                ->setMessage('Неверный тип пользовтеля');

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

            $this->validator
                ->setParam('password2')
                ->isParams([$this->validator->getParam('password')])
                ->setMessage('Пароли не совпадают');

            $this->validator
                ->setParam('agreement')
                ->isNotEmpty()
                ->isCheckBoxActive()
                ->setMessage('Необходимо согласиться с условиями');
        }
    }