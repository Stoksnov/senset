<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\Validation\ValidationHistoryViews;
    use Model\AJAX\ModelHistoryViews;
    use Request\InterfaceRequest;
    use View\AJAX\ObjectsList;

    final class ControllerHistoryViews extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(2, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $validation = new ValidationHistoryViews($this->request->get());
            $validation->run();

            $view = new ObjectsList;

            $model = new ModelHistoryViews($this->request->get());
            $model->run($view);
        }
    }