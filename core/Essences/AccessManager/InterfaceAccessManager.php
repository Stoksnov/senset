<?php

    namespace Essences\AccessManager;

    interface InterfaceAccessManager
    {

        public function check(): void;

        public function isCheck(): bool;
    }