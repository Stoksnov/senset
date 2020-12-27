<?php

    namespace Essences;

    use Exception\ExceptionAuthorization;
    use Exception\ExceptionUpdateDB;
    use Exception\ExceptionValidation;
    use Request\RequestCookie;

    final class User
    {
        private $auth;
        private $id;
        private $roleId;
        private $role;
        private $birthday;
        private $name;
        private $email;
        private $tel;
        private $password;
        private $token;
        private $subscribes;
        private $avatar;
        private static $instance = null;

        private function __construct()
        {
            $this->auth = false;
            $this->subscribes = null;

            session_start();

            if(!session_id())
            {
                $this->auth = true;
            }
            elseif(!empty($_COOKIE['USER']))
            {
                $cookieId = \R::getCell('SELECT ID FROM zaselite_users WHERE token = ?', [$_COOKIE['USER']]);

                if($cookieId != 0)
                {
                    $_SESSION['logged_user'] = $cookieId;
                    $this->auth = true;
                }
            }

            if($this->auth)
            {
                $userDB = \R::findOne('zaselite_users', 'ID = ?', [$_SESSION['logged_user']]);

                if($userDB)
                {
                    $this->id = $userDB->ID;
                    $this->roleId = $userDB->roleId;
                    $this->role = \R::getRow('SELECT * FROM zaselite_roles WHERE ID = ?', [$this->roleId]);
                    $this->birthday = $userDB->birthday;
                    $this->name = $userDB->name;
                    $this->email = $userDB->email;
                    $this->tel = $userDB->tel;
                    $this->password = $userDB->password;
                    $this->token = $userDB->token;
                    $this->subscribes = Subscribe::getInstanceActive($this->id);
                    $this->avatar = $userDB->avatar;
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

        public function getSubscribes(): ?array
        {
            return $this->subscribes;
        }

        public function getAvatar(): ?string
        {
            return $this->avatar;
        }

        public function getStoriesPay(): array
        {
            return Subscribe::getInstanceStories($this->id);
        }

        public function getBirthday(): string
        {
            return $this->birthday;
        }

        public function getRoleId(): string
        {
            return $this->roleId;
        }

        public function getRole(): array
        {
            return $this->role;
        }

        public function getId(): int
        {
            return $this->id;
        }

        public function getAuth(): bool
        {
            return $this->auth;
        }

        public function getFullName(): ?string
        {
            return $this->name;
        }

        public function getFirstName(): string
        {
            return explode(' ', $this->name)[0];
        }

        public function getEmail(): ?string
        {
            return $this->email;
        }

        public function getPhone(): string
        {
            return $this->tel;
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
            $userDB = \R::load('zaselite_users', $id);

            $userDB->ID = $id;

            $_SESSION['logged_user'] = $id;

            $token = password_hash($userDB->role . $id . $userDB->password . ($id + 1), PASSWORD_DEFAULT);

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
            $userDB = \R::getRow('SELECT * FROM zaselite_users WHERE tel = ?', [$phone]);

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

            self::entrance($userDB['ID']);
        }

        public static function updatePassword(string $phone, string $password): void
        {
            $userDB = \R::getRow('SELECT * FROM zaselite_users WHERE phone = ?', [$phone]);

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

            $id = \R::store(\R::convertToBean('zaselite_users', $userDB));

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        public function updatePasswordSession(string $password, string $passwordOld): void
        {
            $userDB = \R::getRow('SELECT * FROM zaselite_users WHERE email = ?', [$this->email]);

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

            $id = \R::store(\R::convertToBean('zaselite_users', $userDB));

            if($id == 0)
            {
                throw new ExceptionUpdateDB;
            }
        }

        public static function signup(array $form): void
        {
            self::relevantUser($form['phone']);

            $userDB = \R::xdispense('zaselite_users');

            $userDB->roleId = $form['roleId'];
            $userDB->tel = $form['phone'];
            $userDB->password = password_hash($form['password'], PASSWORD_DEFAULT);
            $userDB->token = '';

            $id = \R::store($userDB);

            if($id == 0)
            {
                throw new ExceptionUpdateDB();
            }

            self::entrance($id);
        }

        private static function relevantUser(string $tel): void
        {
            if(\R::count('zaselite_users', 'tel = ?', [$tel]) > 0)
            {
                $error = json_encode(['phone' => 'Пользователь с таким телефоном уже существует'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }
        }

        private function relevantUserSession(string $tel, string $email): void
        {
            if(\R::count('zaselite_users', 'tel = ? AND ID != ?', [$tel, $this->id]) > 0)
            {
                $error = json_encode(['phone' => 'Пользователь с таким телефоном уже существует'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }

            if(\R::count('zaselite_users', 'email = ? AND ID != ?', [$email, $this->id]) > 0)
            {
                $error = json_encode(['email' => 'Пользователь с таким Email уже существует'],
                    JSON_UNESCAPED_UNICODE);

                throw new ExceptionValidation($error);
            }
        }

        public function updateProfile(array $form): void
        {
            $this->relevantUserSession($form['phone'], $form['email']);

            $this->name = $form['name'];
            $this->email = $form['email'];
            $this->tel = $form['phone'];
            $this->birthday = $form['birthday'];
            $this->avatar = $form['avatar'];

            $this->updateDB();
        }

        private function updateDB(): void
        {
            $userDB = \R::load('zaselite_users', $this->id);

            if(empty($userDB))
            {
                throw new ExceptionValidation("Неверный параметр ID {$this->id}");
            }

            $userDB->ID = $this->id;
            $userDB->name = $this->name;
            $userDB->email = $this->email;
            $userDB->tel = $this->tel;
            $userDB->password = $this->password;
            $userDB->token = $this->token;
            $userDB->roleId = $this->roleId;
            $userDB->birthday = $this->birthday;
            $userDB->avatar = $this->avatar;

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

        public function checkPay(Tariff $tariff): bool
        {
            if(!$this->auth)
            {
                return false;
            }

            foreach($this->subscribes as $subscribe)
            {
                $tariffUser = $subscribe->getTariff();

                if($tariff->getRoleId() == $tariffUser->getRoleId() && $tariff->getType() <= $tariffUser->getType())
                {
                    return true;
                }
            }

            return false;
        }

        private function __clone()
        {

        }
    }