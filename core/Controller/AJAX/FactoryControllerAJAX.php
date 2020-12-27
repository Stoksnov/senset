<?php

    namespace Controller\AJAX;

    use Controller\Interfaces\InterfaceController;
    use Exception\ExceptionValidation;
    use Request\InterfaceRequest;

    final class FactoryControllerAJAX
    {

        public static function build(InterfaceRequest $request): InterfaceController
        {
            if(array_key_exists('action', $request->get()))
            {
                switch($request->get()['action'])
                {
                    case 'authorization':
                        return new ControllerAuthorization($request);
                    case 'registration':
                        return new ControllerRegistration($request);
                    case 'contacts':
                        return new ControllerContacts($request);
                    case 'recovery':
                        return new ControllerRecovery($request);
                    case 'find':
                        return new ControllerFind($request);
                    case 'favoriteList':
                        return new ControllerFavoriteList($request);
                    case 'favorite':
                        return new ControllerFavorite($request);
                    case 'contact':
                        return new ControllerContact($request);
                    case 'mapObjects':
                        return new ControllerMapObjects($request);
                    case 'mapObjectsCoordinates':
                        return new ControllerMapObjectsCoordinates($request);
                    case 'sale':
                        return new ControllerSale($request);
                    case 'updateProfile':
                        return new ControllerUpdateProfile($request);
                    case 'changePassword':
                        return new ControllerChangePassword($request);
                    case 'historyViews':
                        return new ControllerHistoryViews($request);
                    case 'addOrder':
                        return new ControllerAddOrder($request);
                }
            }

            throw new ExceptionValidation('Неверный формат action');
        }
    }