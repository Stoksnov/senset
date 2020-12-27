<?php

    namespace Request;

    final class RequestFiles extends AbstractRequest
    {

        public function __construct()
        {
            $this->params = $_FILES;
        }
    }