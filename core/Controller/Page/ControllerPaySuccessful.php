<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Model\Page\ModelPaySuccessful;
    use Request\InterfaceRequest;
    use View\Page\PagePaySuccessful;

    final class ControllerPaySuccessful extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(1, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $model = new ModelPaySuccessful($this->request->get());
            $model->run(new PagePaySuccessful);
        }
    }