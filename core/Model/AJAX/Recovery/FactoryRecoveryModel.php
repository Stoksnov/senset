<?php

    namespace Model\AJAX\Recovery;

    use Exception\ExceptionValidation;
    use Model\Interfaces\InterfaceModel;
    use Request\InterfaceRequest;

    final class FactoryRecoveryModel
    {

        public static function build(InterfaceRequest $request): InterfaceModel
        {
            switch($request->get()['step'])
            {
                case 1:
                    return new ModelRecoverySender($request->get());
                case 2:
                    return new ModelRecoveryChecker($request->get());
                case 3:
                    return new ModelRecoveryChanger($request->get());
            }

            throw new ExceptionValidation('Неверный формат step');
        }
    }