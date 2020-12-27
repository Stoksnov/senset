<?php

    namespace Essences\Filter;

    use Essences\AccessManager\AccessManagerAuthorization;
    use Essences\AccessManager\AccessManagerBuyerMin;
    use Essences\AccessManager\AccessManagerRenterMin;
    use Essences\Locator;
    use Essences\User;
    use Exception\ExceptionValidation;
    use mysql_xdevapi\Result;
    use Request\RequestCookie;

    final class FilterEstates extends AbstractFilter
    {

        private const ACTUAL = 1;
        private const COOPERATE = 2;
        private const ARCHIVE = 5;
        private const BLACKLIST = 6;

        private $type;

        public function __construct(array $paramsInput)
        {
            parent::__construct($paramsInput, '*', 'crm_objects');
        }

        public function run(): void
        {
            $this->setTable('');
            $this->setType();
            $this->generateWhere();
        }

        public function getLimitMap(int $start): array
        {
            $this->setParamsOutput('ID, lat, lng');

            return $this->getDB($start, 100000);
        }

        protected function setTable(string $table): void
        {
            $accessor = new AccessManagerAuthorization;

            if($accessor->isCheck())
            {
                $userId = User::getInstance()->getId();
                $this->table .= ' LEFT OUTER JOIN';
                $this->table .= ' zaselite_favorites ON crm_objects.ID = zaselite_favorites.object_id AND zaselite_favorites.user_id = '.$userId;
            }
        }

        private function setType(): void
        {
            if(array_key_exists('type', $this->paramsInput) && !empty($this->paramsInput['type']))
            {
                $type = $this->paramsInput['type'];
            }
            else
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
            }

            $this->type = $type;
        }

        protected function generateWhere(): void
        {
            $arraySQL[] = $this->getAccessParams();

            $this->pushQuery($arraySQL, 'city = ?', Locator::getInstance()->getCity());

            $this->pushQuery($arraySQL, 'type = ?', $this->type);

            $this->pushQuery($arraySQL, 'owner_type = ?', '2');

            $arraySQL[] = $this->getNecessaryRooms();

            $paramsIdenticalDB = ['metro', 'district'];

            foreach($paramsIdenticalDB as $searchParam)
            {
                if(array_key_exists($searchParam, $this->paramsInput) && !empty($this->paramsInput[$searchParam]))
                {
                    if(is_array($this->paramsInput[$searchParam]))
                    {
                        $queries = [];

                        foreach($this->paramsInput[$searchParam] as $key => $value)
                        {
                            if(empty($value))
                            {
                                continue;
                            }

                            $this->pushQuery($queries, "{$searchParam} = ?", $value);
                        }

                        if(empty($queries))
                        {
                            continue;
                        }

                        $arraySQL[] = '(' . implode(' OR ', $queries) . ')';
                    }
                    else
                    {
                        $this->pushQuery($arraySQL, "{$searchParam} = ?", $this->paramsInput[$searchParam]);
                    }
                }
            }

            $searchParam = 'rooms';

            if(array_key_exists($searchParam, $this->paramsInput) && !empty($this->paramsInput[$searchParam]))
            {
                if(is_array($this->paramsInput[$searchParam]))
                {
                    $queries = [];

                    foreach($this->paramsInput[$searchParam] as $key => $value)
                    {
                        if(empty($value))
                        {
                            continue;
                        }

                        if($value == 'Многокомнатная')
                        {
                            $this->pushQuery($queries, "{$searchParam} = ?", '4-к квартира');
                            $this->pushQuery($queries, "{$searchParam} = ?", 'Многокомнатная');
                        }
                        else
                        {
                            $this->pushQuery($queries, "{$searchParam} = ?", $value);
                        }
                    }

                    if(!empty($queries))
                    {
                        $arraySQL[] = '(' . implode(' OR ', $queries) . ')';
                    }
                }
                else
                {
                    $this->pushQuery($arraySQL, "{$searchParam} = ?", $this->paramsInput[$searchParam]);
                }
            }

            $paramsIdenticalDB = ['price', 'square', 'floor'];

            foreach($paramsIdenticalDB as $searchParam)
            {
                $key = "{$searchParam}Min";

                if(array_key_exists($key, $this->paramsInput) && !empty($this->paramsInput[$key])
                    && is_numeric($this->paramsInput[$key]))
                {
                    $this->pushQuery($arraySQL, "{$searchParam} >= ?", $this->paramsInput[$key]);
                }

                $key = "{$searchParam}Max";

                if(array_key_exists($key, $this->paramsInput) && !empty($this->paramsInput[$key])
                    && is_numeric($this->paramsInput[$key]))
                {
                    $this->pushQuery($arraySQL, "{$searchParam} <= ?", $this->paramsInput[$key]);
                }
            }

            $searchParam = "notFloor";

            if(array_key_exists($searchParam, $this->paramsInput) && !empty($this->paramsInput[$searchParam]))
            {
                foreach($this->paramsInput[$searchParam] as $value)
                {
                    $floor = '';

                    if($value == 'notFirst')
                    {
                        $floor = '1';
                    }
                    elseif($value == 'notLast')
                    {
                        $floor = 'floors';
                    }

                    if(!empty($floor))
                    {
                        $this->pushQuery($arraySQL, "floor != ?", $floor);
                    }
                }
            }

            if(array_key_exists('polygon', $this->paramsInput) && !empty($this->paramsInput['polygon']))
            {
                $query = "ST_Contains(ST_PolyFromText(?), Point(lat, lng)) = 1";
                $this->pushQuery($arraySQL, $query, $this->paramsInput['polygon']);
            }

            $paramsIdenticalDB = ['amenities_balcony', 'amenities_refrigerator', 'amenities_washing',
                'amenities_dishwasher', 'amenities_heater', 'amenities_phone', 'amenities_appliances',
                'amenities_tv', 'amenities_conditioner', 'amenities_kettle', 'amenities_microwave',
                'conditions_smoke', 'conditions_events', 'conditions_animals', 'conditions_family'];

            foreach($paramsIdenticalDB as $searchParam)
            {
                if(array_key_exists($searchParam, $this->paramsInput) && !empty($this->paramsInput[$searchParam])
                    && $this->paramsInput[$searchParam] == 'on')
                {
                    $this->pushQuery($arraySQL, "{$searchParam} = ?", 1);
                }
            }

            $this->where = implode(' AND ', $arraySQL);
        }

        protected function getSortParam(): string
        {
            $sort = 'ORDER BY';

            if(array_key_exists('sort', $this->paramsInput))
            {
                switch($this->paramsInput['sort'])
                {
                    case 'date':
                        $sort = $sort . ' ' . $this->getSortDateParam();
                        break;
                    case 'price':
                        $sort .= ' price';
                        break;
                    case 'square':
                        $sort .= ' square';
                        break;
                    default:
                        throw new ExceptionValidation('Неверный параметр sort');
                }
            }
            else
            {
                $sort = $sort . ' ' . $this->getSortDateParam();
            }

            return $sort . ' DESC, rating_zaselite DESC';
        }

        private function getSortDateParam(): string
        {
            if($this->checkAccess())
            {
                return 'date_check';
            }

            return 'date_add';
        }

        private function getAccessParams(): string
        {
            $result = [];

            /*
            if($this->checkAccess())
            {
                $this->pushQuery($result, 'state = ?', self::ACTUAL);
                $this->pushQuery($result, 'state = ?', self::COOPERATE);
            }
            else
            {
                $this->pushQuery($result, 'state != ?', self::ARCHIVE);
                $this->pushQuery($result, 'state != ?', self::BLACKLIST);
            }*/

            $this->pushQuery($result, 'state != ?', self::ARCHIVE);
            $this->pushQuery($result, 'state != ?', self::BLACKLIST);

            return '(' . implode(' OR ', $result) . ')';
        }

        private function checkAccess(): bool
        {
            $accessor = null;

            if($this->type == 'Продажа')
            {
                $accessor = new AccessManagerBuyerMin;
            }
            elseif($this->type == 'Аренда')
            {
                $accessor = new AccessManagerRenterMin;
            }

            return (!empty($accessor) && $accessor->isCheck());
        }

        private function getNecessaryRooms(): string
        {
            $queries = [];

            $params = ['Комната', 'Студия', '1-к квартира', '2-к квартира', '3-к квартира', '4-к квартира', 'Многокомнатная',
                'Коттедж', 'Дом', 'Таунхаус'];

            foreach($params as $param)
            {
                $this->pushQuery($queries, "rooms = ?", $param);
            }

            return '(' . implode(' OR ', $queries) . ')';
        }
    }