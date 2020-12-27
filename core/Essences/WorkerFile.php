<?php

    namespace Essences;

    use Exception\ExceptionUploadFile;

    final class WorkerFile
    {

        private $destination;

        public function __construct(string $destination)
        {
            $this->destination = $destination;
        }

        public function setDestination(string $destination): void
        {
            $this->destination = $destination;
        }

        public function createDestination(): void
        {
            if(!$this->isFileExists($this->destination) && !mkdir($this->destination, 0777, true))
            {
                throw new ExceptionUploadFile('Не удалось создать директорию');
            }
        }

        public function upload(string $tmpName): string
        {
            $name = $this->generateName('jpg');

            if(!move_uploaded_file($tmpName, $this->destination.$name))
            {
                throw new ExceptionUploadFile('Не удалось загрузить файл');
            }

            return $name;
        }

        private function generateName(string $extension): string
        {
            $extension = '.'.$extension;

            do{
                $name = uniqid();
                $fileName = $name.$extension;
            }while($this->isFileExists($this->destination.$fileName));

            return basename($fileName);
        }

        public function delete(string $file): void
        {
            $path = $this->destination.$file;

            if(!$this->isFileExists($path) || !unlink($path))
            {
                throw new ExceptionUploadFile('Не удалось удалить файл');
            }
        }

        private function isFileExists(string $file): bool
        {
            return file_exists($file);
        }
    }