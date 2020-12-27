<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\Validation\ValidationFavorite;
    use Model\AJAX\ModelFavorite;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerFavorite extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(2, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $validation = new ValidationFavorite($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = new ModelFavorite($this->request->get());
            $model->run($view);
        }
    }