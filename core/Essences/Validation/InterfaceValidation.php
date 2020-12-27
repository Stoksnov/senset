<?php

    namespace Essences\Validation;

    interface InterfaceValidation
    {

        public function run(): void;

        public function __toString(): string;
    }