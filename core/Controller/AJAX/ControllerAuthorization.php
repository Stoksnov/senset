<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerNotAuthorization;
    use Essences\Validation\ValidationAuthorization;
    use Model\AJAX\ModelAuthorization;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerAuthorization extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(3, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerNotAuthorization;
            $accessor->check();

            $validation = new ValidationAuthorization($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = new ModelAuthorization($this->request->get());
            $model->run($view);
        }
    }