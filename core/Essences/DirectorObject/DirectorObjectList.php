<?php

    namespace Essences\DirectorObject;

    use Essences\ObjectEstate;

    final class DirectorObjectList extends AbstractDirectorObject
    {

        public function __construct(ObjectEstate $object)
        {
            parent::__construct($object);
        }

        protected function optional(): void
        {

        }
    }