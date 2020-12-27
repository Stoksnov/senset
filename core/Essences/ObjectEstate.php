<?php

    namespace Essences;

    use Exception\ExceptionUpdateDB;
    use Exception\ExceptionValidation;

    final class ObjectEstate
    {

        private $properties;
        private $options;

        private function __construct(array $data)
        {
            $this->properties = $data;
            $this->options = [];
        }

        public static function getInstanceData(array $data): self
        {
            return new self($data);
        }

        public static function getInstanceId(int $id): self
        {
            $object = \R::getRow('SELECT * FROM crm_objects WHERE ID = ?', [$id]);

            if(empty($object))
            {
                throw new ExceptionValidation("Неверный параметр ID {$id}");
            }

            return self::getInstanceData($object);
        }

        public function getInformationFull(): array
        {
            return array_merge($this->properties, $this->options);
        }

        public function getOption(string $key)
        {
            if(!array_key_exists($key, $this->options))
            {
                throw new ExceptionValidation("Неверный параметр {$key}");
            }

            return $this->options[$key];
        }

        public function setOption(string $key, $value): void
        {
            $this->options[$key] = $value;
        }

        public function getProperty(string $key)
        {
            if(!array_key_exists($key, $this->properties))
            {
                throw new ExceptionValidation("Неверный параметр {$key}");
            }

            return $this->properties[$key];
        }

        public function setProperty(string $key, $value): void
        {
            if(!array_key_exists($key, $this->properties))
            {
                throw new ExceptionValidation("Неверный параметр {$key}");
            }

            $this->properties[$key] = $value;
        }

        public function updateDB(): void
        {
            $result = \R::store(\R::convertToBean('crm_objects', $this->properties));

            if($result == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        private function __clone()
        {

        }
    }