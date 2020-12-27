<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerNotAuthorization;
    use Essences\Validation\ValidationRegistration;
    use Model\AJAX\ModelRegistration;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerRegistration extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(6, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerNotAuthorization;
            $accessor->check();

            $validation = new ValidationRegistration($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = new ModelRegistration($this->request->get());
            $model->run($view);
        }
    }