<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Essences\Validation\ValidationObject;
    use Model\Page\ModelObject;
    use Request\InterfaceRequest;
    use View\Page\PageObject;

    final class ControllerObject extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(2, $request);
        }

        protected function generalAction(): void
        {
            $validation = new ValidationObject($this->request->get());
            $validation->run();

            $model = new ModelObject($this->request->get());
            $model->run(new PageObject);
        }
    }