<?php

    namespace Essences\Validation;

    use Exception\ExceptionValidation;

    final class ValidationContacts extends AbstractValidation
    {

        protected function validation(): void
        {
            $this->validator
                ->setParam('email')
                ->isNotEmpty()
                ->isEmail()
                ->strlenMin(5)
                ->strlenMax(128)
                ->setMessage('Неверный формат E-mail');

            $this->validator
                ->setParam('name')
                ->isNotEmpty()
                ->strlenMin(2)
                ->strlenMax(128)
                ->setMessage('Неверный формат имени');

            $k = 2000;

            $this->validator
                ->setParam('message')
                ->isNotEmpty()
                ->strlenMax($k)
                ->setMessage("Текст должен быть не более {$k} символов");

        //6Ldk46gZAAAAAEpZQETxRtbv_-bJLh_o6E8t0o5H публичный ключ

            /*$this->validator
                ->setParam('g-recaptcha-response')
                ->isCustomComparator(function ($response){
                    $requestData = [
                        'secret' => '6Ldk46gZAAAAAO7rs3BR2PUnqtzRIi8OsEatweAB',
                        'response' => $response,
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    ];

                    $requestDataPrepared = http_build_query($requestData);

                    $curl = curl_init();

                    curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $requestDataPrepared);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                    $result = curl_exec($curl);
                    $error = curl_error($curl);

                    curl_close($curl);

                    if($error)
                    {
                        return false;
                    }

                    $result = json_decode($result, true);

                    if(!$result['success'])
                    {
                        return false;
                    }

                    return true;
                })
            ->setMessage('Капча не пройдена');*/

            $this->validator
                ->setParam('confirm')
                ->isNotEmpty()
                ->isCheckBoxActive()
                ->setMessage('Необходимо согласиться с условиями');
        }
    }