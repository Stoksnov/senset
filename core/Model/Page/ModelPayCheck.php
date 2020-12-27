<?php

    namespace Model\Page;

    use Essences\Tariff;
    use Exception\ExceptionAccess;
    use Exception\ExceptionCustom;

    final class ModelPayCheck extends AbstractModelPage
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            try
            {
                $this->paramsInput = $_POST;
                $this->checkInput();
            }
            catch(ExceptionCustom $exception)
            {
                return;
            }
        }

        private function checkInput(): void
        {
            $skey = "66394f3142514e677a5d776b6b6d58684d596a6e754e7b44567736";

            if (!isset($this->paramsInput["WMI_SIGNATURE"]))
                $this->addOutput("Retry", "Отсутствует параметр WMI_SIGNATURE");

            if (!isset($this->paramsInput["WMI_PAYMENT_NO"]))
                $this->addOutput("Retry", "Отсутствует параметр WMI_PAYMENT_NO");

            if (!isset($this->paramsInput["WMI_ORDER_STATE"]))
                $this->addOutput("Retry", "Отсутствует параметр WMI_ORDER_STATE");


            foreach($this->paramsInput as $name => $value)
            {
                if ($name !== "WMI_SIGNATURE") $params[$name] = $value;
            }

            uksort($params, "strcasecmp"); $values = "";

            foreach($params as $name => $value)
            {

                $values .= $value;
            }

            $signature = base64_encode(pack("H*", md5($values . $skey)));

            if ($signature == $this->paramsInput["WMI_SIGNATURE"])
            {
                if (strtoupper($this->paramsInput["WMI_ORDER_STATE"]) == "ACCEPTED")
                {
                    $this->addPay();
                    $this->addOutput("Ok", "Заказ #" . $this->paramsInput["WMI_PAYMENT_NO"] . " оплачен!");
                }
                else
                {
                    $this->addOutput("Retry", "Неверное состояние ". $this->paramsInput["WMI_ORDER_STATE"]);
                }
            }
            else
            {
                $this->addOutput("Retry", "Неверная подпись " . $this->paramsInput["WMI_SIGNATURE"]);
            }
        }

        private function addOutput(string $result, string $description): void
        {
            $this->paramsOutput['answer'] = "WMI_RESULT=" . strtoupper($result) . "&WMI_DESCRIPTION=" .$description;
            throw new ExceptionAccess;
        }

        private function addPay(): void
        {
            $pay = $this->getPay();
            $tariff = $this->getTariff($pay['tariff_id']);

            $this->checkPrice($tariff->getPrice());
            $this->updateDB($pay);
        }

        private function getPay(): array
        {
            $pay = \R::getRow('SELECT * FROM zaselite_pay WHERE ID = ?', [$this->paramsInput["WMI_PAYMENT_NO"]]);

            if(empty($pay))
            {
                $this->outputError();
            }

            return $pay;
        }

        private function outputError(): void
        {
            $this->addOutput('Retry', 'Внутренняя ошибка сервера');
        }

        private function getTariff(int $tariffId): Tariff
        {
            try
            {
                $tariff = Tariff::getInstanceId($tariffId);
            }
            catch(ExceptionCustom $exc)
            {
                $this->outputError();
            }

            return $tariff;
        }

        private function checkPrice(int $price): void
        {
            if($this->paramsInput['WMI_PAYMENT_AMOUNT'] != $price . '.00')
            {
                $this->outputError();
            }
        }

        private function updateDB(array &$pay): void
        {
            $pay['payed'] = true;

            $id = \R::store(\R::convertToBean('zaselite_pay', $pay));

            if($id == 0)
            {
                $this->outputError();
            }
        }
    }