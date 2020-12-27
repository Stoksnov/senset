<?php

    namespace View\AJAX;

    use Essences\EngineTemplate;

    final class MapObjects extends AbstractTemplate
    {

        public function __construct()
        {
            parent::__construct('pages/find/map-card');
        }

        public function out(array $data): void
        {
            $engineTemplate = new EngineTemplate;

            $output = [];
            foreach ($data['objects'] as &$object)
            {
                array_push($output, $engineTemplate->render($this->template . '.pug', ['object' => $object]));
            }

            $outputer = new OutputerAJAX;
            $outputer->out(['cards' => $output]);
        }
    }