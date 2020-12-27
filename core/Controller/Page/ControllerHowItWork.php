<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Model\Page\ModelMain;
    use Request\InterfaceRequest;
    use View\Page\PageHowItWork;

    final class ControllerHowItWork extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(1, $request);
        }

        protected function generalAction(): void
        {
            $model = new ModelMain;
            $model->run(new PageHowItWork);
        }
    }