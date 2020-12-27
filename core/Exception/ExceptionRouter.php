<?php

    namespace Exception;

    final class ExceptionRouter extends ExceptionCustom
    {

        public function __construct()
        {
            parent::__construct('Страницы не существует', 3);
        }
    }