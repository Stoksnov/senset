<?php

    namespace Router\AJAX;

    use Controller\AJAX\FactoryControllerAJAX;
    use Exception\ExceptionCustom;
    use Request\RequestData;
    use Router\Interfaces\InterfaceRouter;

    final class RouterAJAX implements InterfaceRouter
    {

        public function start(): void
        {
            $request = new RequestData;

            try
            {
                $controller = FactoryControllerAJAX::build($request);
                $controller->run();
            }
            catch(ExceptionCustom $exception)
            {
                echo $exception;
            }
        }
    }