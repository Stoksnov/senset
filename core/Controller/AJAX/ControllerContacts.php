<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\Validation\ValidationContacts;
    use Model\AJAX\ModelContacts;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerContacts extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(5, $request);
        }

        protected function generalAction(): void
        {
            $validation = new ValidationContacts($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = new ModelContacts($this->request->get());
            $model->run($view);
        }
    }