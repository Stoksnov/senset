<?php

    namespace Model\AJAX;

    use Essences\User;
    use Essences\WorkerFile;
    use Request\RequestFiles;

    final class ModelUpdateProfile extends AbstractModelAJAX
    {

        private $destination;

        public function __construct(array $data = [])
        {
            parent::__construct($data);
        }

        protected function generateOutput(): void
        {
            if(empty($this->paramsInput['avatar']))
            {
                $this->uploadAvatar();

                if(!empty(User::getInstance()->getAvatar()))
                {
                    $this->deleteOldAvatar();
                }
            }

            $this->updateDB();
        }

        private function uploadAvatar(): void
        {
            $uploader = $this->getUploader();

            $uploader->createDestination();

            $files = $this->getFiles();

            $fileName = $uploader->upload($files['avatarImg']['tmp_name']);

            $this->paramsInput['avatar'] = $this->destination . $fileName;
        }

        private function getUploader(): WorkerFile
        {
            $this->destination = '/users/' . User::getInstance()->getId() . '/';
            return new WorkerFile($_SERVER['DOCUMENT_ROOT'] . $this->destination);
        }

        private function getFiles(): array
        {
            $files = new RequestFiles;
            return $files->get();
        }

        private function deleteOldAvatar(): void
        {
            $uploader = new WorkerFile($_SERVER['DOCUMENT_ROOT']);
            $uploader->delete(User::getInstance()->getAvatar());
        }

        private function updateDB(): void
        {
            User::getInstance()->updateProfile($this->paramsInput);
        }
    }