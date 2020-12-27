<?php

    namespace Request;

    final class RequestRouterPage extends AbstractRequest
    {

        public function __construct(array &$request)
        {
            if(array_key_exists('params', $request))
            {
                $this->params = explode('/', $request['params']);
            }

            if(empty($this->params))
            {
                $this->params[0] = 'main';
            }
        }
    }