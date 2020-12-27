<?php

    namespace Exception;

    final class ExceptionUploadFile extends ExceptionCustom
    {

        public function __construct(string $message)
        {
            parent::__construct($message, 6);
        }
    }