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
                case 'assistant':
                    return new ControllerAssistant($request);
                case 'contacts':
                    return new ControllerContacts($request);
                case 'documents':
                    return new ControllerDocuments($request);
                case 'find':
                    return new ControllerFind($request);
                case 'how-it-work':
                    return new ControllerHowItWork($request);
                case 'object':
                    return new ControllerObject($request);
                case 'profile':
                    return new ControllerProfile($request);
                case 'rent':
                    return new ControllerRent($request);
                case 'tariffs':
                    return new ControllerTariffs($request);
                case 'processing-data':
                    return new ControllerProcessingData($request);
                case 'terms-of-service':
                    return new ControllerTermsOfService($request);
                case 'logout':
                    return new ControllerLogout($request);
                case 'pay':
                    return new ControllerPay($request);
                case 'payerror':
                    return new ControllerPayError($request);
                case 'paysuccessful':
                    return new ControllerPaySuccessful($request);
                case 'paycheck':
                    return new ControllerPayCheck($request);
                case '403':
                    return new Controller403($request);
                case '404':
                    return new Controller404($request);
            }

            throw new ExceptionRouter;
        }
    }