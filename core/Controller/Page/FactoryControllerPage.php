<?php

    namespace Controller\Page;

    use Controller\Interfaces\InterfaceController;
    use Exception\ExceptionRouter;
    use Request\InterfaceRequest;

    final class FactoryControllerPage
    {

        public static function build(InterfaceRequest $request): InterfaceController
        {
            switch($request->get()[0])
            {
                case 'main':
                    return new ControllerMain($request);
                case 'about':
                    return new ControllerAbout($request);
                case 'profile':
                    return new ControllerProfile($request);
                case 'terms-of-service':
                    return new ControllerTermsOfService($request);
                case 'history-data':
                    return new ControllerHistoryData($request);
                case 'logout':
                    return new ControllerLogout($request);
                case '403':
                    return new Controller403($request);
                case '404':
                    return new Controller404($request);
            }

            throw new ExceptionRouter;
        }
    }