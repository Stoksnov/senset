<?php

    namespace Model\AJAX\Recovery;

    use Essences\Sender\SenderSMS;
    use Exception\ExceptionUpdateDB;
    use Exception\ExceptionValidation;
    use Model\AJAX\AbstractModelAJAX;

    final class ModelRecoverySender extends AbstractModelAJAX
    {

        private $code;

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->checkUser();
            $this->deleteOldDB();
            $this->saveDB();
            $this->send();
        }

        private function checkUser(): void
        {
            if(\R::count('zaselite_users', 'tel = ?', [$this->paramsInput['phone']]) == 0)
            {
                $error = json_encode(['phone' => 'Пользователя с таким телефоном не существует'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }
        }

        private function send(): void
        {
            $msg = "Код для восстановления пароля: {$this->code}";

            $sender = new SenderSMS;

            $sender->push($this->paramsInput['phone'], $msg, 'Восстановление пороля zaselite.ru');
        }

        private function saveDB(): void
        {
            $this->code = rand(1000, 9999);

            $recovery = \R::xdispense('zaselite_recovery');

            $recovery->phone = $this->paramsInput['phone'];
            $recovery->code = $this->code;
            $recovery->activate = false;
            $recovery->date_end = \R::isoDateTime(time() + 60 * 60);

            $id = \R::store($recovery);

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        private function deleteOldDB(): void
        {
            \R::exec('DELETE FROM zaselite_recovery WHERE phone = ?', [$this->paramsInput['phone']]);
        }
    }