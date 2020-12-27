var validator = {
    init: () => {
        function Validator(data) {
            this.data = data;
            this.activeParam = Object.keys(data)[0];
            this.isError = false;
            this.isNecessary = true;
            this.messages = [];

            this.empty = function (mixed_var) {
                return (mixed_var === "" || mixed_var === 0 || mixed_var === "0" || mixed_var === null || mixed_var === false || (Array.isArray(mixed_var) && mixed_var.length === 0));
            }

            this.emptyActiveParam = function () {
                if (!this.data.has(this.activeParam)) {
                    return true;
                }

                return this.empty(this.data.get(this.activeParam));
            }

            this.setParam = function (key) {
                this.isError = this.isNecessary = true;

                this.activeParam = key;

                if (this.data.has(key)) {
                    this.isError = false;
                }

                return this;
            }

            this.setNotNecessary = function () {
                this.isNecessary = false;

                return this;
            }

            this.isNotEmpty = function () {
                if (!this.isError && this.emptyActiveParam()) {
                    this.isError = true;
                }

                return this;
            }

            this.isInt = function () {
                if (!this.isError && (this.data.get(this.activeParam).replace(/\s/g, '').length === 0 || isNaN(this.data.get(this.activeParam)))) {
                    this.isError = true;
                }

                return this;
            }

            this.isMinInt = function (min) {
                if (!this.isError && this.data.get(this.activeParam) < min) {
                    this.isError = true;
                }

                return this;
            }

            this.isMaxInt = function (max) {
                if (!this.isError && this.data.get(this.activeParam) > max) {
                    this.isError = true;
                }

                return this;
            }

            this.isIntervalInt = function (min, max) {
                this.isMaxInt(max).isMinInt(min);

                return this;
            }

            this.isCheckBoxActive = function () {
                if (!this.isError && this.data.get(this.activeParam) !== 'on') {
                    this.isError = true;
                }

                return this;
            }

            this.isEmail = function () {
                let regex = /@/;

                if (!this.isError && !regex.test(this.data.get(this.activeParam))) {
                    this.isError = true;
                }

                return this;
            }

            this.isBirthday = function () {
                let regex = /^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[13-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/;

                if (!this.isError && !regex.test(this.data.get(this.activeParam))) {
                    this.isError = true;
                }

                return this;
            }

            this.isParams = function (params) {
                if (!this.isError && !params.includes(this.data.get(this.activeParam))) {
                    this.isError = true;
                }

                return this;
            }

            this.strlenMin = function (min) {
                if (!this.isError && this.data.get(this.activeParam).length < min) {
                    this.isError = true;
                }

                return this;
            }

            this.strlenMax = function (max) {
                if (!this.isError && this.data.get(this.activeParam).length > max) {
                    this.isError = true;
                }

                return this;
            }

            this.isCustomComparator = function (func) {
                if (!this.isError && !func(this.data.get(this.activeParam))) {
                    this.isError = true;
                }

                return this;
            }

            this.isErrors = function () {
                return Object.keys(this.messages).length !== 0;
            }

            this.getIsError = function () {
                return this.isError;
            }

            this.setMessage = function (message) {
                if (!this.isNecessary && this.emptyActiveParam()) {
                    return this;
                }

                if (this.isError && !this.messages.hasOwnProperty(this.activeParam)) {
                    this.messages[this.activeParam] = message;
                }

                return this;
            }

            this.getMessages = function () {
                return this.messages;
            }
        }
    },
};

export { validator };
