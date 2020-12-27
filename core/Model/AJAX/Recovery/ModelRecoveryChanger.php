<?php

    namespace Model\AJAX\Recovery;

    use Essences\User;
    use Exception\ExceptionValidation;
    use Model\AJAX\AbstractModelAJAX;

    final class ModelRecoveryChanger extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->checkRecovery();
            $this->saveDB();
        }

        private function checkRecovery(): void
        {
            $where = 'phone = ? AND code = ? AND activate = ?';

            $count = \R::count('zaselite_recovery', $where,
                [$this->paramsInput['phone'], $this->paramsInput['code'], true]);

            if($count == 0)
            {
                throw new ExceptionValidation('Неверные данные для восстановления');
            }
        }

        private function saveDB(): void
        {
            User::updatePassword($this->paramsInput['phone'], $this->paramsInput['password']);
        }
    }