<?php

    namespace Router\Page;

    use Controller\Page\FactoryControllerPage;
    use Exception\ExceptionAccess;
    use Exception\ExceptionAuthorization;
    use Exception\ExceptionCustom;
    use Request\RequestRouterPage;
    use Router\Interfaces\InterfaceRouter;

    final class RouterPage implements InterfaceRouter
    {

        public function start(): void
        {
            $request = new RequestRouterPage($_GET);

            try
            {
                $controller = FactoryControllerPage::build($request);
                $controller->run();
            }
            catch(ExceptionAccess | ExceptionAuthorization $exception)
            {
                self::redirect('/403');
            }
            catch(ExceptionCustom $exception)
            {
                self::redirect('/404');
            }
        }

        public static function redirect(string $url): void
        {
            \R::close();
            header('Location: ' . $url);
            exit;
        }
    }