<?php

    namespace View\Page;

    final class Page404 extends AbstractPage
    {

        public function __construct()
        {
            parent::__construct('404', 'ZASELITE | Страницы не существует');
        }
    }