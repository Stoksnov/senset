<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\Validation\ValidationPay;
    use Model\Page\ModelPay;
    use Request\InterfaceRequest;
    use View\Page\PagePay;

    final class ControllerPay extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(2, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $validation = new ValidationPay($this->request->get());
            $validation->run();

            $model = new ModelPay($this->request->get());
            $model->run(new PagePay);
        }
    }