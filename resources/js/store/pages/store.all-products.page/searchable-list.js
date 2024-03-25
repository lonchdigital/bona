import $ from "jquery";

export function init () {
    const searchableBlock = $('.filter-item--brands');
    searchableBlock.each(function () {
        const searchInput = $(this).find('.search-input');
        const letters = $(this).find('.option-letter');
        const values = $(this).find('.custom-control');

        handleSearchInput(searchInput, letters, values);
        searchInput.keyup(function () {
            handleSearchInput($(this), letters, values);
        });
    });
}

function handleSearchInput(input, letters, values)
{
    const inputText = input.val();
    let letterAreHidden = false;

    if (inputText) {
        if (!letterAreHidden) {
            letters.attr('style', 'display: none;');
            letterAreHidden = true;
        }

        values.each(function () {
            const textValue = $(this).find('.custom-control-label').text();
            if (textValue.toLowerCase().indexOf(inputText.toLowerCase()) === -1) {
                $(this).attr('style', 'display: none;');
            } else {
                if ($(this).attr('style')) {
                    $(this).removeAttr('style');
                }
            }
        });

    } else {
        letters.removeAttr('style');
        values.removeAttr('style');
        letterAreHidden = false;
    }
}
