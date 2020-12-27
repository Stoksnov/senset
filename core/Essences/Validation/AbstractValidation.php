<?php

    namespace Essences\Validation;

    use Exception\ExceptionValidation;

    abstract class AbstractValidation implements InterfaceValidation
    {

        protected $validator;

        public function __construct(array $data)
        {
            $this->validator = new Validator($data);
        }

        final public function run(): void
        {
            $this->validator
                ->isNotEmptyInput()
                ->setMessage('Данные отсутствуют');

            $this->validation();

            if($this->validator->isErrors())
            {
                throw new ExceptionValidation($this);
            }
        }

        final public function __toString(): string
        {
            return json_encode($this->validator->getMessages(), JSON_UNESCAPED_UNICODE);
        }

        abstract protected function validation(): void;
    }