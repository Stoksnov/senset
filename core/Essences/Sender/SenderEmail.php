<?php

    namespace Essences\Sender;

    use Exception\ExceptionSend;
    use PHPMailer\PHPMailer\PHPMailer;

    final class SenderEmail implements InterfaceSender
    {

        private $sender;

        public function __construct()
        {
            require_once $_SERVER['DOCUMENT_ROOT'].'/include/lib/phpmailer/PHPMailer.php';
            require_once $_SERVER['DOCUMENT_ROOT'].'/include/lib/phpmailer/SMTP.php';
            require_once $_SERVER['DOCUMENT_ROOT'].'/include/lib/phpmailer/Exception.php';

            $this->sender = new PHPMailer;

            $this->sender->isSMTP();
            $this->sender->CharSet = "UTF-8";
            $this->sender->SMTPAuth   = true;

            $this->sender->Host       = 'ssl://smtp.yandex.ru';
            $this->sender->Username   = 'support@zaselite.ru';
            $this->sender->Password   = 'supportzaselite666';
            $this->sender->SMTPSecure = 'ssl';
            $this->sender->Port       = 465;
            $this->sender->setFrom('support@zaselite.ru', 'zaselite.ru');
        }

        public function push(string $receiver, string $message, string $subject): void
        {
            $this->sender->addAddress($receiver);

            $this->sender->isHTML(true);
            $this->sender->Subject = $subject;
            $this->sender->Body    = $message;

            if (!$this->sender->send())
            {
                throw new ExceptionSend('Не удалось отправить E-mail');
            }
        }
    }