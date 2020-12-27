<?php

    namespace View\AJAX;

    use Essences\EngineTemplate;
    use View\Interfaces\InterfaceView;

    abstract class AbstractTemplate implements InterfaceView
    {

        protected $template;

        protected function __construct(string $template)
        {
            $this->template = $template;
        }

        public function out(array $data): void
        {
            $engineTemplate = new EngineTemplate;

            $template = $engineTemplate->render($this->template . '.pug', $data);

            $outputer = new OutputerAJAX;

            $outputer->out(['template' => $template]);
        }
    }