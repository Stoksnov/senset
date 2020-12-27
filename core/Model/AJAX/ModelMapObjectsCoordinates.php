<?php

    namespace Model\AJAX;

    use Essences\Filter\FilterEstates;


    final class ModelMapObjectsCoordinates extends AbstractModelAJAX
    {

       public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            $this->generateCoordinatesObjects();
        }

        private function generateCoordinatesObjects(): void
        {
            $filter = new FilterEstates($this->paramsInput);
            $filter->run();

            $objects = $filter->getLimitMap(0);

            $this->paramsOutput['objects'] = $objects;
            $this->paramsOutput['countSearched'] = $filter->getCount();
        }
    }