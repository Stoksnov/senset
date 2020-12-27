<?php

    declare(strict_types = 1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/include/include.php';

    spl_autoload_register(function ($file) {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/core/' . str_replace("\\", "/", $file) . '.php';

        if(file_exists($file))
        {
            require_once $file;
        }
    });

    date_default_timezone_set(\Essences\Locator::getInstance()->getTimezone());

    $router = new \Router\Page\RouterPage;

    $router->start();

    R::close();