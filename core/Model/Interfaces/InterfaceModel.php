<?php

    namespace Model\Interfaces;

    use View\Interfaces\InterfaceView;

    interface InterfaceModel
    {

        public function run(InterfaceView $view): void;
    }