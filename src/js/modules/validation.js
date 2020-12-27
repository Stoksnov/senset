var validation = {
    init: () => {

        $('.log-in-form').submit(function () {
            let formdata = new FormData($('.log-in-form')[0]);
            let validator = new Validator(formdata);

            $('.log-in-form .error-msg').remove();

            // validator.setParam('email')
            //     .isNotEmpty()
            //     .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
            //     .strlenMin(5)
            //     .strlenMax(128)
            //     .setMessage('<div class="error-msg">Неверный формат E-mail</div>');

            // validator.setParam('password')
            //     .isNotEmpty()
            //     .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
            //     .strlenMin(6)
            //     .strlenMax(128)
            //     .setMessage('<div class="error-msg">Неверный формат пароля</div>');

            validator.setParam('phone')
                .isNotEmpty()
                .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                .strlenMin(2)
                .strlenMax(18)
                .setMessage('<div class="error-msg">Введите существующий номер телефона</div>');

            let msgs = validator.getMessages();

            if (validator.isErrors()) {
                for (key in msgs) {
                    $('.log-in-form [name="' + key + '"]').after(msgs[key])
                }
            } else {
                ajaxAuthorization(formdata);
            }

            return false;
        });

        $('.register-form').submit(function () {
            let formdata = new FormData($('.register-form')[0]);
            let validator = new Validator(formdata);

            $('.register-form .error-msg').remove();

            validator.setParam('roleId')
                .isParams(['2', '1'])
                .setMessage('<div class="error-msg">Выберите один из вариантов</div>');

            // validator.setParam('name')
            //     .setNotNecessary()
            //     .strlenMin(2)
            //     .setMessage('<div class="error-msg">Имя должно содержать минимум 2 символа</div>')
            //     .strlenMax(64)
            //     .setMessage('<div class="error-msg">Имя должно содержать максимум 64 символа</div>');

            validator.setParam('phone')
                .isNotEmpty()
                .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                .strlenMin(2)
                .strlenMax(18)
                .setMessage('<div class="error-msg">Введите существующий номер телефона</div>');

            // validator.setParam('email')
            //     .isNotEmpty()
            //     .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
            //     .strlenMin(5)
            //     .setMessage('<div class="error-msg">Введите существующий e-mail</div>')
            //     .strlenMax(128)
            //     .setMessage('<div class="error-msg">Поле должно содержать максимум 128 символов</div>');

            // validator.setParam('password')
            //     .isNotEmpty()
            //     .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
            //     .strlenMin(6)
            //     .setMessage('<div class="error-msg">Поле должно содержать минимум 6 символов</div>')
            //     .strlenMax(128)
            //     .setMessage('<div class="error-msg">Поле должно содержать максимум 128 символов</div>');

            // validator.setParam('password2')
            //     .isNotEmpty()
            //     .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
            //     .isParams([$('.register-form [name="password"]').val()])
            //     .setMessage('<div class="error-msg">Пароли не совпадают</div>')
            //     .strlenMin(6)
            //     .setMessage('<div class="error-msg">Поле должно содержать минимум 6 символов</div>')
            //     .strlenMax(128)
            //     .setMessage('<div class="error-msg">Поле должно содержать максимум 128 символов</div>');

            validator.setParam('agreement')
                .isCheckBoxActive()
                .setMessage('<div class="error-msg">Необходимо подтвердить согласие</div>');

            let msgs = validator.getMessages();

            if (validator.isErrors()) {
                for (key in msgs) {
                    if (key == 'agreement') {
                        $('.register-form .checkbox-group').after(msgs[key])
                    } else {
                        $('.register-form [name="' + key + '"]').after(msgs[key])
                    }
                }
            } else {
                ajaxRegistration(formdata);
            }

            return false;
        });

        $('.our-team-form').submit(function () {
            let formdata = new FormData($('.our-team-form')[0]);
            let validator = new Validator(formdata);

            $('.our-team-form .error-msg').remove();

            validator.setParam('name')
                .setNotNecessary()
                .strlenMin(2)
                .setMessage('<div class="error-msg">Имя должно содержать минимум 2 символа</div>')
                .strlenMax(64)
                .setMessage('<div class="error-msg">Имя должно быть короче 64 символов</div>');

            validator.setParam('email')
                .isNotEmpty()
                .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                .strlenMin(5)
                .setMessage('<div class="error-msg">Введите существующий e-mail</div>')
                .strlenMax(128)
                .setMessage('<div class="error-msg">Поле должно содержать максимум 128 символов</div>');

            validator.setParam('message')
                .isNotEmpty()
                .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                .strlenMax(2000)
                .setMessage('<div class="error-msg">Поле может содержать максимум 2000 символов</div>');

            validator.setParam('confirm')
                .isNotEmpty()
                .isCheckBoxActive()
                .setMessage('<div class="error-msg">Необходимо подтвердить согласие</div>');

            let msgs = validator.getMessages();

            if (validator.isErrors()) {
                for (key in msgs) {
                    $('.our-team-form [name="' + key + '"]').after(msgs[key])
                }
            } else {
                ajaxContacts(formdata);
            }

            return false;
        });

        var stepRecovery = 1;

        $('.password-recovery-form').submit(function () {
            let formdata = new FormData($('.password-recovery-form')[0]);
            let validator = new Validator(formdata);

            $('.password-recovery-form .error-msg').remove();

            if ($('.password-recovery-form [name="step"]').val() == 1) {
                validator.setParam('phone')
                    .isNotEmpty()
                    .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                    .strlenMin(2)
                    .strlenMax(18)
                    .setMessage('<div class="error-msg">Введите существующий номер телефона</div>');

            } else if ($('.password-recovery-form [name="step"]').val() == 1) {
                validator.setParam('code')
                    .isNotEmpty()
                    .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                    .isInt()
                    .strlenMin(6)
                    .strlenMax(6)
                    .setMessage('Неверный формат кода');
            } else if ($('.password-recovery-form [name="step"]').val() == 1) {
                validator.setParam('password')
                    .isNotEmpty()
                    .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                    .strlenMin(6)
                    .setMessage('<div class="error-msg">Поле должно содержать минимум 6 символов</div>')
                    .strlenMax(128)
                    .setMessage('<div class="error-msg">Поле должно содержать максимум 128 символов</div>');

                validator.setParam('password2')
                    .isNotEmpty()
                    .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                    .isParams([$('.password-recovery-form [name="password"]').val()])
                    .setMessage('<div class="error-msg">Пароли не совпадают</div>')
                    .strlenMin(6)
                    .setMessage('<div class="error-msg">Поле должно содержать минимум 6 символов</div>')
                    .strlenMax(128)
                    .setMessage('<div class="error-msg">Поле должно содержать максимум 128 символов</div>');
            }

            let msgs = validator.getMessages();

            // for (let key of formdata.entries()) {
            //     console.log(key[0] + ', ' + key[1]);
            // }

            if (validator.isErrors()) {
                for (key in msgs) {
                    $('.password-recovery-form [name="' + key + '"]').after(msgs[key])
                }
            } else {
                ajaxRecovery(formdata);
            }

            return false;
        });


        $('.settings-form').submit(function () {
            let form = $(this);
            let formdata = new FormData($(form)[0]);
            let validator = new Validator(formdata);

            form.find('.error-msg').remove();

            validator.setParam('passwordOld')
                .isNotEmpty()
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Поле обязательно для заполнения</div>')
                .strlenMin(6)
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Поле должно содержать минимум 6 символов</div>')
                .strlenMax(128)
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Поле должно содержать максимум 128 символов</div>');

            validator.setParam('password')
                .isNotEmpty()
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Поле обязательно для заполнения</div>')
                .strlenMin(6)
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Поле должно содержать минимум 6 символов</div>')
                .strlenMax(128)
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Поле должно содержать максимум 128 символов</div>');

            validator.setParam('password2')
                .isNotEmpty()
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Поле обязательно для заполнения</div>')
                .isParams([form.find('[name="password"]').val()])
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Пароли не совпадают</div>')
                .strlenMin(6)
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Поле должно содержать минимум 6 символов</div>')
                .strlenMax(128)
                .setMessage('<div class="error-msg" style="margin-top: 15px;">Поле должно содержать максимум 128 символов</div>');

            let msgs = validator.getMessages();

            if (validator.isErrors()) {
                for (key in msgs) {
                    form.find('[name="' + key + '"]').parent().after(msgs[key])
                }
            } else {
                ajaxProfilePassword(formdata);
            }

            return false;
        });

        $('.profile-form').submit(function () {
            let form = $('.profile-form');
            // let form = $(this);
            let formdata = new FormData($(form)[0]);
            let validator = new Validator(formdata);

            for (let i = 0; i < form.find('[name]').length; i++) {
                let attrName = form.find('[name]:eq(' + i + ')').attr('name');

                if (!formdata.has(attrName)) {
                    formdata.append(attrName, '');
                }
            }

            form.find('.error-msg').remove();

            validator.setParam('birthday')
                .setNotNecessary()
                .setMessage('<div class="error-msg>Указан неверный формат</div>');

            validator.setParam('name')
                .isNotEmpty()
                .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                .strlenMin(2)
                .setMessage('<div class="error-msg">Имя должно содержать минимум 2 символа</div>')
                .strlenMax(64)
                .setMessage('<div class="error-msg">Имя должно содержать максимум 64 символа</div>');

            validator.setParam('phone')
                .isNotEmpty()
                .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                .strlenMin(2)
                .strlenMax(18)
                .setMessage('<div class="error-msg">Введите существующий номер телефона</div>');

            validator.setParam('email')
                .isNotEmpty()
                .setMessage('<div class="error-msg">Поле обязательно для заполнения</div>')
                .strlenMin(5)
                .setMessage('<div class="error-msg">Введите существующий e-mail</div>')
                .strlenMax(128)
                .setMessage('<div class="error-msg">Поле должно содержать максимум 128 символов</div>');

            let msgs = validator.getMessages();

            if (validator.isErrors()) {
                for (key in msgs) {
                    form.find('[name="' + key + '"]').parent().after(msgs[key])
                }
            } else {
                ajaxProfileMain(formdata);
            }

            return false;
        });

    },
};

export { validation };
