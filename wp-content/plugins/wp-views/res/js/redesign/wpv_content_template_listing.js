jQuery(function($) {

	var query = '';
	var data_ct_id = null;

	$(document).on('input keyup','.js-duplicated-ct-name', function(e){
		$('.js-ct-duplicate-error .toolset-alert').remove();
		if ($(this).val() === ""){
		 $('.js-duplicate-ct').prop('disabled',true).addClass('button-secondary').removeClass('button-primary');
		}
		else{
			$('.js-duplicate-ct').prop('disabled',false).removeClass('button-secondary').addClass('button-primary');
		}
	});

	//Process: assign selected content template to all posts types
	$(document).on('click','.js-wpv-content-template-update-posts-process', function() {
		var type = $(this).data('type');
		var tid = $(this).data('id');
		var data = {
			action : 'set_view_template_listing',
			view_template_id : tid,
			wpnonce : $('#work_view_template').attr('value'),
			type : type,
			lang : ''
		};
		$.post(ajaxurl, data, function(response) {
			$.colorbox({
                 html: response,
                 onClose: function() {

                 }
             });
			$('.js-alret-icon-hide-'+type).hide()
		});
	});


	//Proccess change post type for template
	$(document).on('click','.js-ct-change-type-process', function(e) {
		e.preventDefault();
		var spinnerContainer = $('<div class="spinner ajax-loader">').insertBefore($(this)).show();
		$(this).prop('disabled', true).addClass('button-secondary').removeClass('button-primary');
        var type = [];
        $("input[name='wpv-new-content-template-post-type[]']:checked").each(function(){type.push($(this).val());});
        var id = $(this).data('id');
        var data = {
             action : 'ct_change_types_process',
             view_template_id : id,
             wpnonce : $('#work_view_template').attr('value'),
             type : type
        };
          $.post(ajaxurl, data, function(response) {
		if ( (typeof(response) !== 'undefined') ) {
			if ( response == 'ok' ) {
				  navigateWithURIParams(decodeURIParams());
			} else {
				  console.log( "Error: AJAX returned ", response );
			}
		} else {
			console.log( "Error: AJAX returned ", response );
		}
          });
          return false;
   });

	// Search box action

	$('#posts-filter').submit(function(e) {
		e.preventDefault();
		navigateWithURIParams(decodeURIParams($(this).serialize()));

		return false;
	});

    $(document).on('click','.js-remove-template-permanent', function(e) {
		e.preventDefault();
		var spinnerContainer = $('<div class="spinner ajax-loader">').insertBefore($(this)).show();
		$(this).prop('disabled', true).addClass('button-secondary').removeClass('button-primary');
		var url_params = decodeURIParams(),
		   url_page = null;
		if ( typeof(url_params['paged']) !== 'undefined' && url_params['paged'] > 1 ) {
			if ( $('.js-wpv-ct-list-row').length == 1) {
				url_page = 'paged=' + ( url_params['paged'] - 1 );
			}
		}
		var data = {
			action: 'wpv_delete_ct',
			wpnonce : $('#work_view_template').attr('value'),
			id: data_ct_id // get global data_ct_id
		};

		data_view_id = null;
		$.ajax({
			async:false,
			type:"POST",
			url:ajaxurl,
			data:data,
			success:function(response){
				if ( (typeof(response) !== 'undefined') && (response == data_ct_id)) {
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

    $(document).on('click','.js-duplicate-ct', function (e) {
	    e.preventDefault();
	    var spinnerContainer = $('<div class="spinner ajax-loader">').insertBefore($(this)).show();
	    $(this).prop('disabled', true).addClass('button-secondary').removeClass('button-primary');
		var newname = $('.js-duplicated-ct-name').val();

		if ( newname.length !== 0 ) {

       			var data = {
				action: 'wpv_duplicate_ct',
				id: data_ct_id,
				wpnonce : $('#work_view_template').attr('value'),
				name: newname
			};

			$.post(ajaxurl, data, function(response) {
				if ( (typeof(response) !== 'undefined') ) {
					response = $.parseJSON(response);
				//	console.log(response);
					if ( response[0] == 'error' ){
						$('.js-ct-duplicate-error').wpvToolsetMessage({
							text: response[1],
							stay: true,
							close: false,
							type: ''
				 		});
						spinnerContainer.remove();
					}
					else if ( response[0] == 'ok' ) {
						navigateWithURIParams(decodeURIParams());
					} else {
						console.log( "Error: AJAX returned ", response );
					}
				} else {
					console.log( "Error: AJAX returned ", response );
				}
			});

        }
    });

	$(document).on('change','.js-list-ct-action', function(e) {
		data_ct_id = $(this).attr('data-ct-id'); // set global data_ct_id

		// Delete content template
		if( $(this).val() === 'delete' ) {

			$.colorbox({
				href: '.js-remove-content-template-dialog',
				inline: true,
				onComplete: function() {

				}
			});
		}

		// Duplicate content template
		else if ( $(this).val() === 'duplicate' ) {
			$('.js-ct-duplicate-error .toolset-alert').remove();
			$('.js-duplicate-ct').prop('disabled',true);
            $.colorbox({
                href: '.js-duplicate-ct-dialog',
                inline: true,
                onComplete: function() {

                    var $input = $('.js-duplicated-ct-name');
                    var $submitButton = $('.js-duplicate-ct');

                    $input.focus().val('');

                }
            });

		}

		// Load list of Post Types/Taxonomies for assing to template
		else if ( $(this).val() === 'change' ) {
             e.preventDefault();
             var $thiz = $(this);
             $.colorbox({
                 href: ajaxurl+'?wpnonce='+$('#work_view_template').attr('value')+'&action=ct_change_types&id='+data_ct_id,
                 inline : false,
                 onComplete: function() {

                 }
             });
         }

         // Assign  Content template to Post type/Taxonomy
         else if ( $(this).val() === 'change_pt' ) {
             e.preventDefault();
             var $thiz = $(this);
             var $thiz_option = $thiz.find('option:selected');
             $.colorbox({
                 href: ajaxurl+'?wpnonce='+$('#work_view_template').attr('value')+'&action=ct_change_types_pt&pt='+$thiz_option.data('pt')
                 +'&msg='+$thiz_option.data('msg')+'&sort='+$thiz_option.data('sort'),
                 inline : false,
                 onComplete: function() {

                 }
             });
         }
		$('.js-list-ct-action option:selected').removeAttr('selected');
	});

	//reload pagination TODO maybe deprecated
	function reload_pages(query,activePage){
	  activePage = (typeof activePage == 'undefined')?1:activePage;
	  var sorting = $('.views-list-sort-active').data('orderby');

	  	var data = {
	        action : 'ct_reload_pages',
	        sort : 'name',
	        query : query,
	        page : WpvPagination.ajaxData.page,
			orderby : sorting,
	        wpnonce : $('#work_view_template').attr('value')
	    };

	    $.post(ajaxurl, data, function(response) {

	    		$('.wpv-listing-pagination')
	    			.empty()
	    			.append(response);
	            WpvPagination.ajaxData = { // set data object for AJAX request
					action: 'ct_change_view',
					view: 'name',
					query : query,
					orderby : sorting,
					wpnonce : $('#work_view_template').attr('value')
	            };
	            WpvPagination.totalPages = $('.js-wpv-listing-pagination-nav').length - 2;
	            WpvPagination.setActivePage(activePage);
	    });
    }


    //Proccess Post type/Taxonomy assign to template
    $(document).on('click','.js-ct-change-types-pt-process', function(e){
     	e.preventDefault();
	    var $thiz = $(this);
	    var sort = $thiz.data('sort');
	    var pt = $thiz.data('pt');
	    var value = $('input[name=wpv-new-post-type-content-template]:checked').val();

	    var data = {
	        action : 'ct_change_types_pt_process',
	        sort : sort,
	        pt: pt,
	        value : value,
	        wpnonce : $('#work_view_template').attr('value')
	    };
	    $.post(ajaxurl, data, function(response) {
	            $.colorbox.close();
	            $('.js-wpv-views-listing-body tr.js-wpv-ct-list-row').remove();
	            $('.js-wpv-views-listing-body').prepend(response);
	            $("html, body").animate({ scrollTop: 0 }, "slow");
	    });
	    return false;
	});

	//Open popup create new content template
	$(document).on('click','.js-add-new-content-template', function(e){
		e.preventDefault();
		var $spinnerContainer = $('<div class="spinner ajax-loader">').insertAfter($(this)).show();
		var $thiz = $(this);
		$.colorbox({
			href: $thiz.data('target') +'&wpnonce='+$('#work_view_template').attr('value'),
			inline : false,
			onComplete: function() {
				 $spinnerContainer.remove();
				 if ( !$thiz.data('disabled') ){
		  			$('.js-create-new-temlate').prop('disabled', true).addClass('button-secondary').removeClass('button-primary');
				 }
				$('.js-create-new-temlate').live("propertyChanged", function(event, args, val ){

					if( args == 'disabled' && val == true )
					{
						$(event.target).addClass('button-secondary').removeClass('button-primary');
					}
					else if( args == 'disabled' && val == false )
					{
						$(event.target).removeClass('button-secondary').addClass('button-primary');
					}
				});
			}
		});
		return false;
	});

	$(document).on('click','.js-ct-change-types-pt,.js-apply-for-all-posts', function(e){
	    e.preventDefault();
	    var $thiz = $(this);
	    $.colorbox({
	        href: $thiz.data('target') + '&wpnonce='+$('#work_view_template').attr('value'),
	        inline : false,
	        onComplete: function() {

	        }
	    });
	    return false;
	});


	$(document).on('input','.js-wpv-new-content-template-name', function(e){
		$('.js-error-container .toolset-alert').remove();
		if ($(this).val() === ""){
		 $('.js-create-new-temlate').prop('disabled',true);
		}
		else{
		 $('.js-create-new-temlate').prop('disabled',false);
		}
	});


	$(document).on('change','.js-wpv-dialog-add-new-content-template input[type=checkbox]',function(e){
		var $dontAssignInput = $('.js-dont-assign');
		var $allCheckboxes = $('.js-wpv-dialog-add-new-content-template input[type=checkbox]');
		if ( $(this).is(':checked') ) {
			if ( $(e.target).is($dontAssignInput) ) {
				$allCheckboxes.not(this).prop('checked',false)
			}
			else {
				$dontAssignInput.prop('checked',false)
			}
		}
	});

	//Dropdown for Post Types/Taxonomies
	$(document).on('click','.js-wpv-content-template-open', function(e) {
		e.preventDefault();
		var $dropdownList = $(this).parent().next('.js-wpv-content-template-dropdown-list');
		$dropdownList.toggle('fast',function(){
		if ( $dropdownList.is(':hidden') ) {
			$(this).prev('p').find('[class^="icon-"]')
				.removeClass('icon-caret-up')
				.addClass('icon-caret-down');
		} else {
			$(this).prev('p').find('[class^="icon-"]')
				.removeClass('icon-caret-down')
				.addClass('icon-caret-up');
		}
		});
		return false;
	});

	$(document).on('click','.js-create-new-temlate', function(e) {
		e.preventDefault();
		var $spinnerContainer = $('<div class="spinner ajax-loader">').insertAfter($(this)).show();
		var name = $('#wpv-add-new-content-template-form').find('input[type=text]').val();
		var type = [];
		$("input[name='wpv-new-content-template-post-type[]']:checked").each(function(){type.push($(this).val());});
		var title = $('input[name=wpv-new-content-template-post-type]:checked', '#wpv-add-new-content-template-form').data('title');
		 var data = {
			action : 'wpv_ct_create_new_save',
			name : name,
			wpnonce : $('#work_view_template').attr('value'),
			type : type,
			title: title
		};
		$('.js-create-new-temlate').prop('disabled', true);
		$.post(ajaxurl, data, function(response) {
			 response = $.parseJSON(response);
			 console.log(response);
			if ( (typeof(response) !== 'undefined') ) {
				 if ( response[0] == 'error' ){
					$('.js-error-container').wpvToolsetMessage({
						text: response[1],
						stay: true,
						close: false,
						type: ''
				 	});
				 	$('.js-create-new-temlate').prop('disabled', false);
					$spinnerContainer.remove();
				 }
				 else{
				 	console.log('Content Tempalte Created');
				 	document.location.href = 'post.php?post='+response[0]+'&action=edit';

				 }
			} else {
				//	console.log( "Error: AJAX returned " + response );
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			//	console.log( "Error: ", textStatus, errorThrown );
		})
		.always(function() {

		});
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

