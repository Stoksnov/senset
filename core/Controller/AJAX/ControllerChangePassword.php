<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\Validation\ValidationChangePassword;
    use Model\AJAX\ModelChangePassword;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerChangePassword extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(4, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $validation = new ValidationChangePassword($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = new ModelChangePassword($this->request->get());
            $model->run($view);
        }
    }