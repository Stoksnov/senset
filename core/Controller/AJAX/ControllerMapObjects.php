<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\Validation\ValidationMapObjects;
    use Model\AJAX\ModelMapObjects;
    use Request\InterfaceRequest;
    use View\AJAX\MapObjects;

    final class ControllerMapObjects extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(2, $request);
        }

        protected function generalAction(): void
        {
            $validation = new ValidationMapObjects($this->request->get());
            $validation->run();

            $view = new MapObjects;

            $model = new ModelMapObjects($this->request->get());
            $model->run($view);
        }
    }