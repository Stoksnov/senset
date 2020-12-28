<?php

    namespace Essences;

    final class EngineTemplate
    {

        private $engine;

        public function __construct()
        {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

            /*$loader = new \Twig\Loader\FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/dist');

            $this->engine = new \Twig\Environment($loader, array(
                'cache' => $_SERVER['DOCUMENT_ROOT'] . '/core/View/CompilationCache',
                'debug' => true
            ));

            $timezone = Locator::getInstance()->getTimezone();
            $this->engine->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone($timezone);*/


            $this->engine = new \Pug([
                'cache' => $_SERVER['DOCUMENT_ROOT'] . '/core/View/CompilationCache',
                'basedir' => $_SERVER['DOCUMENT_ROOT'] . '/src/pug/pages',
                'upToDateCheck' => false
            ]);
        }

        public function render(string $template, array $data): string
        {
            return $this->engine->render($_SERVER['DOCUMENT_ROOT'] . '/src/pug/pages/' . $template, $data);
        }
    }