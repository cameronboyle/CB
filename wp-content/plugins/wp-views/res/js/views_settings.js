jQuery(function($){

    var $cfList = $('.js-cf-toggle');
    var $cfSummary = $('.js-cf-summary');

    $('.js-show-cf-list').on('click', function(e) {
        e.preventDefault();
        $cfList.show();
        $cfSummary.hide();
        return false;
    });

    $('.js-hide-cf-list').on('click', function(e) {
        e.preventDefault();
        $cfList.hide();
        $cfSummary.show();
        return false;
    });

    // Save CF options
    $('.js-save-cf-list').on('click', function(e) {
        e.preventDefault();

        var $spinner = $('.js-cf-spinner').css('display','inline-block');
        var $selectedCfList = $('.js-selected-cf-list');
        var $checked = $('.js-all-cf-list :checked');
        var $cfExistsMessage = $('.js-cf-exists-message');
        var $cfNotExistsMessage = $('.js-no-cf-message');
        var data;

        data = $('.js-all-cf-list input[type="checkbox"]').serialize();
        data += '&action=wpv_get_show_hidden_custom_fields';
        data += '&wpv_show_hidden_custom_fields_nonce=' + $('#wpv_show_hidden_custom_fields_nonce').val();

        $.ajax({
            async:false,
            type:"POST",
            url:ajaxurl,
            data:data,
            success:function(response){
                if ( (typeof(response) !== 'undefined') ) {

                    $selectedCfList.empty();

                    if ( $checked.length !== 0 ) {

                        $cfExistsMessage.show();
                        $cfNotExistsMessage.hide();
                        $selectedCfList.show();

                        $.each( $checked, function() {
                            $selectedCfList.append('<li>' + $(this).next('label').text() + '</li>');
                        });

                    }

                    else {

                        $cfExistsMessage.hide();
                        $cfNotExistsMessage.show();
                        $selectedCfList.hide();

                    }

                    $cfSummary.show();
                    $cfList.hide();
                    $('.js-cf-update-message').show().fadeOut('slow');
                }
                else {
                    console.log( "Error: AJAX returned ", response );
                }
            },
            error: function (ajaxContext) {
                console.log( "Error: ", ajaxContext.responseText );
            },
            complete: function() {
				$spinner.hide();
            }
        });

        return false;
    });

    // FIXME: Nonce required!
    // TODO: Add ajax errors handling

    // Save debug options
    $('.js-save-debug-settings').on('click', function(e) {
        e.preventDefault();

        var $spinner = $('.js-debug-spinner');
        var data;
        var $thiz = $(this);

        $spinner.css('display','inline-block');
        $thiz
            .prop('disabled', true)
            .removeClass('button-primary')
            .addClass('button-secondary');

        data = $('.js-debug-settings-form :input').serialize();
        data += '&action=wpv_save_theme_debug_settings';

        $.ajax({
            async:false,
            type:"POST",
            url:ajaxurl,
            data:data,
            success:function(response){
                if ( (typeof(response) !== 'undefined') ) {
                    if (response == 'ok') {
                        $('.js-debug-update-message').show().fadeOut('slow');

                        $thiz
                        .prop('disabled', false)
                        .removeClass('button-secondary')
                        .addClass('button-primary');
                    }
                    else {
                        console.log( "Error: WordPress AJAX returned ", response );
                    }
                }
                else {
                    console.log( "Error: AJAX returned ", response );
                }
            },
            error: function (ajaxContext) {
				console.log( "Error: ", ajaxContext.responseText );
            },
            complete: function() {
				$spinner.hide();
            }
        });

        return false;
    });

    // Save WPML options
    $('.js-save-wpml-settings').on('click', function(e) {
        e.preventDefault();

        var $spinner = $('.js-wpml-spinner');
        var data;
        var $thiz = $(this);

        $spinner.css('display','inline-block');
        $thiz
            .prop('disabled', true)
            .removeClass('button-primary')
            .addClass('button-secondary');

        data = $('.js-wpml-settings-form :input').serialize();
        data += '&action=wpv_save_wpml_settings';

        $.ajax({
            async:false,
            type:"POST",
            url:ajaxurl,
            data:data,
            success:function(response){
                if ( (typeof(response) !== 'undefined') ) {
                    if (response == 'ok') {
                        $('.js-wpml-update-message').show().fadeOut('slow');

                        $thiz
                        .prop('disabled', false)
                        .removeClass('button-secondary')
                        .addClass('button-primary');
                    }
                    else {
                        console.log( "Error: WordPress AJAX returned ", response );
                    }
                }
                else {
                    console.log( "Error: AJAX returned ", response );
                }
            },
            error: function (ajaxContext) {
                console.log( "Error: ", ajaxContext.responseText );
            },
            complete: function() {
                $spinner.hide();
            }
        });

        return false;
    });

});