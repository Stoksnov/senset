<?php

    namespace Model\Page;

    use Essences\DirectorObject\DirectorObjectList;
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
        }

    }