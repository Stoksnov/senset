<?php

    namespace Essences\Filter;

    final class AutoSelectorEstates implements InterfaceFilter
    {

        private $filter;

        public function __construct(array $paramsInput)
        {
            $this->convertInput($paramsInput);
            $this->filter = new FilterEstates($paramsInput);
        }

        private function convertInput(array &$paramsInput): void
        {
            $params = ['rooms', 'district', 'metro'];

            foreach($params as $param)
            {
                if(array_key_exists($param, $paramsInput))
                {
                    $paramsInput[$param] = $this->convertParam($paramsInput[$param]);
                }
            }
        }

        private function convertParam(string $param): array
        {
            if(empty($param))
            {
                return [];
            }

            return explode(',', $param);
        }

        public function run(): void
        {
            $this->filter->run();
        }

        public function getCount(): int
        {
            return $this->filter->getCount();
        }

        public function getLimit(int $start, int $count): array
        {
            return $this->filter->getLimit($start, $count);
        }
    }