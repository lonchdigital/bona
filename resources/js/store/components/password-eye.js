import iconUrl from '$img/icon.svg';
export function handlePasswordEye(block)
{
    const input = block.find(':input');
    const eye = block.find('.password-eye-icon-use');
    const eyeToggle = block.find('.password-eye-toggle');

    const eyeOnSVG = iconUrl + '#i-eye';
    const eyeOffSVG = iconUrl + '#i-eye-off';
    const passwordShowClassName = 'password-show';

    eyeToggle.click(function (event) {
        event.preventDefault();
        if(!input.hasClass(passwordShowClassName)) {
            input.attr('type', 'text');
            eye.attr('href', eyeOnSVG);
            input.addClass(passwordShowClassName);
        } else {
            input.attr('type', 'password');
            eye.attr('href', eyeOffSVG);
            input.removeClass(passwordShowClassName);
        }
    });
}
