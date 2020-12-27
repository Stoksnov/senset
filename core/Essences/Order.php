<?php

    namespace Essences;

    use Exception\ExceptionUpdateDB;
    use http\Params;

    final class Order
    {

        private $phone;
        private $action;
        private $type;

        public function __construct(string $phone, int $action, int $roleId)
        {
            $this->phone = $phone;
            $this->action = $action;
            $this->type = \R::getCell('SELECT type FROM zaselite_roles WHERE ID = ?', [$roleId]);
        }

        public function saveDB(array &$optionalParams = []): void
        {
            if(static::isOrder($this->phone))
            {
                $this->insertDB($optionalParams);
            }
            else
            {
                $this->updateDB($optionalParams);
            }
        }

        public static function isOrder(string $phone): bool
        {
            return \R::count('moderators_renters', 'phone = ?', [$phone]) == 0;
        }

        public function insertDB(array &$optionalParams): void
        {
            $order = \R::xdispense('moderators_renters');

            $order->action = $this->action;
            $order->date_action = \R::isoDateTime();
            $order->task = json_encode([], JSON_UNESCAPED_UNICODE);
            $order->city = Locator::getInstance()->getCity();
            $order->phone = $this->phone;
            $order->type = $this->type;
            $order->edit = false;

            if(!empty($optionalParams))
            {
                $params = ['rooms', 'price_min', 'price_max', 'name'];

                foreach($params as $param)
                {
                    if(array_key_exists($param, $optionalParams) && !empty($optionalParams[$param]))
                    {
                        if($param == 'rooms' && is_array($optionalParams[$param]))
                        {
                            $order->{$param} = implode(',', $optionalParams[$param]);
                        }
                        else
                        {
                            $order->{$param} = $optionalParams[$param];
                        }
                    }
                }
            }

            $id = \R::store($order);

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        public function updateDB(array &$optionalParams): void
        {
            $order = \R::getRow('SELECT * FROM moderators_renters WHERE phone = ?', [$this->phone]);

            $order['action'] = $this->action;
            $order['date_action'] = \R::isoDateTime();
            $order['phone'] = $this->phone;
            $order['type'] = $this->type;

            if(!$order['edit'] && !empty($optionalParams))
            {
                $params = ['rooms', 'price_min', 'price_max', 'name'];

                foreach($params as $param)
                {
                    if(array_key_exists($param, $optionalParams) && !empty($optionalParams[$param]))
                    {
                        if($param == 'rooms' && is_array($optionalParams[$param]))
                        {
                            $order[$param] = implode(',', $optionalParams[$param]);
                        }
                        else
                        {
                            $order[$param] = $optionalParams[$param];
                        }
                    }
                }
            }

            $id = \R::store(\R::convertToBean('moderators_renters', $order));

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }
    }