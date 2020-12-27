<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Model\Page\ModelFind;
    use Request\InterfaceRequest;
    use Request\RequestData;
    use View\Page\PageFind;

    final class ControllerFind extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(1, $request);
        }

        protected function generalAction(): void
        {
            $this->request = new RequestData;
            $model = new ModelFind($this->request->get());
            $model->run(new PageFind);
        }
    }