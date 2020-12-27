<?php

    namespace Essences\Validation;

    final class ValidationSale extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('roleId')
                ->isNotEmpty()
                ->isParams(['1', '2'])
                ->setMessage('Неверный тип пользовтеля');

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

            $this->validator
                ->setParam('price_min')
                ->isNotEmpty()
                ->isInt()
                ->setMessage('Неверный формат мин. цены');

            $this->validator
                ->setParam('price_max')
                ->isNotEmpty()
                ->isInt()
                ->setMessage('Неверный формат макс. цены');

            $this->validator
                ->setParam('rooms')
                ->isNotEmpty()
                ->setMessage('Неверный формат типа недвижимости');
        }
    }