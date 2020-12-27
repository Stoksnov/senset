<?php

    namespace Essences\Validation;

    use Exception\ExceptionValidation;

    final class Validator
    {

        private $data;
        private $activeParam;
        private $isError;
        private $isNecessary;
        private $messages;

        public function __construct(array $data)
        {
            $this->data = $data;
            $this->activeParam = array_key_first($this->data);
            $this->isError = false;
            $this->isNecessary = true;
            $this->messages = [];
        }

        public function setParam(string $key): self
        {
            $this->isError = $this->isNecessary = true;

            $this->activeParam = $key;

            if(array_key_exists($key, $this->data))
            {
                $this->isError = false;
            }

            return $this;
        }

        public function getParam(string $key): string
        {
            if(!array_key_exists($key, $this->data))
            {
                throw new ExceptionValidation("Неверный параметр {$key}");
            }

            return $this->data[$key];
        }

        public function setNotNecessary(): self
        {
            $this->isNecessary = false;

            return $this;
        }

        public function isNotEmptyInput(): self
        {
            if(!$this->isError && empty($this->data))
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isNotEmpty(): self
        {
            if(!$this->isError && $this->emptyActiveParam())
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isInt(): self
        {
            if(!$this->isError && !is_numeric($this->data[$this->activeParam]))
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isDate(): self
        {
            $date = explode('.', $this->data[$this->activeParam]);

            if(count($this->data) != 3)
            {
                $this->isError = true;
            }

            if(!$this->isError && !checkdate($date[1], $date[0], $date[2]))
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isIntervalInt(int $min, int $max): self
        {
            $this->isMaxInt($max)->isMinInt($min);

            return $this;
        }

        public function isMinInt(int $min): self
        {
            if(!$this->isError && intval($this->data[$this->activeParam]) < $min)
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isMaxInt(int $max): self
        {
            if(!$this->isError && intval($this->data[$this->activeParam]) > $max)
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isCheckBoxActive(): self
        {
            if(!$this->isError && $this->data[$this->activeParam] != 'on')
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isEmail(): self
        {
            $pattern = '/@/';

            if(!$this->isError && preg_match($pattern, $this->data[$this->activeParam]) != 1)
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isPhone(): self
        {
            $pattern = '/\+7 \([0-9][0-9][0-9]\) [0-9][0-9][0-9]\-[0-9][0-9]\-[0-9][0-9]$/';

            if(!$this->isError && preg_match($pattern, $this->data[$this->activeParam]) != 1)
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isParams(array $params, bool $strict = true): self
        {
            if(!$this->isError && !in_array($this->data[$this->activeParam], $params, $strict))
            {
                $this->isError = true;
            }

            return $this;
        }

        public function strlenMax(int $max): self
        {
            if(!$this->isError && strlen($this->data[$this->activeParam]) > $max)
            {
                $this->isError = true;
            }

            return $this;
        }

        public function strlenMin(int $min): self
        {
            if(!$this->isError && strlen($this->data[$this->activeParam]) < $min)
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isCustomComparator(callable $function): self
        {
            if(!$this->isError && !call_user_func($function, $this->data[$this->activeParam]))
            {
                $this->isError = true;
            }

            return $this;
        }

        public function isErrors(): bool
        {
            return !empty($this->messages);
        }

        public function getIsError(): bool
        {
            return $this->isError;
        }

        public function setMessage(string $message): self
        {
            if(!$this->isNecessary && $this->emptyActiveParam())
            {
                return $this;
            }

            if($this->isError && !array_key_exists($this->activeParam, $this->messages))
            {
                $this->messages[$this->activeParam] = $message;
            }

            return $this;
        }

        public function getMessages(): array
        {
            return $this->messages;
        }

        private function emptyActiveParam(): bool
        {
            return empty($this->data[$this->activeParam]);
        }
    }