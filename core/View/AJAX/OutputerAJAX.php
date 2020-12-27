<?php

    namespace View\AJAX;

    use View\Interfaces\InterfaceView;

    final class OutputerAJAX implements InterfaceView
    {
        private $data = [];

        public function setData(array $data, int $codeError = 0): void
        {
            $data['codeError'] = $codeError;
            $this->data = $data;
        }

        public function getData(): string
        {
            return json_encode($this->data, JSON_UNESCAPED_UNICODE);
        }

        public function out(array $data): void
        {
            $data['codeError'] = 0;

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
    }