/*  This is a temporary file, we should probably have separate JS for each admin page */

jQuery(function($) {

    $.extend($.colorbox.settings, { // override some Colorbox defaults
        transition: 'fade',
        opacity: 0.3,
        speed: 150,
        fadeOut : 0,
        close: '<i class="icon-remove-sign"></i>',
        onComplete: function() {
            $('#cboxClose').addClass('visible');
        },
        onCleanup: function() {
            $('#cboxClose').removeClass('visible');
        }
    });

    // close dialogs
    $(document).on('click','.js-dialog-close',function(e) {
        e.preventDefault();
        $.colorbox.close();
        return false;
    });

    $(document).on('click','.js-wpv-dialog-change li',function(){
    //    $('.js-wpv-dialog-change i').removeClass('icon-check-sign').addClass('icon-check-empty');
        $(this).find('i').addClass('icon-check-sign');
    });

});
