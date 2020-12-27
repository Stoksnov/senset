<?php

    namespace Exception;

    final class ExceptionValidation extends ExceptionCustom
    {

        public function __construct(string $message)
        {
            parent::__construct($message, 5);
        }
    }