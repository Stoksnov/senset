<?php

    namespace Essences\DirectorObject;

    use Essences\ObjectEstate;

    final class DirectorObjectPage extends AbstractDirectorObject
    {

        public function __construct(ObjectEstate $object)
        {
            parent::__construct($object);
        }

        protected function optional(): void
        {
            $this->changeDeposit();
            $this->changeDateAdd();
            $this->changeDateCheck();
            $this->decodeDescription();

            if($this->object->getProperty('type') == 'Продажа')
            {
                $this->calculatePricePerSquareMeter();
            }
        }

        private function changeDeposit(): void
        {
            if(empty($this->object->getProperty('deposit')))
            {
                $this->object->setProperty('deposit', 'Без залога');
            }
        }

        private function changeDateAdd(): void
        {
            $date = date('d.m.Y H:i', strtotime($this->object->getProperty('date_add')));

            $this->object->setProperty('date_add', $date);
        }

        private function changeDateCheck(): void
        {
            $date = date('d.m.Y H:i', strtotime($this->object->getProperty('date_check')));

            $this->object->setProperty('date_check', $date);
        }

        private function decodeDescription(): void
        {
            $description = $this->object->getProperty('description');

            $description = decodeTextDB($description);
            
            $this->object->setProperty('description', $description);
        }

        private function calculatePricePerSquareMeter(): void
        {
            $price = $this->object->getProperty('price');
            $square = $this->object->getProperty('square');

            $pricePerSquareMeter = round($price / $square, 2);
            $pricePerSquareMeter = number_format($pricePerSquareMeter, 2, '.', ' ');

            $this->object->setOption('pricePerSquareMeter', $pricePerSquareMeter);
        }

        protected function changeRooms(): void
        {
            parent::changeRooms();

            $this->object->setOption('houses', self::HOUSES);

            $rooms = $this->object->getProperty('rooms');

            if($rooms == self::ROOM)
            {
                $this->object->setOption('rooms', 'КОМН.');
                $this->object->setOption('roomsGeneral', $rooms);
                return;
            }
            elseif($rooms == self::STUDIO)
            {
                $this->object->setOption('rooms', $rooms);
                $this->object->setOption('roomsGeneral', $rooms);
                return;
            }
            elseif(strpos(self::HOUSES, $rooms) !== false)
            {
                $this->object->setOption('rooms', $rooms);
                $this->object->setOption('roomsGeneral', $rooms);
                return;
            }

            if($this->object->getOption('countRooms') == '4+')
            {
                $this->object->setOption('rooms', $this->object->getOption('countRooms') . 'к');
            }
            else
            {
                $this->object->setOption('rooms', $this->object->getOption('countRooms') . '-к');
            }
        }
    }