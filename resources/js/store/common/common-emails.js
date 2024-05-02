import $ from "jquery";
import iconUrl from '$img/icon.svg';
import InputCounter from "./input-counter";


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



        // TODO:: remove as finish
        // User Call Measurer
        /*const $userCallMeasurerForm =  $('#user-call-measurer, #user-call-dialog-0, #user-call-dialog-1, #user-call-dialog-2');
        $userCallMeasurerForm.submit(function(event) {
            event.preventDefault();

            let formData = new FormData(this);
            let data = {};

            for (var pair of formData.entries()) {
                data[pair[0]] = pair[1];
            }

            userChooseDoors(
                data,
                function (data) {
                    var button = document.getElementById("user-choose-doors-success");
                    button.click();

                    $userCallMeasurerForm.find('.field-error').remove();
                },
                function (xhr) {
                    if (xhr.status === 422) {
                        userCallMeasurErrors(xhr.responseJSON.errors);
                    } else {
                        console.error('[Email]: init: error during sending the email.');
                    }
                },
                $userCallMeasurerForm
            );

        });

        function userCallMeasurErrors(errors)
        {
            $userCallMeasurerForm.find('.field-error').remove();

            for (let fieldName in errors) {
                $userCallMeasurerForm.find('.' + fieldName + '-field').after(`<p class="field-error ${fieldName}">${errors[fieldName]}</p>`);
            }
        }*/

        // I need call form separately
        $('form[id^="user-call-"]').submit(function(event) {
            event.preventDefault();

            let formTag = $(this);
            var formData = new FormData(this);
            formTag.find('.field-error').remove();

            console.log('event ' + formTag.find('input[name="event"]').val());

            var data = {};

            for (var pair of formData.entries()) {
                data[pair[0]] = pair[1];
            }
            if(formData.get('agree') !== null){
                data['agree'] = true;
            }

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
            name: data['name'],
            phone: data['phone'],
            agree: data['agree']
        },
        dataType: 'json'
    }).done(function(data) {
        success(data);
    }).fail(function (xhr) {
        fail(xhr);
    });
}
