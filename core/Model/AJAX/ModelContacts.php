<?php

    namespace Model\AJAX;

    use Essences\Sender\SenderEmail;
    use Exception\ExceptionUpdateDB;

    final class ModelContacts extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->saveDB();
            $this->sendEmail();
        }

        private function saveDB(): void
        {
            $message = \R::xdispense('zaselite_messages');

            $message->name = $this->paramsInput['name'];
            $message->email = $this->paramsInput['email'];
            $message->message = $this->paramsInput['message'];

            $id = \R::store($message);

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        private function sendEmail(): void
        {
            $msgBlocks = [];

            $msgBlocks[] = 'Имя: ' . $this->paramsInput['name'];
            $msgBlocks[] = 'E-mail: ' . $this->paramsInput['email'];
            $msgBlocks[] = 'Сообщение: ' . $this->paramsInput['message'];

            $sender = new SenderEmail;

            $sender->push('info@zaselite.ru', implode("<br>", $msgBlocks), 'Форма обратной связи');
        }
    }