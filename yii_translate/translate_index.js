const   header = document.querySelector('.header-panel'),
    csrfToken = $('[name="csrf-token"]').attr('content'),
    languages = $('#main-data').data('languages'),
    countLang = languages.length
standartError = 'Что-то пошло не так, попробуйте еще раз, либо напишите нам письмо. Спасибо!';

$(function() {
    /*--- Form Validation ---*/
    $(document).on('change', '.form_index', function() {
        $(this).validate(validateSettings);
    });

    /*--- Translation Buttons ---*/
    $(document).on('click', '[data-action]', function() {
        let $el = $(this),
            $form = $el.closest('.form_index'),
            $formBlock = $el.closest('.one_data'),
            action = $el.data('action'),
            countLangItem = $form.find('.lang-item').length;

        if ( !$form.valid() ) {
            return;
        }

        switch (action) {
            case 'update':
                $.ajax({
                    url: 'translate/save',
                    type: 'POST',
                    data: $.param($form.serializeArray()),
                    complete: function (xhr) {
                        let response = xhr.responseJSON;
                        let alertBlock = '<div class="alert alert-status alert-' +
                            (response.status==='success' ? 'success' : 'danger') +
                            ' mt-20" style="display: none;">' +
                            (xhr.status===200 ? response.message : standartError) +
                            '</div>';

                        $form.find('.messages').html(alertBlock);
                        $form.find('.alert-status').fadeIn().delay(3000).fadeOut(function() {$(this).remove()});

                        if (!$form.find('[name="id"]').val()) {
                            $form.find('[name="id"]').val(response.id);
                        }
                    }
                });
                return false;
                break;
            case 'create':
                let newForm = $(langForm).appendTo($('.translate')).slideDown().addClass('border-green');
                $('body, html').animate({ scrollTop: newForm.offset().top }, 800);
                $('.border-green').delay(2000).queue(function() { $(this).removeClass('border-green'); });
                newForm.find('.form_index').validate(validateSettings);
                break;
            case 'delete':
                if ($form.find('[name="id"]').val() === '') {
                    $formBlock.stop(true).slideUp(function() { $(this).remove(); });
                    return false;
                }

                $.ajax({
                    url: 'translate/delete',
                    type: 'POST',
                    data: 'id=' + $form.find('[name="id"]').val(),
                    complete: function (xhr) {
                        let response = xhr.responseJSON;
                        if (response.status==='success' && xhr.status===200) {
                            $formBlock.stop(true).slideUp(function() { $(this).remove(); });
                            return false;
                        }

                        let alertBlock = '<div class="alert alert-status alert-' +
                            (response.status==='success' ? 'success' : 'danger') +
                            ' mt-20" style="display: none;">' +
                            (xhr.status===200 ? response.message : standartError) +
                            '</div>';

                        $form.find('.messages').html(alertBlock);
                        $form.find('.alert-status').fadeIn().delay(3000).fadeOut(function() {$(this).remove()});
                    }
                });
                break;
            case 'copy':
                let clone = $formBlock.clone().addClass('border-green').hide(),
                    newItem = clone.insertAfter($formBlock);

                clone.find('[name="id"]').val('').attr('value', '');
                clone.find('[name="message"]').val('').attr('value', '');
                clone.find('[name^="translation"]').val('').attr('value', '');
                newItem.slideDown('slow');
                $('body, html').animate({ scrollTop: newItem.offset().top - 100 }, 800);
                $('.border-green').delay(2000).queue(function() { $(this).removeClass('border-green'); });
                break;
            case 'add_lang':
                if (countLangItem < countLang) {
                    let newLang = $(langItem).hide();
                    newLang.find('[name="lang_new"]').attr('name', 'lang_new' + countLangItem);
                    newLang.find('[name="translation_new"]').attr('name', 'translation_new' + countLangItem);
                    newLang.insertAfter( $formBlock.find('form > .row').last() );

                    if ($formBlock.find('.lang-item').length > 1) {
                        $formBlock.find('[data-action="delete_lang"]').fadeIn();
                    }

                    newLang.find('[name*="translation"]').val('');
                    newLang.slideDown();
                }

                if ($formBlock.find('.lang-item').length === countLang) {
                    $formBlock.find('[data-action="add_lang"]').fadeOut();
                }
                break;
            case 'delete_lang':
                $.ajax({
                    url: 'translate/delete-lang',
                    type: 'POST',
                    data: 'id2=' + $form.find('[name="id"]').val() + '&language=' + $el.closest('.lang-item').find('[name^="lang"]').attr('name').split('_')[1],
                    complete: function (xhr) {
                        let response = xhr.responseJSON;
                        if (response.status==='success' && xhr.status===200) {
                            if (countLangItem === countLang) {
                                $formBlock.find('[data-action="delete_lang"]').fadeOut();
                                $formBlock.find('[data-action="add_lang"]').fadeIn();
                            }

                            if (countLangItem > 1) {
                                $el.closest('.lang-item').slideUp(function() { $(this).remove(); });
                            }

                            return false;
                        }

                        let alertBlock = '<div class="alert alert-status alert-' +
                            (response.status==='success' ? 'success' : 'danger') +
                            ' mt-20" style="display: none;">' +
                            (xhr.status===200 ? response.message : standartError) +
                            '</div>';

                        $form.find('.messages').html(alertBlock);
                        $form.find('.alert-status').fadeIn().delay(3000).fadeOut(function() {$(this).remove()});
                    }
                });
                break;
            default:
                break;
        }
    });

    /*$(document).on('change', 'select[name^="lang"]', function() {
     let lang = $(this).find('option:selected').val();
     $(this).attr('name', 'lang_' + lang);
     $(this).closest('.lang-item').find('[name^="translation"]').attr('name', 'translation_' + lang)
     });*/

    /**
     * Show Errors
     */
    function showErrors(errors, invalidFields, currentForm) {
        let wrap_el = $('<div class="alert alert-danger mt-20" role="alert">'),
            errorsBlock = $(currentForm).find(".alert"),
            errorsList = '',
            formValid = true;

        for (let error in errors) {
            if (invalidFields[error]) {
                formValid = false;
                errorsList += `<div>${error}: ${errors[error]}</div>`;
            }
        }

        if (formValid) {
            errorsBlock.fadeOut(function() { $(this).remove(); });
            $(currentForm).data('valid', true);

            return false;
        }

        if (errorsBlock.length) {
            errorsBlock.html(errorsList);
        } else {
            wrap_el.hide();
            wrap_el.html(errorsList);
            wrap_el.appendTo($(currentForm).find('.messages'));
            wrap_el.fadeIn('slow');
        }
    }

    let validateSettings = {
        showErrors: function() {
            showErrors(this.submitted, this.invalid, this.currentForm);
        },
        rules: {
            category: {
                required: true,
                normalizer: function( value ) {
                    return $.trim( value );
                },
                minlength: 2,
            },
            message: {
                required: true,
                normalizer: function( value ) {
                    return $.trim( value );
                },
                minlength: 2,
            },
            language_ru: {
                required: true,
                normalizer: function( value ) {
                    return $.trim( value );
                },
                minlength: 2,
                maxlength: 3,
            },
            language_en: {
                required: true,
                normalizer: function( value ) {
                    return $.trim( value );
                },
                minlength: 2,
                maxlength: 3,
            },
            translation_ru: {
                required: true,
                normalizer: function( value ) {
                    return $.trim( value );
                },
                minlength: 2,
            },
            translation_en: {
                required: true,
                normalizer: function( value ) {
                    return $.trim( value );
                },
                minlength: 2,
            },
            translation_new: {
                required: true,
                normalizer: function( value ) {
                    return $.trim( value );
                },
                minlength: 2,
            }
        }
    };
});


window.onscroll = () => stickHeader();

function stickHeader() {
    if (window.pageYOffset > header.offsetTop) {
        header.classList.add('sticky', 'container-fluid');
    } else {
        header.classList.remove('sticky', 'container-fluid');
    }
}

let langListBlock = '';
languages.forEach((item) => langListBlock += `<option value="${item}">${item}</option>`);

const langItem =
    '            <div class="row lang-item">\n' +
    '                <div class="col-md-2">\n' +
    '                    <!-- SELECT LANG -->\n' +
    '                    <div class="form-group">\n' +
    '                        <label class="col-form-label">LANG</label>\n' +
    '                        <select name="lang_new">\n' + langListBlock + '</select>\n' +
    '                    </div>\n' +
    '                </div>\n' +
    '                <div class="col-lg-8 col-md-7">\n' +
    '                    <!-- TRANSLATION -->\n' +
    '                    <div class="form-group">\n' +
    '                        <label class="col-form-label">TRANSLATE</label>\n' +
    '                        <textarea class="form-control" name="translation_new"></textarea>\n' +
    '                    </div>\n' +
    '                </div>\n' +
    '                <!-- DELETE BUTTON -->\n' +
    '                <div class="col-lg-2 col-md-3 mb-20 mb-md-0 mt-md-5">\n' +
    '                    <button type="button" class="btn btn-outline-danger" data-action="delete_lang" style="display: none;">Delete translate</button>\n' +
    '                </div>\n' +
    '            </div>\n';

const langForm = '<div class="container one_data" style="display: none;">' +
    '<!-- START FORM -->\n' +
    '<form class="form_index" name="translate_update" method="post" enctype="multipart/form-data" data-valid="false">\n' +
    '<input type="hidden" name="_csrf" value="' + csrfToken + '">\n' +
    '<div class="row messages_block">\n' +
    '            <div class="col messages">\n' +
    '\n' +
    '            </div>\n' +
    '        </div>\n' +
    '\n' +
    '        <!-- Values up -->\n' +
    '        <div class="row">\n' +
    '            <div class="col-md-2">\n' +
    '                <!-- ID -->\n' +
    '                <div class="form-group">\n' +
    '                    <label class="col-form-label">ID</label>\n' +
    '                    <input type="text" class="form-control text-danger" name="id" readonly>\n' +
    '                </div>\n' +
    '            </div>\n' +
    '\n' +
    '            <div class="col-md-2">\n' +
    '                <!-- CATEGORY -->\n' +
    '                <div class="form-group">\n' +
    '                    <label class="col-form-label">CATEGORY</label>\n' +
    '                    <input type="text" class="form-control text-left" name="category">\n' +
    '                </div>\n' +
    '            </div>\n' +
    '\n' +
    '            <div class="col-md-3">\n' +
    '                <!-- MESSAGE -->\n' +
    '                <div class="form-group">\n' +
    '                    <label class="col-form-label">MESSAGE</label>\n' +
    '                    <input type="text" class="form-control" name="message">\n' +
    '                </div>\n' +
    '            </div>\n' +
    '\n' +
    '\n' +
    '            <!-- BUTTONS -->\n' +
    '            <div class="col-md-5 order-first order-md-last">\n' +
    '                <div class="row pt-15">\n' +
    '                    <div class="col-md-6">\n' +
    '                        <div class="form-group">\n' +
    '                            <button type="button" class="btn btn-info btn-block translate_save" data-action="update">Save</button>\n' +
    '                        </div>\n' +
    '                        <div class="form-group">\n' +
    '                            <button type="button" class="btn btn-block btn-danger" data-action="delete">Delete</button>\n' +
    '                        </div>\n' +
    '                    </div>\n' +
    '\n' +
    '                    <div class="col-md-6">\n' +
    '                        <div class="form-group">\n' +
    '                            <button type="button" class="btn btn-block btn-light" data-action="copy">Copy</button>\n' +
    '                        </div>\n' +
    '                        <div class="form-group">\n' +
    '                            <button type="button" class="btn btn-block btn-success" data-action="add_lang">Add Lang</button>\n' +
    '                        </div>\n' +
    '                    </div>\n' +
    '                </div>\n' +
    '            </div>\n' +
    '        </div>\n' +
    langItem +
    '        </form>        <!-- end form -->\n' +
    '    </div>';