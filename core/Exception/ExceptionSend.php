<?php

    namespace Exception;

    final class ExceptionSend extends ExceptionCustom
    {

        public function __construct(string $message)
        {
            parent::__construct($message, 7);
        }
    }