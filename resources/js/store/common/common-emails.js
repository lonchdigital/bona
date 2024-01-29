import $ from "jquery";
import iconUrl from '$img/icon.svg';
import InputCounter from "./input-counter";


export default {
    init: async function () {

        $('#user-choose-doors').submit(function(event) {
            event.preventDefault();

            let formData = new FormData(this);
            let data = {};

            for (var pair of formData.entries()) {
                data[pair[0]] = pair[1];
            }

            userChooseDoors(
                data,
                function (data) {
                    // $('.choose-doors-errors').html('<p style="color: green">Ваш запит успішно відправленно!</p>');

                    var button = document.getElementById("user-choose-doors-success");
                    button.click();
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
function userChooseDoorsErrors(errors)
{
    let errorsToAppend = '';

    for (let key in errors) {
        errorsToAppend += `<p>${errors[key]}</p>`;
    }

    $('.choose-doors-errors').html(errorsToAppend);
}
