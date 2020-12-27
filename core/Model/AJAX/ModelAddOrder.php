<?php

    namespace Model\AJAX;

    use Essences\ObjectEstate;
    use Essences\Order;

    final class ModelAddOrder extends AbstractModelAJAX
    {

        private const LEAD = 0;
        private const ROLE_ID = 2;

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->addOrder();
        }

        private function addOrder(): void
        {
            $phone = $this->paramsInput['phone'];

            if(Order::isOrder($phone))
            {
                $order = new Order($phone, self::LEAD, self::ROLE_ID);

                $params = ['name' => $this->paramsInput['name']];
                $order->insertDB($params);
            }
        }
    }