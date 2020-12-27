<?php

    namespace Exception;

    /*
    Коды ошибок:
    0 - ОК
    1 - Ошибка авторизации
    2 - Ошибка доступа
    3 - Ошибка роутера
    4 - Ошибка изменения базы данных
    5 - Ошибка типа данных
    6 - Ошибка загрузки файлов
    7 - Ошибка отправки почты, смс
    */

    use View\AJAX\OutputerAJAX;

    abstract class ExceptionCustom extends \Exception
    {

        protected function __construct(string $message, int $code = 0, \Exception $previous = null)
        {
            $json = json_decode($message);

            if(json_last_error() !== JSON_ERROR_NONE)
            {
                $message = json_encode(['global' => $message], JSON_UNESCAPED_UNICODE);
            }

            parent::__construct($message, $code, $previous);
        }

        final public function __toString(): string
        {
            $outputer = new OutputerAJAX;

            $outputer->setData(['message' => $this->getMessage()], $this->getCode());

            return $outputer->getData();
        }
    }