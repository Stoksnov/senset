<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Model\Page\ModelProfile;
    use Request\InterfaceRequest;
    use View\Page\PageProfile;

    final class ControllerProfile extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(1, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            $model = new ModelProfile;
            $model->run(new PageProfile);
        }
    }