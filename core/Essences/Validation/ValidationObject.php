<?php

    namespace Essences\Validation;

    final class ValidationObject extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam(1)
                ->isNotEmpty()
                ->isInt()
                ->setMessage('Неверный формат objectId');
        }
    }