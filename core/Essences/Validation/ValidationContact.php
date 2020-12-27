<?php

    namespace Essences\Validation;

    final class ValidationContact extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('objectId')
                ->isNotEmpty()
                ->isInt()
                ->setMessage('Неверный формат objectId');
        }
    }