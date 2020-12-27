<?php

    namespace Essences\Validation;

    final class ValidationHistoryViews extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('day')
                ->isParams(['', 'today', 'yesterday'])
                ->setMessage('Неверный параметр day');
        }
    }