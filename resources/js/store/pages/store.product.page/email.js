import $ from "jquery";


export function init () {

        // get GTM
        window.dataLayer = window.dataLayer || [];

        // User Choose Doors
        const $orderCountForm =  $('#order-count-form');
        $orderCountForm.submit(function(event) {
            event.preventDefault();

            let formData = new FormData(this);
            $orderCountForm.find('.field-error').remove();
            let data = {};

            for (var pair of formData.entries()) {
                data[pair[0]] = pair[1];
            }
            if(formData.get('agree') !== null){
                data['agree'] = true;
            }

            data['title'] = $orderCountForm.find('.title.h2').text();

            let currentProductLink = $orderCountForm.find('.art-current-product-link');
            data['current_product_title'] = currentProductLink.text();
            data['current_product_url'] = currentProductLink.attr('href');


            userChooseDoors(
                data,
                function (data) {
                    var button = document.getElementById("user-choose-doors-success");
                    button.click();

                    $orderCountForm.find('input[name="name"]').val('');
                    $orderCountForm.find('input[name="phone"]').val('');
                    $orderCountForm.find('input[type="checkbox"]').prop('checked', false);

                    $orderCountForm.find('.field-error').remove();

                    window.dataLayer.push({
                        'event': $orderCountForm.find('input[name="event"]').val()
                    });
                },
                function (xhr) {
                    if (xhr.status === 422) {
                        userChooseDoorsErrors(xhr.responseJSON.errors);
                    } else {
                        console.error('[Email]: init: error during sending the email.');
                    }
                },
                $orderCountForm
            );

        });

        function userChooseDoorsErrors(errors)
        {
            for (let fieldName in errors) {
                $orderCountForm.find('input[name="'+ fieldName +'"]').val('');
                $orderCountForm.find('.' + fieldName + '-field').after(`<p class="field-error ${fieldName}">${errors[fieldName]}</p>`);
            }
        }



};


function userChooseDoors(data, success, fail, form)
{
    const routeWithSlug = routes.email.order_count_doors_route;

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
            title: data['title'],
            name: data['name'],
            phone: data['phone'],
            agree: data['agree'],
            current_product_title: data['current_product_title'],
            current_product_url: data['current_product_url']
        },
        dataType: 'json'
    }).done(function(data) {
        success(data);
    }).fail(function (xhr) {
        fail(xhr);
    });
}
