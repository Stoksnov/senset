<?php

    namespace Model\AJAX;

    use Essences\AccessManager\FactoryAccessorContact;
    use Exception\ExceptionValidation;

    final class ModelContact extends AbstractModelAJAX
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $contact = $this->getContactDB();
            $this->checkAccess($contact['type']);

            $this->paramsOutput['phone'] = $contact['phone'];
            $this->paramsOutput['name'] = decodeTextDB($contact['name']);
        }

        private function getContactDB(): array
        {
            $contact = \R::getRow('SELECT phone, name, type FROM crm_objects WHERE ID = ?', [$this->paramsInput['objectId']]);

            if(empty($contact))
            {
                throw new ExceptionValidation('Неверный параметр objectId');
            }

            return $contact;
        }

        private function checkAccess(string $type): void
        {
            $accessor = FactoryAccessorContact::build($type);
            $accessor->check();
        }
    }