<?php

    namespace Essences;

    use Exception\ExceptionUploadFile;

    final class FTPClient
    {

        private $connect;

        public function __construct()
        {
            $this->connect = ftp_connect('91.210.168.146', 21);
            $login = ftp_login($this->connect, 'agentumupload', '98agentum98');

            if(!$this->connect || !$login || !ftp_pasv($this->connect, true))
            {
                throw new ExceptionUploadFile('Не удалось подключиться к серверу');
            }
        }

        public function getServerName(): string
        {
            return 'https://photo-object.ru';
        }

        public function getContentsDirectory(): array
        {
            $contents = ftp_nlist($this->connect, '.');

            if($contents === false)
            {
                throw new ExceptionUploadFile('Не удалось получить содержимое директории');
            }

            return $contents;
        }

        public function createDirectory(string $dir): void
        {
            if(!ftp_mkdir($this->connect, $dir) || !ftp_site($this->connect, 'chmod 0777 '.$dir))
            {
                throw new ExceptionUploadFile('Не удалось создать директорию');
            }
        }

        public function setDirectory(string $dir): void
        {
            if(!ftp_chdir($this->connect, $dir))
            {
                throw new ExceptionUploadFile('Не удалось сменить директорию');
            }
        }

        public function generateFileName(string $extension = ''): string
        {
            $extension = $extension ? '.'.$extension : '';

            $contents = $this->getContentsDirectory();

            $fileName = '';

            do{
                $name = uniqid();
                $fileName = $name.$extension;
            }while(in_array($fileName, $contents));

            return $fileName;
        }

        public function put(string $fileLocal, string $fileRemote): void
        {
            if(!ftp_put($this->connect, $fileRemote, $fileLocal, FTP_BINARY) || !ftp_chmod($this->connect, 0777, $fileRemote))
            {
                throw new ExceptionUploadFile('Не удалось загрузить файл');
            }
        }

        public function delete(string $file): void
        {
            if(!ftp_delete($this->connect, $file))
            {
                throw new ExceptionUploadFile('Не удалось удалить файл');
            }
        }

        public function close(): void
        {
            if(!ftp_close($this->connect))
            {
                throw new ExceptionUploadFile('Не удалось закрыть соединение с сервером');
            }
        }
    }