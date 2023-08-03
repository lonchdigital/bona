function formatStateCountry (opt)
{
    if (!opt.id) {
        return opt.text;
    }

    const optImage = $(opt.element).data('image');

    if (!optImage) {
        return opt.text;
    }

    return $('<span><img src="' + optImage + '" width="28px" /> ' + opt.text + '</span>');
}

function formatStateColor (opt)
{
    if (!opt.id) {
        return opt.text;
    }

    const optColor = $(opt.element).data('value');

    if (!optColor) {
        return opt.text;
    }

    return $('<span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><rect width="20" height="20" rx="3" fill="' + optColor + '"/></svg></span> ' + opt.text + '</span>');
}

function slugify(s) {
    return s.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, "") //remove diacritics
        .toLowerCase()
        .replace(/\s+/g, '-') //spaces to dashes
        .replace(/&/g, '-and-') //ampersand to and
        .replace(/[^\w\-]+/g, '') //remove non-words
        .replace(/\-\-+/g, '-') //collapse multiple dashes
        .replace(/^-+/, '') //trim starting dash
        .replace(/-+$/, ''); //trim ending dash
}
