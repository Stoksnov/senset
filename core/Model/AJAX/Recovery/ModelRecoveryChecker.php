<?php

    namespace Model\AJAX\Recovery;

    use Exception\ExceptionUpdateDB;
    use Exception\ExceptionValidation;
    use Model\AJAX\AbstractModelAJAX;

    final class ModelRecoveryChecker extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $recovery = $this->getRecovery();

            $this->checkCode($recovery['code']);
            $this->saveDB($recovery);
        }

        private function getRecovery(): array
        {
            $where = 'phone = ? AND date_end >= ? AND activate = ?';

            $recovery = \R::getRow("SELECT * FROM zaselite_recovery WHERE {$where}",
                                        [$this->paramsInput['phone'], \R::isoDateTime(), false]);

            if(empty($recovery))
            {
                throw new ExceptionValidation('Неверные данные для восстановления');
            }

            return $recovery;
        }

        private function checkCode(int $code): void
        {
            if($code != $this->paramsInput['code'])
            {
                $error = json_encode(['code' => 'Неверно введен код'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }
        }

        private function saveDB(array &$recovery): void
        {
            $recovery['activate'] = true;

            $id = \R::store(\R::convertToBean('zaselite_recovery', $recovery));

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }
    }