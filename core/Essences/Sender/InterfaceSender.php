<?php

    namespace Essences\Sender;

    interface InterfaceSender
    {

        public function push(string $receiver, string $message, string $subject): void;
    }