<?php

    namespace Exception;

    final class ExceptionAccess extends ExceptionCustom
    {

        public function __construct()
        {
            parent::__construct('Ошибка доступа', 2, null);
        }
    }