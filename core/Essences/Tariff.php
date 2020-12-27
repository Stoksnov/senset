<?php

    namespace Essences;

    use Exception\ExceptionValidation;

    final class Tariff
    {

        private $id;
        private $roleId;
        private $type;
        private $price;
        private $name;

        public function __construct(int $roleId, int $type, string $name = '', int $price = 0, int $id = 0)
        {
            $this->id = $id;
            $this->roleId = $roleId;
            $this->type = $type;
            $this->name = $name;
            $this->price = $price;
        }

        public static function getInstanceId(int $id): self
        {
            $tariff = \R::load('zaselite_tariffs', $id);

            if(empty($tariff))
            {
                throw new ExceptionValidation('Неверный параметр tariffId');
            }

            return new self((int) $tariff->roleId, (int) $tariff->type, $tariff->name, $tariff->price, $id);
        }

        public function getId(): int
        {
            return $this->id;
        }

        public function getType(): int
        {
            return $this->type;
        }

        public function getRoleId(): string
        {
            return $this->roleId;
        }

        public function getPrice(): int
        {
            return $this->price;
        }

        public function getName(): string
        {
            return $this->name;
        }
    }