<?php

    namespace Controller;

    use Controller\Interfaces\InterfaceController;
    use Exception\ExceptionRouter;
    use Request\InterfaceRequest;

    abstract class AbstractController implements InterfaceController
    {

        protected $countNecessaryParams;
        protected $request;

        protected function __construct(int $countNecessaryParams, InterfaceRequest $request)
        {
            $this->countNecessaryParams = $countNecessaryParams;
            $this->request = $request;
        }

        public function run(): void
        {
            if($this->request->count() != $this->countNecessaryParams)
            {
                throw new ExceptionRouter;
            }

            $this->generalAction();
        }

        abstract protected function generalAction(): void;
    }