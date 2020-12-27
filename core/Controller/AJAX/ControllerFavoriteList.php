<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\Validation\ValidationFind;
    use Model\AJAX\ModelFavoriteList;
    use Model\AJAX\ModelFind;
    use Request\InterfaceRequest;
    use View\AJAX\ObjectsList;

    final class ControllerFavoriteList extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(2, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $validation = new ValidationFind($this->request->get());
            $validation->run();

            $view = new ObjectsList;

            $model = new ModelFavoriteList($this->request->get());
            $model->run($view);
        }
    }