<?php

    namespace Model\Page;

    use Essences\Locator;
    use Essences\User;
    use Model\AbstractModel;
    use Request\RequestCookie;
    use View\Interfaces\InterfaceView;

    abstract class AbstractModelPage extends AbstractModel
    {

        protected $paramsInput;
        protected $paramsOutput;

        protected function __construct(array $data)
        {
            parent::__construct($data);
        }

        final public function run(InterfaceView $view): void
        {
            $this->generateGeneralOutput();
            parent::run($view);
        }

        final protected function generateGeneralOutput(): void
        {
            $this->paramsOutput['user'] = User::getInstance();
            $this->paramsOutput['cookie'] = new RequestCookie;
        }
    }