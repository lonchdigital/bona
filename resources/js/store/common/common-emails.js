import $ from "jquery";
// import iconUrl from '$img/icon.svg';
// import InputCounter from "./input-counter";


export default {
    init: async function () {

        // get GTM
        window.dataLayer = window.dataLayer || [];

        // User Choose Doors
        const $userChooseDoorsForm =  $('#user-choose-doors');
        $userChooseDoorsForm.submit(function(event) {
            event.preventDefault();

            let formData = new FormData(this);
            $userChooseDoorsForm.find('.field-error').remove();
            let data = {};

            for (var pair of formData.entries()) {
                data[pair[0]] = pair[1];
            }
            if(formData.get('agree') !== null){
                data['agree'] = true;
            }

            data['title'] = $userChooseDoorsForm.find('.title.h2').text();

            userChooseDoors(
                data,
                function (data) {
                    var button = document.getElementById("user-choose-doors-success");
                    button.click();

                    $userChooseDoorsForm.find('input[name="name"]').val('');
                    $userChooseDoorsForm.find('input[name="phone"]').val('');
                    $userChooseDoorsForm.find('input[type="checkbox"]').prop('checked', false);

                    $userChooseDoorsForm.find('.field-error').remove();

                    window.dataLayer.push({
                        'event': $userChooseDoorsForm.find('input[name="event"]').val()
                    });
                },
                function (xhr) {
                    if (xhr.status === 422) {
                        userChooseDoorsErrors(xhr.responseJSON.errors);
                    } else {
                        console.error('[Email]: init: error during sending the email.');
                    }
                },
                $userChooseDoorsForm
            );

        });

        function userChooseDoorsErrors(errors)
        {
            for (let fieldName in errors) {
                $userChooseDoorsForm.find('input[name="'+ fieldName +'"]').val('');
                $userChooseDoorsForm.find('.' + fieldName + '-field').after(`<p class="field-error ${fieldName}">${errors[fieldName]}</p>`);
            }
        }





        // I need call form separately
        $('form[id^="user-call-"]').submit(function(event) {
            event.preventDefault();

            let formTag = $(this);
            var formData = new FormData(this);
            formTag.find('.field-error').remove();

            var data = {};

            for (var pair of formData.entries()) {
                data[pair[0]] = pair[1];
            }
            if(formData.get('agree') !== null){
                data['agree'] = true;
            }

            data['title'] = formTag.find('.title.h2').text();

            userChooseDoors(
                data,
                function(data) {
                    formTag.find('.field-error').remove(); // Remove current Form errors
                    var button = document.getElementById("user-choose-doors-success");
                    button.click();

                    formTag.find('input[name="name"]').val('');
                    formTag.find('input[name="phone"]').val('');
                    formTag.find('input[type="checkbox"]').prop('checked', false);

                    $('button.is-close-btn').click();

                    window.dataLayer.push({
                        'event': formTag.find('input[name="event"]').val()
                    });
                },
                function(xhr) {
                    if (xhr.status === 422) {
                        userCallMeasurErrors(xhr.responseJSON.errors, formData, formTag); // Передаем текущую форму в функцию обработки ошибок
                    } else {
                        console.error('[Email]: init: error during sending the email.');
                    }
                },
                formData
            );
        });

        /*
         * Buy in one click books an order for the product whose page it sits
         * on, so it posts to that product's own route rather than the shared
         * call-back endpoint the forms above use. Everything the shop needs to
         * settle — address, delivery, payment — is agreed on the phone.
         */
        $(document).on('submit', '#one-click-order-form', function (event) {
            event.preventDefault();

            const $form = $(this);
            const $submit = $form.find('[type="submit"]');

            if ($submit.prop('disabled')) {
                return;
            }

            $form.find('.field-error').remove();
            $submit.prop('disabled', true).attr('aria-busy', 'true');

            $.ajax({
                url: $form.attr('action'),
                type: 'post',
                data: $form.serialize(),
                dataType: 'json',
            }).done(function () {
                $form.find('input[name="name"]').val('');
                $form.find('input[name="phone"]').val('');

                $('button.is-close-btn').click();
                document.getElementById('user-choose-doors-success').click();

                window.dataLayer.push({
                    'event': $form.find('input[name="event"]').val()
                });
            }).fail(function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;

                    for (let fieldName in errors) {
                        $form.find('.' + fieldName + '-field')
                            .after(`<p class="field-error ${fieldName}">${errors[fieldName][0]}</p>`);
                    }
                } else {
                    console.error('[OneClick]: could not place the order.');
                }
            }).always(function () {
                $submit.prop('disabled', false).removeAttr('aria-busy');
            });
        });

        function userCallMeasurErrors(errors, formData, formTag) {
            for (let fieldName in errors) {
                formTag.find('input[name="'+ fieldName +'"]').val('');
                formTag.find('.' + fieldName + '-field').after(`<p class="field-error ${fieldName}">${errors[fieldName]}</p>`);
            }
        }



        // FAQs accordion
        // ----------------------------------------------------------------
        var acc = document.getElementsByClassName("accordion");
        var i;

        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {

                if( !$(this).hasClass('active') ) {
                    $('.accordion').removeClass('active');
                    $('.art-panel').removeAttr("style");
                }

                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.maxHeight) {
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = (panel.scrollHeight + 100) + "px";
                }
            });
        }

        // Strecher accordion
        // ----------------------------------------------------------------
        var $strecherItem = $('.stretcher-item');
        $strecherItem.bind({
            mouseenter: function (e) {
                $(this).addClass('active');
                $(this).siblings().addClass('inactive');
            },
            mouseleave: function (e) {
                $(this).removeClass('active');
                $(this).siblings().removeClass('inactive');
            }
        });


    }
};


function userChooseDoors(data, success, fail, form)
{
    const routeWithSlug = routes.email.user_choose_doors_route;

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
            title: data['title'],
            name: data['name'],
            phone: data['phone'],
            description: data['description'],
            agree: data['agree']
        },
        dataType: 'json'
    }).done(function(data) {
        success(data);
    }).fail(function (xhr) {
        fail(xhr);
    });
}
