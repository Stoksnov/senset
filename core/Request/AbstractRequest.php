<?php

    namespace Request;

    abstract class AbstractRequest implements InterfaceRequest
    {

        protected $params = [];

        public function get(): array
        {
            return $this->params;
        }

        public function set(string $key, string $value)
        {
            $this->params[$key] = $value;
        }

        public function count(): int
        {
            return count($this->params);
        }

        public function isSet(string $key): bool
        {
            return isset($this->params[$key]);
        }

        public function getValue(string $key): string
        {
            return $this->params[$key];
        }
    }