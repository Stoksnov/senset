<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Model\AJAX\ModelMapObjectsCoordinates;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerMapObjectsCoordinates extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(16, $request);
        }

        protected function generalAction(): void
        {
            $view = new OutputerAJAX;

            $model = new ModelMapObjectsCoordinates($this->request->get());
            $model->run($view);
        }
    }