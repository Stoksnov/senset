<?php

    namespace Essences\GeneratorStatuses;

    use Essences\ObjectEstate;

    interface IGeneratorStatuses
    {

        public function setNext(self $next): void;

        public function run(ObjectEstate $object): void;
    }