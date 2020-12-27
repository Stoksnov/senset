<?php

    namespace Exception;

    final class ExceptionAuthorization extends ExceptionCustom
    {

        public function __construct()
        {
            parent::__construct('Ошибка авторизации', 1);
        }
    }