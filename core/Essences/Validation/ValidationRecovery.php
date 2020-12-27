<?php

    namespace Essences\Validation;

    final class ValidationRecovery extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('phone')
                ->isNotEmpty()
                ->isPhone()
                ->setMessage('Неверный формат телефона');

            $this->validator
                ->setParam('step')
                ->isNotEmpty()
                ->isParams([1, 2, 3], false)
                ->setMessage('Неверный формат шага');

            $step = $this->validator->getParam('step');

            if($step > 1)
            {
                $this->validator
                    ->setParam('code')
                    ->isNotEmpty()
                    ->isInt()
                    ->isIntervalInt(1000, 9999)
                    ->setMessage('Неверный формат кода');
            }

            if($step == 3)
            {
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
            }
        }
    }