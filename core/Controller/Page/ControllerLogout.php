<?php

    namespace Controller\Page;

    use Controller\AbstractController;
    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\User;
    use Request\InterfaceRequest;
    use Router\Page\RouterPage;

    final class ControllerLogout extends AbstractController
    {
        public function __construct(InterfaceRequest $request)
        {
            parent::__construct(1, $request);
        }

        protected function generalAction(): void
        {
            $accessor = new AccessManagerAuthorization;
            $accessor->check();

            User::getInstance()->logout();

            RouterPage::redirect('/');
        }
    }