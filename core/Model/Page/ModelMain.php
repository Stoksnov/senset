<?php

    namespace Model\Page;

    use Essences\DirectorObject\DirectorObjectList;
    use Essences\Locator;
    use Essences\ObjectEstate;
    use Essences\User;
    use Request\RequestCookie;

    final class ModelMain extends AbstractModelPage
    {

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->generateLastCheckObjects();
        }

        private function generateLastCheckObjects(): void
        {
            $objects = $this->getObjectsDB();

            foreach($objects as &$object)
            {
                $object = ObjectEstate::getInstanceData($object);

                $director = new DirectorObjectList($object);
                $director->change();
            }

            $this->paramsOutput['objects'] = $objects;
        }

        private function getObjectsDB(): array
        {
            $type = $this->getObjectType();

            $where = "city = ? AND type = ? AND (state = '1' OR state = '2') AND owner_type = '2'";
            $where .= " AND (rooms = 'Комната' OR rooms = 'Студия' OR rooms = '1-к квартира' OR rooms = '2-к квартира' OR rooms = '3-к квартира' OR rooms = '4-к квартира' OR rooms = 'Многокомнатная' OR rooms = 'Коттедж' OR rooms = 'Таунхаус' OR rooms = 'Дом')";

            return \R::getAll("SELECT * FROM crm_objects WHERE {$where} ORDER BY date_check DESC LIMIT 0, 3", [
                Locator::getInstance()->getCity(), $type
            ]);
        }

        private function getObjectType(): string
        {
            $type = 'Продажа';

            $cookie = new RequestCookie;

            if($cookie->isSet('ZASELITE_TYPE'))
            {
                $type = $cookie->getValue('ZASELITE_TYPE');
            }
            elseif(User::getInstance()->getAuth())
            {
                $role = User::getInstance()->getRole();
                $type = $role['type'];
            }

            return $type;
        }
    }