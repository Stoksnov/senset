<?php

    namespace Request;

    final class RequestCookie extends AbstractRequest
    {

        public function __construct()
        {
            $this->params = $_COOKIE;
        }

        public function set(string $key, string $value)
        {
            setcookie($key, $value, time() + 30 * 24 * 60 * 60 * 1000, '/');
        }
    }