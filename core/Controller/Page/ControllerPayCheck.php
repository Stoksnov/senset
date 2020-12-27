<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Model\Page\ModelPayCheck;
    use Request\InterfaceRequest;
    use View\Page\PagePayCheck;

    final class ControllerPayCheck extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(0, $request);
        }

        public function run(): void
        {
            $this->generalAction();
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $model = new ModelPayCheck($this->request->get());
            $model->run(new PagePayCheck);
        }
    }