<?php

    namespace Exception;

    final class ExceptionUpdateDB extends ExceptionCustom
    {

        public function __construct()
        {
            parent::__construct('Что-то пошло не так, повторите попытку', 4);
        }
    }