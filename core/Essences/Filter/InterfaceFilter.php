<?php

    namespace Essences\Filter;

    interface InterfaceFilter
    {

        public function run(): void;

        public function getCount(): int;

        public function getLimit(int $start, int $count): array;
    }