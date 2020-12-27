<?php

    namespace Essences\Validation;

    final class ValidationUpdateProfile extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('name')
                ->isNotEmpty()
                ->strlenMin(2)
                ->strlenMax(128)
                ->setMessage('Неверный формат имени');

            $this->validator
                ->setParam('email')
                ->isNotEmpty()
                ->isEmail()
                ->strlenMin(5)
                ->strlenMax(128)
                ->setMessage('Неверный формат E-mail');

            $this->validator
                ->setParam('phone')
                ->isNotEmpty()
                ->isPhone()
                ->setMessage('Неверный формат телефона');

            $this->validator
                ->setParam('birthday')
                ->setNotNecessary()
                ->isDate()
                ->setMessage('Неверный формат даты');

            $this->validator
                ->setParam('avatar')
                ->setMessage('Неверный формат фото');
        }
    }