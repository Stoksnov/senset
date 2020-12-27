<?php

    namespace Essences\Validation;

    final class ValidationFind extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('countViewed')
                ->isInt()
                ->setMessage('Неверный формат countViewed');
        }
    }