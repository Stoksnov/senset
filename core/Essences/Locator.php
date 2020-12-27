<?php

    namespace Essences;

    use Dadata\DadataClient;
    use Request\RequestCookie;

    final class Locator
    {

        private $city;
        private $timezone;
        private $cityId;
        private $isMetro;
        private static $instance = null;

        private function __construct()
        {
            $cookie = new RequestCookie;

            if($cookie->isSet('ZASELITE_CITY'))
            {
                $this->city = $cookie->getValue('ZASELITE_CITY');
            }
            else
            {
                $this->setDefault();
                return;
            }

            $city = \R::findOne('cities', 'city = ?', [$this->city]);

            if(!empty($city))
            {
                $this->city = $city->city;
                $this->timezone = $city->timezone;
                $this->cityId = $city->id;
                $this->isMetro = $city->metro == 1;
            }
            else
            {
                $this->setDefault();
            }
        }

        private function setDefault(): void
        {
            $this->timezone = 'Asia/Novosibirsk';
            $this->city = 'Новосибирск';
            $this->cityId = 1;
            $this->isMetro = true;
        }

        public static function getInstance(): self
        {
            if(self::$instance === null)
            {
                self::$instance = new self;
            }

            return self::$instance;
        }

        public function getCity(): string
        {
            return $this->city;
        }

        public function getTimezone(): string
        {
            return $this->timezone;
        }

        public function getIsMetro(): bool
        {
            return $this->isMetro;
        }

        public function getDistricts(): array
        {
            return \R::getCol('SELECT name FROM districts WHERE city_id = ?', [$this->cityId]);
        }

        public function getMetro(): array
        {
            if(!$this->isMetro)
            {
                return [];
            }

            return \R::getCol('SELECT name FROM metro WHERE city_id = ?', [$this->cityId]);
        }

        public function getIconMetro(string $metro): string
        {
            if(empty($metro))
            {
                return '';
            }

            $icon = \R::getCell('SELECT icon FROM metro WHERE city_id = ? AND name = ?', [$this->cityId, $metro]);

            if(empty($icon))
            {
                return '';
            }

            return $icon;
        }

        public function getCities(): array
        {
            return \R::getCol('SELECT city FROM cities WHERE city != ? AND city != ?', [$this->city, 'Москва']);
        }

        private function getIp(): string
        {
            $keys = [
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'REMOTE_ADDR'
            ];

            foreach($keys as $key)
            {
                if(!empty($_SERVER[$key]))
                {
                    $array = explode(',', $_SERVER[$key]);
                    $ip = trim(end($array));
                    if(filter_var($ip, FILTER_VALIDATE_IP))
                    {
                        return $ip;
                    }
                }
            }

            return '';
        }

        private function __clone()
        {

        }
    }