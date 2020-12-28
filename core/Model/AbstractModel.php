<?php

    namespace Model;

    use Exception\ExceptionCustom;
    use Exception\ExceptionUpdateDB;
    use Model\Interfaces\InterfaceModel;
    use View\Interfaces\InterfaceView;

    abstract class AbstractModel implements InterfaceModel
    {

        protected $paramsInput;
        protected $paramsOutput;

        protected function __construct(array $data)
        {
            $this->paramsInput = $data;
            $this->paramsOutput = [];
        }

        public function run(InterfaceView $view): void
        {
            
                $this->generateOutput();

                $view->out($this->paramsOutput);
        }

        abstract protected function generateOutput(): void;
    }