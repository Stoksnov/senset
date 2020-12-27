<?php

    namespace Essences\Validation;

    final class ValidationAddOrder extends AbstractValidation
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
                ->setParam('phone')
                ->isNotEmpty()
                ->isPhone()
                ->setMessage('Неверный формат телефона');
        }
    }