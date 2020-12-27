<?php

    namespace Model\Page;

    use Essences\Tariff;
    use Essences\User;
    use Exception\ExceptionUpdateDB;

    final class ModelPay extends AbstractModelPage
    {

        //https://www.walletone.com/ru/merchant/documentation/
        private $tariffId;

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $payId = $this->savePayDB();
            $this->generateParamsAPI($payId);
        }

        private function savePayDB(): int
        {
            $this->tariffId = $this->paramsInput[1];

            $pay = \R::xdispense('zaselite_pay');

            $pay->tariff_id = (int) $this->tariffId;
            $pay->user_id = User::getInstance()->getId();
            $pay->successful = false;
            $pay->payed = false;

            $id = \R::store($pay);

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }

            return $id;
        }

        private function generateParamsAPI(int $payId): void
        {
            $tariff = $this->getTariff();

            $key = "66394f3142514e677a5d776b6b6d58684d596a6e754e7b44567736";

            $fields = [];

            $fields["WMI_MERCHANT_ID"]    = "159268752204";
            $fields["WMI_PAYMENT_AMOUNT"] = $tariff->getPrice() . ".00";
            $fields["WMI_CURRENCY_ID"]    = "643";
            $fields["WMI_PAYMENT_NO"]     = $payId;
            $fields["WMI_DESCRIPTION"]    = "BASE64:".base64_encode($this->getDescription($tariff));
            $fields["WMI_SUCCESS_URL"]    = "https://zaselite.ru/paysuccessful";
            $fields["WMI_FAIL_URL"]       = "https://zaselite.ru/payerror";
            $fields["WMI_AUTO_LOCATION"]  = 0;
            $fields["WMI_AUTO_ADJUST_AMOUNT"] = 0;
            $fields['WMI_PTENABLED'] = 'CreditCardRUB';

            foreach($fields as $name => $val)
            {
                if(is_array($val))
                {
                    usort($val, "strcasecmp");
                    $fields[$name] = $val;
                }
            }

            uksort($fields, "strcasecmp");
            $fieldValues = "";

            foreach($fields as $value)
            {
                if(is_array($value))
                    foreach($value as $v)
                    {
                        $v = iconv("utf-8", "windows-1251", $v);
                        $fieldValues .= $v;
                    }
                else
                {
                    $value = iconv("utf-8", "windows-1251", $value);
                    $fieldValues .= $value;
                }
            }

            $signature = base64_encode(pack("H*", md5($fieldValues . $key)));

            $fields["WMI_SIGNATURE"] = $signature;

            $formPay['form'] = $fields;
            $this->paramsOutput = array_merge($this->paramsOutput, $formPay);
        }

        private function getTariff(): Tariff
        {
            return Tariff::getInstanceId($this->tariffId);
        }

        private function getDescription(Tariff $tariff): string
        {
            return 'Тариф "' . $tariff->getName() . '" на zaselite.ru #' . ($this->getCountStories() + 1);
        }

        private function getCountStories(): int
        {
            return \R::getCell('SELECT COUNT(*) FROM zaselite_stories');
        }
    }