<?php

    namespace View\Page;

    final class PagePayCheck extends AbstractPage
    {

        public function __construct()
        {
            parent::__construct('paycheck', 'ZASELITE | Оплата');
        }

        public function out(array $data): void
        {
            echo $data['answer'];
        }
    }