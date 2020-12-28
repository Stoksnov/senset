<?php

    namespace View\Page;

    use Essences\EngineTemplate;
    use View\Interfaces\InterfaceView;

    abstract class AbstractPage implements InterfaceView
    {

        protected $template ;
        protected $title;

        protected function __construct(string $template, string $title)
        {
            $this->template = $template;
            $this->title = $title;
        }

        public function out(array $data): void
        {
            $engineTemplate = new EngineTemplate;

            $data['title'] = $this->title;

            echo $engineTemplate->render($this->template . '.pug', $data);
        }
    }