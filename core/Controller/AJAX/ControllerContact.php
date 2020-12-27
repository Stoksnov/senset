<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\Validation\ValidationContact;
    use Model\AJAX\ModelContact;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerContact extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(2, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $validation = new ValidationContact($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = new ModelContact($this->request->get());
            $model->run($view);
        }
    }