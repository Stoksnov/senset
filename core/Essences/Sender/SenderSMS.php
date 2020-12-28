<?php

    namespace Essences\Sender;

    use Exception\ExceptionSend;

    final class SenderSMS implements InterfaceSender
    {

        private const HOST = 'gate.iqsms.ru';
        private const PORT = 80;
        private const LOGIN = 'z1609071935881';
        private const PASSWORD = '253065';

        public function __construct()
        {

        }

        public function push(string $receiver, string $message, string $subject): void
        {
            $phone = $this->maskToIntegerPhone($receiver);

            $response = $this->send(self::HOST, self::PORT, self::LOGIN, self::PASSWORD,
                $phone, $message, '176.119.158.214', 'wap.176.119.158.214');

            $this->checkResponse($response);
            $response = explode('=', $response);
            $this->checkResponse($response);

            if(!(count($response) == 2 && $response[1] == 'accepted'))
            {
                throw new ExceptionSend('Повторите попытку');
            }

            do
            {
                $response = $this->status(self::HOST, self::PORT, self::LOGIN, self::PASSWORD, $response[0]);
                $this->checkResponse($response);
                $response = explode('=', $response);
                $this->checkResponse($response);

                if(count($response) != 2)
                {
                    throw new ExceptionSend('Повторите попытку');
                }

                if($response[1] == 'delivered')
                {
                    return;
                }

                sleep(2);
            }while($response[1] == 'queued' || $response[1] == 'smsc submit');

            throw new ExceptionSend('Повторите попытку');
        }

        private function send(string $host, int $port, string $login, string $password, string $phone, string $text, $sender = false, $wapurl = false ): ?string
        {
            $fp = fsockopen($host, $port, $errno, $errstr);
            if (!$fp) {
                return "errno: $errno \nerrstr: $errstr\n";
            }
            fwrite($fp, "GET /send/" .
                "?phone=" . rawurlencode($phone) .
                "&text=" . rawurlencode($text) .
                ($sender ? "&sender=" . rawurlencode($sender) : "") .
                ($wapurl ? "&wapurl=" . rawurlencode($wapurl) : "") .
                " HTTP/1.0\n");
            fwrite($fp, "Host: " . $host . "\r\n");
            if ($login != "") {
                fwrite($fp, "Authorization: Basic " .
                    base64_encode($login. ":" . $password) . "\n");
            }
            fwrite($fp, "\n");
            $response = "";
            while(!feof($fp)) {
                $response .= fread($fp, 1);
            }
            fclose($fp);
            list($other, $responseBody) = explode("\r\n\r\n", $response, 2);
            return $responseBody;
        }

        private function status(string $host, int $port, string $login, string $password, int $sms_id): ?string
        {
            $fp = fsockopen($host, $port, $errno, $errstr);
            if (!$fp) {
                return "errno: $errno \nerrstr: $errstr\n";
            }
            fwrite($fp, "GET /status/" .
                "?id=" . $sms_id .
                " HTTP/1.0\n");
            fwrite($fp, "Host: " . $host . "\r\n");
            if ($login != "") {
                fwrite($fp, "Authorization: Basic " .
                    base64_encode($login. ":" . $password) . "\n");
            }
            fwrite($fp, "\n");
            $response = "";
            while(!feof($fp)) {
                $response .= fread($fp, 1);
            }
            fclose($fp);
            list($other, $responseBody) = explode("\r\n\r\n", $response, 2);
            return $responseBody;
        }

        private function maskToIntegerPhone(string $phone): string
        {
            $phone = str_replace('(', '', $phone);
            $phone = str_replace(')', '', $phone);
            $phone = str_replace('-', '', $phone);
            $phone = str_replace(' ', '', $phone);

            return substr($phone, 1);
        }

        private function checkResponse($response): void
        {
            if(empty($response))
            {
                throw new ExceptionSend('Повторите попытку');
            }
        }
    }