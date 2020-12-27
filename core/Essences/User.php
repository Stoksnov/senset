<?php

    namespace Essences;

    use Exception\ExceptionAuthorization;
    use Exception\ExceptionUpdateDB;
    use Exception\ExceptionValidation;
    use Request\RequestCookie;

    final class User
    {
        // private $birthday;
        private $auth;
        private $id;
        private $name;
        private $surname;
        private $patronymic;
        private $email;
        private $phone;
        private $password;
        private $token;
        private $personal_account;
        private $type_device;
        private $number_device;
        private $address;

        private static $instance = null;

        private function __construct()
        {
            $this->auth = false;

            session_start();

            if(!session_id())
            {
                $this->auth = true;
            }
            elseif(!empty($_COOKIE['USER']))
            {
                $cookieId = \R::getCell('SELECT id FROM users WHERE token = ?', [$_COOKIE['USER']]);

                if($cookieId != 0)
                {
                    $_SESSION['logged_user'] = $cookieId;
                    $this->auth = true;
                }
            }

            if($this->auth)
            {
                $userDB = \R::findOne('users', 'id = ?', [$_SESSION['logged_user']]);

                if($userDB)
                {
                    // $this->birthday = $userDB->birthday;
                    $this->$id = $userDB->$id;
                    $this->$name = $userDB->$name;
                    $this->$surname = $userDB->$surname;
                    $this->$patronymic = $userDB->$patronymic;
                    $this->$email = $userDB->$email;
                    $this->$phone = $userDB->$phone;
                    $this->$password = $userDB->$password;
                    $this->$token = $userDB->$token;
                    $this->$personal_account = $userDB->$personal_account;
                    $this->$type_device = $userDB->$type_device;
                    $this->$number_device = $userDB->$number_device;
                    $this->$address = $userDB->$address;
                }
                else
                {
                    $this->logout();
                }
            }
        }

        public static function getInstance(): self
        {
            if(self::$instance === null)
            {
                self::$instance = new self;
            }

            return self::$instance;
        }

        // public function getBirthday(): string
        // {
        //     return $this->birthday;
        // }


        public function getId(): int
        {
            return $this->id;
        }

        public function getAuth(): bool
        {
            return $this->auth;
        }

        public function getName(): ?string
        {
            return $this->name;
        }

        public function getSurname(): ?string
        {
            return $this->surname;
        }

        public function getPatronymic(): ?string
        {
            return $this->patronymic;
        }

        public function getEmail(): ?string
        {
            return $this->email;
        }

        public function getPhone(): string
        {
            return $this->phone;
        }

        public function logout(): void
        {
            session_destroy();
            $this->auth = false;

            if(!empty($_COOKIE['USER']))
            {
                unset($_COOKIE['USER']);
                setcookie('USER', null, -1, '/');
            }
        }

        private static function entrance(int $id): void
        {
            $userDB = \R::load('users', $id);

            $userDB->id = $id;

            $_SESSION['logged_user'] = $id;

            $token = password_hash($id . $userDB->password . ($id + 1), PASSWORD_DEFAULT);

            $userDB->token = $token;

            $userId = \R::store($userDB);

            if($userId == 0)
            {
                throw new ExceptionUpdateDB;
            }

            $cookie = new RequestCookie;
            $cookie->set('USER', $token);
        }

        public static function login(string $phone, string $password): void
        {
            $userDB = \R::getRow('SELECT * FROM users WHERE phone = ?', [$phone]);

            if(empty($userDB))
            {
                $error = json_encode(['phone' => 'Пользователя с таким телефоном не существует'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }

            if(!password_verify($password, $userDB['password']))
            {
                $error = json_encode(['password' => 'Неверный пароль'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }

            self::entrance($userDB['id']);
        }

        public static function updatePassword(string $phone, string $password): void
        {
            $userDB = \R::getRow('SELECT * FROM users WHERE phone = ?', [$phone]);

            if(empty($userDB))
            {
                $error = json_encode(['phone' => 'Пользователя с таким телефоном не существует'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }

            if(password_verify($password, $userDB['password']))
            {
                $error = json_encode(['password' => 'Пароль совпадает со старым'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }

            $userDB['password'] = password_hash($password, PASSWORD_DEFAULT);

            $id = \R::store(\R::convertToBean('users', $userDB));

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        public function updatePasswordSession(string $password, string $passwordOld): void
        {
            $userDB = \R::getRow('SELECT * FROM users WHERE email = ?', [$this->email]);

            if(!password_verify($passwordOld, $userDB['password']))
            {
                $error = json_encode(['passwordOld' => 'Неверно введен старый пароль'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }

            if(password_verify($password, $userDB['password']))
            {
                $error = json_encode(['password' => 'Пароль совпадает со старым'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }

            $userDB['password'] = password_hash($password, PASSWORD_DEFAULT);

            $id = \R::store(\R::convertToBean('users', $userDB));

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        public static function signup(array $form): void
        {
            self::relevantUser($form['phone']);

            $userDB = \R::xdispense('users');

            $userDB->phone = $form['phone'];
            $userDB->password = password_hash($form['password'], PASSWORD_DEFAULT);
            $userDB->token = '';

            $id = \R::store($userDB);

            if($id == 0)
            {
                throw new ExceptionUpdateDB();
            }

            self::entrance($id);
        }

        private static function relevantUser(string $phone): void
        {
            if(\R::count('users', 'phone = ?', [$phone]) > 0)
            {
                $error = json_encode(['phone' => 'Пользователь с таким телефоном уже существует'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }
        }

        private function relevantUserSession(string $phone, string $email): void
        {
            if(\R::count('users', 'phone = ? AND id != ?', [$phone, $this->id]) > 0)
            {
                $error = json_encode(['phone' => 'Пользователь с таким телефоном уже существует'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }

            if(\R::count('users', 'email = ? AND id != ?', [$email, $this->id]) > 0)
            {
                $error = json_encode(['email' => 'Пользователь с таким Email уже существует'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }
        }

        public function updateProfile(array $form): void
        {
            $this->relevantUserSession($form['phone'], $form['email']);

            // $this->birthday = $form['birthday'];
            $this->$name = $form['name'];
            $this->$surname = $form['surname'];
            $this->$patronymic = $form['patronymic'];
            $this->$email = $form['email'];
            $this->$phone = $form['phone'];
            $this->$personal_account = $form['personal_account'];
            $this->$type_device = $form['type_device'];
            $this->$number_device = $form['number_device'];
            $this->$address = $form['address'];

            $this->updateDB();
        }

        private function updateDB(): void
        {
            $userDB = \R::load('users', $this->id);

            if(empty($userDB))
            {
                throw new ExceptionValidation("Неверный параметр id {$this->id}");
            }

            $userDB->$id = $this->$id;
            $userDB->$name = $this->$name;
            $userDB->$surname = $this->$surname;
            $userDB->$patronymic = $this->$patronymic;
            $userDB->$email = $this->$email;
            $userDB->$phone = $this->$phone;
            $userDB->$password = $this->$password;
            $userDB->$token = $this->$token;
            $userDB->$personal_account = $this->$personal_account;
            $userDB->$type_device = $this->$type_device;
            $userDB->$number_device = $this->$number_device;
            $userDB->$address = $this->$address;
                    
            $id = \R::store($userDB);

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        public function checkAuthorization(): void
        {
            if(!$this->auth)
            {
                throw new ExceptionAuthorization;
            }
        }

        public function checkNotAuthorization(): void
        {
            if($this->auth)
            {
                throw new ExceptionAuthorization;
            }
        }

        private function __clone()
        {

        }
    }