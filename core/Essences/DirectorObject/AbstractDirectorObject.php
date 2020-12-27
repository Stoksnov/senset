<?php

    namespace Essences\DirectorObject;

    use Essences\GeneratorStatuses\AbstractGeneratorStatusesBots;
    use Essences\GeneratorStatuses\AbstractGeneratorStatusesCheck;
    use Essences\Locator;
    use Essences\ObjectEstate;
    use Essences\User;
    use Exception\ExceptionValidation;

    abstract class AbstractDirectorObject implements InterfaceDirectorObject
    {

        protected const ROOM = 'Комната';
        protected const FLAT = 'Квартира';
        protected const STUDIO = 'Студия';
        protected const HOUSES = 'Дом,Коттедж,Таунхаус';

        protected $object;

        protected function __construct(ObjectEstate $object)
        {
            $this->object = $object;
        }

        final public function change(): void
        {
            $this->generateAddress();
            $this->setFavorite();
            $this->changePrice();
            $this->changeRooms();
            $this->setIconMetro();
            $this->changeImages();
            $this->generateStatus();
            $this->optional();
        }

        protected function generateAddress(): void
        {
            $address = '';
            $street = $this->object->getProperty('adres');
            $district = $this->object->getProperty('district');

            if(!empty($district))
            {
                $address .= $district . ' р-н';
            }

            if(!empty($street))
            {
                if(!empty($address))
                {
                    $address .= ', ';
                }

                $address .= $street;
            }

            $this->object->setOption('address', $address);
        }

        protected function setFavorite(): void
        {
            if(!User::getInstance()->getAuth())
            {
                $this->object->setOption('favorite', false);
                return;
            }

            $userId = User::getInstance()->getId();
            $objectId = $this->object->getProperty('ID');

            $favorite = \R::count('zaselite_favorites','user_id = ? AND object_id = ?', [$userId, $objectId]) > 0;

            $this->object->setOption('favorite', $favorite);
        }

        protected function changePrice(): void
        {
            $price = $this->object->getProperty('price');

            $price = number_format($price, 0, '', ' ');

            $this->object->setOption('price', $price);
        }

        protected function changeRooms(): void
        {
            $rooms = $this->object->getProperty('rooms');

            if($rooms == self::ROOM)
            {
                $this->object->setOption('roomsGeneral', 'КОМН.');
                $this->object->setOption('roomsType', self::ROOM);
                return;
            }
            elseif($rooms == self::STUDIO)
            {
                $this->object->setOption('roomsGeneral', $rooms);
                $this->object->setOption('roomsType', 'Тип');
                return;
            }
            elseif(strpos(self::HOUSES, $rooms) !== false)
            {
                $this->object->setOption('roomsGeneral', $rooms);
                $this->object->setOption('roomsType', 'Тип');
                return;
            }

            $countRooms = $this->getCountRooms();

            $this->object->setOption('roomsGeneral', $countRooms . ' КОМН.');
            $this->object->setOption('countRooms', $countRooms);

            $this->object->setOption('roomsType', self::FLAT);
        }

        protected function getCountRooms(): string
        {
            $rooms = $this->object->getProperty('rooms');

            if($rooms == 'Многокомнатная')
            {
                return '4+';
            }

            $countRooms = preg_replace('/[^0-9]/', '', $rooms);

            if($countRooms == $rooms || $countRooms === null)
            {
                throw new ExceptionValidation('Неверный формат rooms');
            }

            if($countRooms == '4')
            {
                return '4+';
            }

            return $countRooms;
        }

        protected function setIconMetro(): void
        {
            $metro = $this->object->getProperty('metro');

            if(empty($metro))
            {
                $this->object->setOption('iconMetro', '');
                return;
            }

            $iconMetro = Locator::getInstance()->getIconMetro($metro);

            $this->object->setOption('iconMetro', $iconMetro);
        }

        protected function changeImages(): void
        {
            $images = json_decode($this->object->getProperty('images_rent'), true);

            if(!empty($images))
            {
                $images = array_values($images);
            }
            else
            {
                $images = [];
            }

            $this->object->setProperty('images', $images);
        }

        final protected function generateStatus(): void
        {
            $statusBot = new AbstractGeneratorStatusesBots;
            $statusCheck = new AbstractGeneratorStatusesCheck;

            $statusBot->setNext($statusCheck);

            $statusBot->run($this->object);
        }

        abstract protected function optional(): void;
    }