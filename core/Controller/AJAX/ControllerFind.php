<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\Validation\ValidationFind;
    use Model\AJAX\ModelFind;
    use Request\InterfaceRequest;
    use View\AJAX\ObjectsFind;

    final class ControllerFind extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(16, $request);
        }

        protected function generalAction(): void
        {
            $validation = new ValidationFind($this->request->get());
            $validation->run();

            $view = new ObjectsFind;

            $model = new ModelFind($this->request->get());
            $model->run($view);
        }
    }