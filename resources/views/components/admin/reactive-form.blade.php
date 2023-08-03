<form id="main-form" method="{{ $method }}" action="{{ $action }}" @if($enctype) enctype="{{ $enctype }}" @endif>
    <div class="row">
        <div class="col-md-12 mb-3 text-danger ajaxError" id="form-global-error">
        </div>
    </div>
    <div class="reactive-form-loader d-flex justify-content-center align-items-center" id="main-form-loader" style="display: none !important">
        <div class="spinner-border mr-3" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    {{ $slot }}
</form>
@push('scripts')
    <script>
        jQuery.expr[':'].regex = function(elem, index, match) {
            var matchParams = match[3].split(','),
                validLabels = /^(data|css):/,
                attr = {
                    method: matchParams[0].match(validLabels) ?
                        matchParams[0].split(':')[0] : 'attr',
                    property: matchParams.shift().replace(validLabels,'')
                },
                regexFlags = 'ig',
                regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
            return regex.test(jQuery(elem)[attr.method](attr.property));
        }

        $(document).ready(function() {
            $('#main-form').submit(function (event) {
                event.preventDefault();
                submitForm($(this));
            });
        });

        function submitForm(form)
        {
            const formData = new FormData(form[0]);

            if (form.data('submitted') === true) {
                return;
            }

            form.data('submitted', true);
            form.find('button[type="submit"]').attr('disabled', true);
            form.find('#main-form-loader').removeAttr('style');

            $('.ajaxError').text('');

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.data.hasOwnProperty('redirect_to')  && data.data.redirect_to !== '') {
                        window.location.href = data.data.redirect_to;
                    }

                    form.data('submitted', false);
                    form.find('button[type="submit"]').removeAttr('disabled');
                    form.find('#main-form-loader').attr('style', 'display: none !important;');
                },
                error: function(data) {
                    if (data.status === 422) {
                        $.each(data.responseJSON.errors, function (field, errors) {
                            if (field.indexOf('.') !== -1) {
                                let regex = 'error-field-';
                                let isBase = true;
                                field.split('.').forEach(function (level) {
                                    if (isBase) {
                                        isBase = false;
                                        regex += level;
                                    } else {
                                        regex += `\\.(${level}|\\*)`;
                                    }
                                });
                                const errorFields = $(`div:regex(id, ${regex})`);
                                errorFields.each(function () {
                                    $(this).append(`<p>${errors[0]}</p>`);
                                });
                            } else {
                                const errorField = $(`#error-field-${field}`);
                                if (errorField) {
                                    errorField.html('');
                                    for(const error of errors) {
                                        errorField.append(`<p>${error}</p>`)
                                    }

                                }
                            }
                        });
                    } else {
                        $('#form-global-error').text('{{ trans('common.action_unexpected_error') }}');
                    }

                    form.data('submitted', false);
                    form.find('button[type="submit"]').removeAttr('disabled');
                    form.find('#main-form-loader').attr('style', 'display: none !important;');
                }
            });
        }
    </script>
@endpush
