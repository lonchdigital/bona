// import $ from "jquery";

$(function() {
    'use strict';

    const $additionalProductSearchInput = $('#additional-product-search-input');
    const $additionalProductSearchResult = $('#additional-product-search-result');
    const $additionalProductTags = $('#art-additional-product-tags');


    const $additionalProductSearchInputField = $('#additional-product-search-input-field');
    $additionalProductSearchInputField.on('input', function() {
        runAjaxFilter();
    });

    // add tags
    $additionalProductSearchResult.on('click', '.doc-item', function(event) {
        var art_this = $(this);
        var currentId = art_this.data('id');
        var currentValues = $additionalProductSearchInput.val();
        var newValues = currentValues ? currentValues + ',' + currentId : currentId;

        // update values
        $additionalProductSearchInput.val(newValues);

        // add tag
        $additionalProductTags.append(`<button class="btn" data-id="${art_this.data('id')}">${art_this.text()}
            <?xml version="1.0" ?><!DOCTYPE svg  PUBLIC '-//W3C//DTD SVG 1.1//EN'  'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'><svg height="512px" id="Layer_1" style="enable-background:new 0 0 512 512;" version="1.1" viewBox="0 0 512 512" width="512px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M443.6,387.1L312.4,255.4l131.5-130c5.4-5.4,5.4-14.2,0-19.6l-37.4-37.6c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4  L256,197.8L124.9,68.3c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4L68,105.9c-5.4,5.4-5.4,14.2,0,19.6l131.5,130L68.4,387.1  c-2.6,2.6-4.1,6.1-4.1,9.8c0,3.7,1.4,7.2,4.1,9.8l37.4,37.6c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1L256,313.1l130.7,131.1  c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1l37.4-37.6c2.6-2.6,4.1-6.1,4.1-9.8C447.7,393.2,446.2,389.7,443.6,387.1z"/></svg>
        </button>`);
        art_this.remove();
    });

    // remove tags
    $additionalProductTags.on('click', '.btn', function(event) {
        var art_this = $(this);
        var currentValues = $additionalProductSearchInput.val();

        var valuesArray = currentValues.split(",").map(function(item) {
            return parseInt(item, 10);
        });

        var indexToRemove = valuesArray.indexOf(art_this.data('id'));
        if (indexToRemove !== -1) {
            valuesArray.splice(indexToRemove, 1);
        }

        // update values
        var newValues = valuesArray.join(",");
        $additionalProductSearchInput.val(newValues);

        art_this.remove();
    });



    function runAjaxFilter(pageNumber = null) {

        ajaxThematicFilter(
            function (data) {
                if( data['documents'].length > 0 ) {
                    handResult(data['documents']);
                    $additionalProductSearchResult.removeClass('d-none');
                } else {
                    $additionalProductSearchResult.html(getNothingFoundHTML());
                    $additionalProductSearchResult.removeClass('d-none');
                }
            },
            function () {
                console.error('init: error during Search.');
            },
            $additionalProductSearchInputField.val(),
            $additionalProductSearchInput.val()
        );
    }

    function ajaxThematicFilter(success, fail, searchValue, excludeAdditionalProductIds)
    {

        $.ajax({
            url: additional_product_search_route,
            type: 'post',
            data: {
                _token: csrf,
                search: searchValue,
                excludePostIds: excludeAdditionalProductIds
            },
            dataType: 'json'
        }).done(function(data) {
            success(data);
        }).fail(function () {
            fail();
        });
    }


    function handResult(data)
    {
        console.log(data);

        let documentsToAppend = '';
        if(data.length > 0) {
            data.forEach(function (document, index) {
                documentsToAppend += getDocumentHTML(document);
            });
        } else {
            for (let key in data) {
                documentsToAppend += getDocumentHTML(data[key]);
            }
        }

        $additionalProductSearchResult.html(documentsToAppend);
    }

    function getDocumentHTML(document)
    {
        return `<div class="doc-item" data-id="${document['id']}">${document['name'][locale]}</div>`;
    }
    function getNothingFoundHTML()
    {
        return `<div class="doc-item-nothing">Нічого не знайдено</div>`;
    }


    // Hide and Show Search result
    const $artAdditionalProductFields = $('.art-additional-product-fields');
    $(document).on('click', function(event) {
        if (!$artAdditionalProductFields.is(event.target) && $artAdditionalProductFields.has(event.target).length === 0) {
            $additionalProductSearchResult.addClass('d-none');
        }
    });
    $artAdditionalProductFields.on('click', function(event) {
        event.stopPropagation();
    });

});
