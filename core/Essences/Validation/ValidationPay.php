<?php

    namespace Essences\Validation;

    final class ValidationPay extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam(1)
                ->isNotEmpty()
                ->isInt()
                ->isIntervalInt(1, 12)
                ->setMessage('Неверный формат tariffId');
        }
    }