<?php

    namespace Request;

    final class RequestData extends AbstractRequest
    {

        public function __construct()
        {
            $this->params = $_REQUEST;
        }
    }