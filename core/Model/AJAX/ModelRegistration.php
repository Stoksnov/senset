<?php

    namespace Model\AJAX;

    use Essences\Order;
    use Essences\User;

    final class ModelRegistration extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->registration();
            $this->updateOrder();
        }

        private function registration(): void
        {
            User::signup($this->paramsInput);
        }

        private function updateOrder(): void
        {
            $phone = $this->paramsInput['phone'];
            $roleId = $this->paramsInput['roleId'];
            $action = 8;

            $order = new Order($phone, $action, $roleId);
            $order->saveDB();
        }
    }