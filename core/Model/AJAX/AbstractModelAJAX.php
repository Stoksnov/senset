<?php

    namespace Model\AJAX;

    use Model\AbstractModel;
    use View\Interfaces\InterfaceView;

    abstract class AbstractModelAJAX extends AbstractModel
    {

        protected function __construct(array $data)
        {
            parent::__construct($data);
        }

        final public function run(InterfaceView $view): void
        {
            parent::run($view);
        }
    }