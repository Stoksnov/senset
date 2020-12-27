<?php

    namespace Model\Page;

    use Essences\Order;
    use Essences\Subscribe;
    use Essences\Tariff;
    use Essences\User;
    use Exception\ExceptionAccess;
    use Exception\ExceptionUpdateDB;

    final class ModelPaySuccessful extends AbstractModelPage
    {

        private const PAYED = 7;

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->changeLastPay();
        }

        private function changeLastPay(): void
        {
            $pay = \R::getRow('SELECT * FROM zaselite_pay WHERE user_id = ? AND successful = ? AND payed = ? ORDER BY ID DESC LIMIT 1',
                [User::getInstance()->getId(), false, true]);

            if(empty($pay))
            {
                throw new ExceptionAccess;
            }

            $this->updateDB($pay);
            $this->addSubscribe($pay['tariff_id']);
        }

        private function updateDB(array &$pay): void
        {
            $pay['successful'] = true;

            $id = \R::store(\R::convertToBean('zaselite_pay', $pay));

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        private function addSubscribe(int $tariffId): void
        {
            $tariff = Tariff::getInstanceId($tariffId);

            Subscribe::saveDB(User::getInstance()->getId(), $tariff);
            Subscribe::insertStoryDB(User::getInstance()->getId(), $tariff->getId());

            $order = new Order(User::getInstance()->getPhone(), self::PAYED, User::getInstance()->getRoleId());
            $order->saveDB();
        }
    }