import $ from "jquery";

export default {
    init: async function() {
        const form = $('.discount-subscription-form');
        const submitButton = form.find('.submit-button');
        const emailInput = form.find('input[name="email"]');
        const errorText = form.find('.error-text');
        const emailSubscriptionSentSuccess = $('.email-subscription-sent-success');

        submitButton.click(function (event) {
            event.preventDefault();

            errorText.text('');

            $.ajax({
                url: routes.emailSubscription.email_subscription_subscribe,
                type: 'post',
                data: {
                    _token: csrf,
                    email: emailInput.val(),
                },
                dataType: 'json',
            }).done(function(data) {
                form.addClass('d-none');
                emailSubscriptionSentSuccess.removeClass('d-none');
            }).fail(function (data) {
                if(data.hasOwnProperty('responseJSON') && data.responseJSON.hasOwnProperty('message')) {
                    errorText.text(data.responseJSON.message);
                } else {
                    errorText.text(translations.action_unexpected_error);
                }
            });
        });
    }
}
