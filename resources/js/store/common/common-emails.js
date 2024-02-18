import $ from "jquery";
import iconUrl from '$img/icon.svg';
import InputCounter from "./input-counter";


export default {
    init: async function () {

        // User Choose Doors
        const $userChooseDoorsForm =  $('#user-choose-doors');
        $userChooseDoorsForm.submit(function(event) {
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

                    $userChooseDoorsForm.find('.field-error').remove();
                },
                function (xhr) {
                    if (xhr.status === 422) {
                        userChooseDoorsErrors(xhr.responseJSON.errors);
                    } else {
                        console.error('[Email]: init: error during sending the email.');
                    }
                }
            );

        });

        function userChooseDoorsErrors(errors)
        {
            $userChooseDoorsForm.find('.field-error').remove();

            for (let fieldName in errors) {
                $userChooseDoorsForm.find('.' + fieldName + '-field').after(`<p class="field-error ${fieldName}">${errors[fieldName]}</p>`);
            }
        }



        // User Call Measurer
        const $userCallMeasurerForm =  $('#user-call-measurer');
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
                }
            );

        });

        function userCallMeasurErrors(errors)
        {
            $userCallMeasurerForm.find('.field-error').remove();

            for (let fieldName in errors) {
                $userCallMeasurerForm.find('.' + fieldName + '-field').after(`<p class="field-error ${fieldName}">${errors[fieldName]}</p>`);
            }
        }

    }
};


function userChooseDoors(data, success, fail)
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
