<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Model\Page\ModelMain;
    use Request\InterfaceRequest;
    use View\Page\Page404;

    final class Controller404 extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(1, $request);
        }

        protected function generalAction(): void
        {
            $model = new ModelMain;
            $model->run(new Page404);
        }
    }