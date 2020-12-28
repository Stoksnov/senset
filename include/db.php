<?php

    require_once 'lib/redbean/rb-postgres.php';

    R::setup('pgsql:host=176.119.158.214/;dbname=crm',
        'postgres', 'Qwe');

    R::ext('xdispense', function($type){
        return R::getRedBean()->dispense($type);

    });
