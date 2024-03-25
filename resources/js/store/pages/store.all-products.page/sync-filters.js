import $ from "jquery";

export function init () {
    $('.sync-input').change(function (event, isSync) {

        if (isSync) {
            return;
        }

        const type = $(this).attr('type');

        const reversedId = $(this).attr('id').indexOf('full') !== -1 ? $(this).attr('id').replace('full', 'main') : $(this).attr('id').replace('main', 'full');

        if (type === 'checkbox') {
            $('label[for="' + reversedId + '"]').trigger('click', [true]);
        }
        else if (type === 'number' || type === 'text') {
            $('#' + reversedId).val($(this).val()).trigger('change', [true])
        }
    });
}
