<?php

    namespace Model\Page;

    use Essences\User;

    final class ModelPayError extends AbstractModelPage
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->deleteErrorPay();
        }

        private function deleteErrorPay(): void
        {
            \R::exec('DELETE FROM zaselite_pay WHERE user_id = ? AND successful = ? AND payed = ?', [User::getInstance()->getId(), false, false]);
        }
    }