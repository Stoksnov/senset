<?php

    namespace Essences\Filter;

    abstract class AbstractFilter implements InterfaceFilter
    {

        protected $paramsInput;
        protected $paramsOutput;
        protected $params;
        protected $table;
        protected $where;
        protected $sort;

        protected function __construct(array $paramsInput, string $paramsOutput, string $table)
        {
            $this->paramsInput = $paramsInput;
            $this->params = [];
            $this->where = '';
            $this->sort = '';
            $this->paramsOutput = $paramsOutput;
            $this->table = $table;
        }

        public function run(): void
        {
            $this->generateWhere();
        }

        final public function getCount(): int
        {
            $this->setParamsOutput('COUNT(*)');

            return (int) \R::getCell($this->getSQL(), $this->params);
        }

        final public function getLimit(int $start, int $count): array
        {
            $this->setParamsOutput('*');

            return $this->getDB($start, $count);
        }

        protected function setTable(string $table): void
        {
            $this->table = $table;
        }

        final protected function setParamsOutput(string $params): void
        {
            $this->paramsOutput = $params;
        }

        final protected function pushQuery(array &$queries, string $query, string $value): void
        {
            $queries[] = $query;
            $this->params[] = $value;
        }

        final protected function getSQL(): string
        {
            $sql = "SELECT {$this->paramsOutput} FROM {$this->table} WHERE {$this->where}";

            return $sql;
        }

        protected function getSortParam(): string
        {
            return '';
        }

        final protected function getDB(int $start, int $count): array
        {
            return \R::getAll($this->getSQL() . $this->getSort() . " LIMIT {$start}, {$count}", $this->params);
        }

        abstract protected function generateWhere(): void;

        private function getSort(): string
        {
            $sort = $this->getSortParam();

            if(!empty($sort))
            {
                $sort = " {$sort}";
            }

            return $sort;
        }
    }