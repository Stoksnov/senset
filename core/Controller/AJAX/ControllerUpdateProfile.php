<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\Validation\ValidationUpdateProfile;
    use Model\AJAX\ModelUpdateProfile;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerUpdateProfile extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(6, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $validation = new ValidationUpdateProfile($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = new ModelUpdateProfile($this->request->get());
            $model->run($view);
        }
    }