<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Model\Page\ModelHistoryData;
    use Request\InterfaceRequest;
    use View\Page\PageAbout;
    use View\Page\PageHistoryData;

    final class ControllerHistoryData extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(1, $request);
        }

        protected function generalAction(): void
        {
            $model = new ModelHistoryData;
            $model->run(new PageHistoryData);
        }
    }