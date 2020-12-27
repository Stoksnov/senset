<?php

    namespace Request;

    interface InterfaceRequest
    {

        public function get(): array;

        public function set(string $key, string $value);

        public function count(): int;
    }