<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\Validation\ValidationAddOrder;
    use Model\AJAX\ModelAddOrder;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerAddOrder extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(3, $request);
        }

        protected function generalAction(): void
        {
            $validation = new ValidationAddOrder($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = new ModelAddOrder($this->request->get());
            $model->run($view);
        }
    }