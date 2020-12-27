<?php

    require_once 'lib/redbean/rb-mysql.php';

    R::setup('mysql:host=91.210.168.146;dbname=crm',
        'petrakov', '98vova98');

    R::ext('xdispense', function($type){
        return R::getRedBean()->dispense($type);

    });
