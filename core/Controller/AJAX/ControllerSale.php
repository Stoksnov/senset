<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\Validation\ValidationSale;
    use Model\AJAX\ModelSale;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerSale extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(8, $request);
        }

        protected function generalAction(): void
        {
            $validation = new ValidationSale($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = new ModelSale($this->request->get());
            $model->run($view);
        }
    }