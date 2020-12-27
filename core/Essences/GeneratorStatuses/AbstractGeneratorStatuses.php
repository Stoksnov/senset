<?php

    namespace Essences\GeneratorStatuses;

    use Essences\ObjectEstate;

    abstract class AbstractGeneratorStatuses implements IGeneratorStatuses
    {

        private $next = null;

        final public function setNext(IGeneratorStatuses $next): void
        {
            $this->next = $next;
        }

        final protected function next(ObjectEstate $object): void
        {
            if($this->next !== null)
            {
                $this->next->run($object);
            }
        }

        abstract public function run(ObjectEstate $object): void;
    }