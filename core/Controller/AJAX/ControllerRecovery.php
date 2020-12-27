<?php

    namespace Controller\AJAX;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerNotAuthorization;
    use Essences\Validation\ValidationRecovery;
    use Model\AJAX\Recovery\FactoryRecoveryModel;
    use Request\InterfaceRequest;
    use View\AJAX\OutputerAJAX;

    final class ControllerRecovery extends AbstractController
    {

        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(6, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerNotAuthorization;
            $accessor->check();

            $validation = new ValidationRecovery($this->request->get());
            $validation->run();

            $view = new OutputerAJAX;

            $model = FactoryRecoveryModel::build($this->request);
            $model->run($view);
        }
    }