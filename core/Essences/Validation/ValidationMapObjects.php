<?php

    namespace Essences\Validation;

    final class ValidationMapObjects extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('objectId')
                ->isNotEmpty()
                ->isCustomComparator(function (string $param) {
                    return preg_match('/^(\d+,)*\d+$/', $param) == 1;
                })
                ->setMessage('Неверный формат objectId');
        }
    }