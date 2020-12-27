<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Model\Page\ModelPayError;
    use Request\InterfaceRequest;
    use View\Page\PagePayError;

    final class ControllerPayError extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(1, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $model = new ModelPayError($this->request->get());
            $model->run(new PagePayError);
        }
    }