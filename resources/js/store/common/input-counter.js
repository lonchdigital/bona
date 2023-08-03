import $ from "jquery";

export default {
    init: async function () {
        const dateInput = $('.custom-control-number--date input');

        //counter for input
        if (dateInput.length) {
            dateInput.each(function () {
                if ($(this).val() === '') {
                    $(this).closest('.custom-control-number--date').find('.before').show();
                } else {
                    $(this).closest('.custom-control-number--date').find('.before').hide();
                }
            });

            dateInput.on('input', function () {
                if ($(this).val() === '') {
                    $(this).closest('.custom-control-number--date').find('.before').show();
                } else {
                    $(this).closest('.custom-control-number--date').find('.before').hide();
                }
            });
        }

        const counter = $('.custom-control-number .counter');
        initCountersHandler(counter);

    },
    addCounterHandler: function (elements) {
        initCountersHandler(elements);
    }
}

function initCountersHandler(elements)
{
    if (elements.length) {
        elements.click(function (e) {
            $(this).closest('.custom-control-number').find('.before').hide();
            let valueElement = $(this).closest('.custom-control-number').find('input');
            const minVal = valueElement.attr('min');

            if (!valueElement.val()) {
                valueElement.val(minVal);
            }

            else if ($(this).hasClass('plus')) {
                if (!valueElement.val()) {
                    valueElement.val(minVal)
                } else
                    valueElement.val(Math.max(parseInt(valueElement.val()) + 1)).trigger('change', ['plus']);
            }
            else if (valueElement.val() > minVal) // Stops the value going into negatives
            {
                valueElement.val(Math.max(parseInt(valueElement.val()) - 1)).trigger('change', ['minus']);
            }

            return false;
        });
    }
}
