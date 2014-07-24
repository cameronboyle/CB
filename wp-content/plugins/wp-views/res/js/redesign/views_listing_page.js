jQuery(document).ready(function($)  {

    $('.js-views-actions option').removeAttr('selected');

    // Store the view ID
    var data_view_id = null;

    // Process delete view
    /*
	$(document).on('click','.js-remove-view-permanent', function () {

		WpvPaginationOverlay.showOverlay();
			$.colorbox.close();

		var data = {
		action: 'wpv_delete_view',
		id: data_view_id,
		page: WpvPagination.getActivePage(),
		search_term: $('[name="s"]').val(),
		query_mode: 'normal',
		wpnonce : $('#work_views_listing').attr('value')
		};

		// set data_view_id back to default
		data_view_id = null;

		$.post(ajaxurl, data, function(response) {
		$('#wpv_view_list_row_'+data.id).fadeOut('fast',function(){

			var $tbody = $(this).parent();
			$(this).remove();

			if (response.content.length == 0 ) {
			$('.js-wpv-views-listing').hide();
				$('.js-wpv-views-add-new-top, .js-wpv-views-add-new, .js-wpv-views-listing-arrange, #posts-filter').hide();
				$('.js-wpv-view-not-exist').show();
			} else {
			$('.js-wpv-views-listing tbody').empty().append(response.content);
			}

			$('.wpv-listing-pagination').empty().append(response.pager);
				WpvPaginationOverlay.hideOverlay();

		});

		}, "json");

	});
	*/

	$(document).on('click','.js-remove-view-permanent', function () {
		var spinnerContainer = $('<div class="spinner ajax-loader">').insertBefore($(this)).show();
		$(this).prop('disabled', true).addClass('button-secondary').removeClass('button-primary');
		
		var url_params = decodeURIParams(),
		       url_page = null;
		if ( typeof(url_params['paged']) !== 'undefined' && url_params['paged'] > 1 ) {
			if ( $('.js-wpv-view-list-row').length == 1) {
				url_page = 'paged=' + ( url_params['paged'] - 1 );
			}
		}
		var data = {
			action: 'wpv_delete_view_permanent',
			id: data_view_id,
			wpnonce : $(this).data('nonce')
		};
		$.ajax({
			async:false,
			type:"POST",
			url:ajaxurl,
			data:data,
			success:function(response){
				if ( (typeof(response) !== 'undefined') && (response == data.id)) {
					navigateWithURIParams(decodeURIParams(url_page));
				} else {
					console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() {

			}
		});
	});

	/*
    $(document).on('click','.js-duplicate-view', function () {

        var newname = $('.js-duplicated-view-name').val();

        WpvPaginationOverlay.showOverlay();
        $('.js-duplicate-view').prop('disabled',true);

        if ( newname.length !== 0 ) {

            var data = {
                action: 'wpv_duplicate_view',
                id: data_view_id, // read the global data_view_id variable
                name: newname,
                page_num: $('.wpv-listing-pagination .active').text(), // TODO: Shouldn't we use WpvPagination.getActivePage() here?
                wpnonce : $('#work_views_listing').attr('value')
            };

            //data_view_id = null;

            $.post(ajaxurl, data, function(response) {
            	if ( (typeof(response) !== 'undefined') && response !== null) {
	            	if( response.error == 'error'){
		        		$('.js-view-duplicate-error').wpvToolsetMessage({
							text: response.error_message,
							stay: true,
							close: false,
							type: ''
						 	});
						$('.js-duplicate-view').prop('disabled',false);
						WpvPaginationOverlay.hideOverlay();
						return false;
		        	}

		                $('.js-wpv-views-listing tbody').empty().append(response.content);
		                $('.wpv-listing-pagination').empty().append(response.pager);
		                $('#wpv_view_list_row_'+response.new_row_id).hide();
		                $(response.new_row).insertAfter($('#wpv_view_list_row_'+data_view_id));
		                WpvPaginationOverlay.hideOverlay();
		 				$.colorbox.close();




               }
            }, "json");

        }
    }); */

	$(document).on('click','.js-duplicate-view', function () {

		var newname = $('.js-duplicated-view-name').val();

	//	WpvPaginationOverlay.showOverlay();
		$('.js-duplicate-view').prop('disabled',true).addClass('button-secondary').removeClass('button-primary');
		var spinnerContainer = $('<div class="spinner ajax-loader">').insertBefore($(this)).show();

		if ( newname.length !== 0 ) {

			var data = {
				action: 'wpv_duplicate_this_view',
				id: data_view_id, // read the global data_view_id variable
				name: newname,
				wpnonce :  $(this).data('nonce')
			};
			var error = $(this).data('error');

			$.ajax({
				async:false,
				type:"POST",
				url:ajaxurl,
				data:data,
				success:function(response){
					if ( ( typeof(response) !== 'undefined' ) && ( response == data.id ) ) {
						navigateWithURIParams(decodeURIParams());
					} else if ( ( typeof(response) !== 'undefined' ) && ( response == 'error' ) ) {
						$('.js-view-duplicate-error').wpvToolsetMessage({
							text: error,
							stay: true,
							close: false,
							type: ''
						});
						spinnerContainer.remove();
					} else {
						console.log( "Error: AJAX returned ", response );
					}
				},
				error: function (ajaxContext) {
					console.log( "Error: ", ajaxContext.responseText );
				},
				complete: function() {

				}
			});
		}
	});

    $(document).on('change', '.js-views-actions', function() {

        data_view_id = $(this).data('view-id');

        if ( $(this).val() === 'delete' ) {

            $.colorbox({
                 href: '.js-delete-view-dialog',
                 inline: true,
                 onComplete: function() {
                 //    WpvPaginationOverlay.hideOverlay();
                 }
             });

        }

        else if ( $(this).val() === 'duplicate' ) {
			$('.js-view-duplicate-error .toolset-alert').remove();
            $.colorbox({
                 href: '.js-duplicate-view-dialog',
                 inline: true,
                 onComplete: function() {

                     var $input = $('.js-duplicated-view-name');
                     var $submitButton = $('.js-duplicate-view');

               //      WpvPaginationOverlay.hideOverlay();

                     $input.focus().val('');

                     $input.keyup(function(){
                     	$('.js-view-duplicate-error .toolset-alert').remove();
                        if ( $(this).val().length !== 0 ) {
                            $submitButton
                                .prop('disabled', false)
                                .removeClass('button-secondary')
                                .addClass('button-primary');
                        } else {
                            $submitButton
                                .prop('disabled', true)
                                .removeClass('button-primary')
                                .addClass('button-secondary');
                        }
                     });
                 }
             });

        }

        $('.js-views-actions option').removeAttr('selected');
        $('#list_views_action_'+data_view_id).val($('#list_views_action_'+data_view_id+' option:first').val());

    });

    $(document).on('click', '.js-scan-button', function() {

        if ( !$(this).data('loading') ) {

            var data_view_id = $(this).attr('data-view-id');
            var thiz = $(this);
            var $cellParent = thiz.parent();

            thiz
                .data('loading',true)
                .attr('disabled');

            var $spinnerContainer = $('<div class="spinner ajax-loader">').insertAfter($(this)).show();
            var data = {

                    action: 'wpv_scan_view',
                    id: data_view_id,
                    wpnonce : $('#work_views_listing').attr('value')
            };

            var loadPosts = $.post( ajaxurl , data, function(responseData) {


                if ( (typeof(responseData) !== 'undefined') && responseData !== null) {

                    thiz
                        .data('loading',false)
                        .remove();
                    $spinnerContainer.remove();

                    var $postsList = $('<ul class="posts-list">');
                    $postsList.appendTo($cellParent);

                    $.each(responseData,function(index, value){
                        $('<li><a target="_blank" href="'+value.link+'">'+value.post_title+'</a></li>').appendTo($postsList);
                    });


                } else {
                    thiz.parent().find('.js-nothing-message').show();
                    thiz.remove();

                }

             }, "json");

            loadPosts.fail(function(){ // function executed when ajax call fails
                //
            });

            loadPosts.always(function(){ // function executed when ajax request us complete. Makes no difference if success or not.
                thiz
                    .data('loading',false)
                    .removeAttr('disabled');
                $spinnerContainer.remove();
            });

        }

    });

    // Search function

    $('#posts-filter').submit(function(e) {
	    e.preventDefault();
	    navigateWithURIParams(decodeURIParams($(this).serialize()));
        return false;
    });

    // Change pagination items per page

    $(document).on('change', '.js-items-per-page', function() {
	    navigateWithURIParams(decodeURIParams('paged=1&items_per_page=' + $(this).val()));
    });

    $(document).on('click', '.js-wpv-display-all-items', function(e){
	    e.preventDefault();
	    navigateWithURIParams(decodeURIParams('paged=1&items_per_page=-1'));
    });

    $(document).on('click', '.js-wpv-display-default-items', function(e){
	    e.preventDefault();
	    navigateWithURIParams(decodeURIParams('paged=1&items_per_page=20'));
    });

    // add new View

    $(document).on('click', '.js-wpv-views-add-new-top, .js-wpv-views-add-new, .js-wpv-views-add-first', function(e) {
	    e.preventDefault();
	    $.colorbox({
		    inline:true,
		href: '.js-create-view-form-dialog',
		open:true,
		onComplete: function() {
			$('.js-create-new-view').prop('disabled', true).removeClass('button-primary').addClass('button-secondary');
			if (0 < $('input.js-view-purpose:checked').length) {
				$('input.js-view-purpose:checked').prop('checked', false);
				$('.js-create-view-form-dialog').find('.toolset-alert').remove();
			}
			//	thiz.prop('disabled',true);
		},
		onClosed : function() {
			//	thiz.prop('disabled',false);
		}
	    })
    });

	$(document).on('change keyup input cut paste', '.js-view-purpose, .js-new-post_title', function(){
	    $('.js-create-view-form-dialog').find('.toolset-alert').remove();
	    if ('' != $('input.js-new-post_title').val() && 0 < $('input.js-view-purpose:checked').length) {
		    $('.js-create-new-view').prop('disabled', false).addClass('button-primary').removeClass('button-secondary');
	    } else {
		    $('.js-create-new-view').prop('disabled', true).removeClass('button-primary').addClass('button-secondary');
	    }
	    if ('' == $('.js-new-post_title').val()) {
		    $('.js-new-post_title').focus().parent().wpvToolsetMessage({
			    text:$('input.js-new-post_title').data('highlight'),
			    type:'info',
			    stay: true
		    });
	    }
	});

	$(document).on('click', '.js-create-new-view', function(e){
		e.preventDefault();
		$('.js-create-new-view').addClass('button-secondary').removeClass('button-primary');
		$thiz = $(this);
		var spinnerContainer = $('<div class="spinner ajax-loader">').insertAfter($(this)).show();
		var title = $('.js-new-post_title').val();
		var purpose = $('input.js-view-purpose:checked').val();
		var data = {
			action: 'wpv_create_view',
			title: title,
			purpose: purpose,
			wpnonce : $('#wp_nonce_create_view').attr('value')
		};
		$thiz.prop('disabled',true);
		$.post(ajaxurl, data, function(response) {
			if ( (typeof(response) !== 'undefined') ) {
				temp_res = jQuery.parseJSON(response);

				if ( temp_res.error == 'error' ){
					console.log(temp_res.error_message);
					$('.js-error-container').wpvToolsetMessage({
						text:temp_res.error_message,
						type: '',
						stay: true
					});
					$thiz.prop('disabled',false);
					spinnerContainer.remove();
					$('.js-create-new-view').addClass('button-primary').removeClass('button-secondary');
					return false;
				}
				if (response != 0) {
					var url = $('.js-view-new-redirect').val();
					$(location).attr('href',url + response);
				} else {
					console.log( "Error: WordPress AJAX returned ", response );
				}
			} else {
				$('<span class="updated">error</span>').insertAfter($('.js-create-new-view')).hide().fadeIn(500).delay(1500).fadeOut(500, function(){
					$(this).remove();
				});
				console.log( "Error: AJAX returned ", response );
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			if ($('.js-create-view').parent().find('.unsaved').length < 1) {
				$('<span class="message unsaved"><i class="icon-warning-sign"></i> error</span>').insertAfter($('.js-create-new-view')).show();
			}
			console.log( "Error: ", textStatus, errorThrown );
		})
		.always(function() {

		});
	});

	// Redirection functions for search, delete and duplicate

	function decodeURIParams(query) {
		if (query == null)
			query = window.location.search;
		if (query[0] == '?')
			query = query.substring(1);

		var params = query.split('&');
		var result = {};
		for (var i = 0; i < params.length; i++) {
			var param = params[i];
			var pos = param.indexOf('=');
			if (pos >= 0) {
				var key = decodeURIComponent(param.substring(0, pos));
				var val = decodeURIComponent(param.substring(pos + 1));
				result[key] = val;
			} else {
				var key = decodeURIComponent(param);
				result[key] = true;
			}
		}
		return result;
	}

	function encodeURIParams(params, addQuestionMark) {
		var pairs = [];
		for (var key in params) if (params.hasOwnProperty(key)) {
			var value = params[key];
			if (value != null) /* matches null and undefined */ {
				pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(value))
			}
		}
		if (pairs.length == 0)
			return '';
		return (addQuestionMark ? '?' : '') + pairs.join('&');
	}

	function navigateWithURIParams(newParams) {
		window.location.search = encodeURIParams($.extend(decodeURIParams(), newParams), true);
	}

});
