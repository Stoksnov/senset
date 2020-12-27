import { App } from './init';

var app = new App();

app.init();

function modals() {
    $('body').on('click', '.header__log-in', function () {
        openModal('.modal-log-in');
    });
    $('body').on('click', '.remind-password', function () {
        // stepRecovery = 1;
        $('.modal-password-recovery .form-group').hide();
        $('.password-recovery_step1').show();

        openModal('.modal-password-recovery');
    });
};

function openModal(modalElem, headText, bodyText) {
    closeModal();
    $(modalElem).fadeIn();
    $('.modal-background').fadeIn();
    $('html').addClass('scroll-hidden');

    $(modalElem).find('.modal__head-text').html(headText);
    $(modalElem).find('.modal__body').html(bodyText);
}

$('body').on('click', '[data-close="close"]', closeModal);

function closeModal() {
    $('.modal-background, .modal').fadeOut();
    $('html').removeClass('scroll-hidden');
}
function tabToggle() {
    $('.tab-panel_title').on('click', function () {
        let tab = $(this).data('tab-panel');
        let tabContent = $('.tab-content--' + tab);

        adjustTabPanel($(this));
        adjustTabContent();

        function adjustTabPanel(elem) {
            elem.parent().find('.tab-panel_title').removeClass('active');
            elem.addClass('active');
        }

        function adjustTabContent() {
            tabContent.parent().find('.tab-content').removeClass('active');
            tabContent.parent().find('.tab-panel_title').removeClass('active');
            tabContent.addClass('active');

            if (tabContent.has('.tab-content')) {
                $('.tab-content--' + tab + ' .tab-content:eq(0)').addClass('active');
                $('.tab-content--' + tab + ' .tab-panel_title:not(.select__item):eq(0)').addClass('active');
            }
        }
    });
};
$(document).ready(function () {
    tabToggle();
    modals();
});



// settings 
$('.edit-field').click(function () {
    $(this).prev().removeClass('not-active');
    if ($(this).prev().attr('readonly') == null) {
        $(this).prev().attr('readonly', '');
    } else {
        $(this).prev().removeAttr('readonly');
    }
});
$('.settings-form .btnCancel, .subscriptions-form .btnCancel').click(function () {
    $(this).closest('form').find('input').val('');
    $(this).closest('form').find('[type="checkbox"]').prop('checked', false);
});
$('.profile-form .btnCancel').click(function () {
    let input = $('.profile-form input');

    input.addClass('not-active');
    input.attr('readonly', 'readonly');

    for (let i = 2; i < input.length; i++) {
        let currentInput = $(`.profile-form input:eq(${i})`);

        currentInput.val(currentInput.attr('value'));
    }
});

$(window).on('hashchange', function openTabAHash() {
    $('[data-tab-panel="' + location.hash.substr(1) + '"]').click();
});