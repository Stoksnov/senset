<?php

    namespace Model\AJAX;

    use Essences\Order;

    final class ModelSale extends AbstractModelAJAX
    {

        private const LEAD = 0;

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->updateOrder();
        }

        private function updateOrder(): void
        {
            $phone = $this->paramsInput['phone'];
            $roleId = $this->paramsInput['roleId'];
            $name = $this->paramsInput['name'];

            if(Order::isOrder($phone))
            {
                $order = new Order($phone, self::LEAD, $roleId);

                $order->insertDB($this->paramsInput);
            }
        }
    }