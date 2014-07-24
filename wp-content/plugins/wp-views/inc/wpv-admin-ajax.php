<?php

/*
* General file for all AJAX calls
* All AJAX calls used in the backend must be set here
*/

/*
* Views & WPA edit sceen
*/

// Screen options save callback function

add_action('wp_ajax_wpv_save_screen_options', 'wpv_save_screen_options_callback');

function wpv_save_screen_options_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_show_hide_nonce') ) die("Security check");
	$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
	if ( isset( $_POST['settings'] ) ) {
		parse_str($_POST['settings'], $settings);
		foreach ($settings as $section => $state) {
			$view_array['sections-show-hide'][$section] = $state;
		}
	}
	if ( isset( $_POST['helpboxes'] ) ) {
		parse_str($_POST['helpboxes'], $help_settings);
		foreach ($help_settings as $section => $state) {
			$view_array['metasections-hep-show-hide'][$section] = $state;
		}
	}
	if ( isset( $_POST['purpose'] ) ) {
		$view_array['view_purpose'] = $_POST['purpose'];
	}
	update_post_meta($_POST["id"], '_wpv_settings', $view_array);
	echo $_POST["id"];
	die();
}

// Title and description save callback function

add_action('wp_ajax_wpv_update_title_description', 'wpv_update_title_description_callback');

function wpv_update_title_description_callback() {
    global $wpdb;
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_title_description_nonce') ) die("Security check");
	$view_desc = get_post_meta($_POST["id"], '_wpv_description', true);
	$view_title = get_the_title($_POST["id"]);
	$result = true;
	$return = $_POST["id"];
    $postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . mysql_real_escape_string($_POST["title"]) . "' AND post_type='view' AND ID!=". $return ." " );
    if ( !empty($postid)  ){
           $edit = 'Archive';
           if ( isset($_POST['edit']) ){
                $edit = $_POST['edit'];
           }

           print json_encode( array('error', sprintf( __( 'A %s with that name already exists. Please use another name.', 'wpv-views' ), $edit )) );
           die();
    }
	$value = filter_input_array(INPUT_POST, array('description' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => !FILTER_FLAG_STRIP_LOW)));
	if (!isset($view_desc) || $value['description'] != $view_desc) {
		$view_desc = $value['description'];
		$result = update_post_meta($_POST["id"], '_wpv_description', $view_desc);
	}
	if ($_POST["title"] != $view_title) {
	   	$view = array();
		$view['ID'] = $_POST["id"];
		//$view['post_title'] = sanitize_text_field($_POST["title"]);
        $view['post_title'] = $_POST["title"];
		$return = wp_update_post( $view );
	}

	echo $result ? $return : false;
        die();
}

// Loop selection save callback function

add_action('wp_ajax_wpv_update_loop_selection', 'wpv_update_loop_selection_callback');

function wpv_update_loop_selection_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_loop_selection_nonce') ) die("Security check");
	global $WPV_view_archive_loop;
	parse_str($_POST['form'], $form_data);
	$WPV_view_archive_loop->update_view_archive_settings($_POST["id"], $form_data);
	echo $_POST["id"];
	die();
}

// Query type save callback function

add_action('wp_ajax_wpv_update_query_type', 'wpv_update_query_type_callback');

function wpv_update_query_type_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_query_type_nonce') ) die("Security check");
	$changed = false;
	if (!isset($_POST["post_types"])) $_POST["post_types"] = array('any');
	$view_array = get_post_meta($_POST["id"],'_wpv_settings', true);
	if (isset($view_array['query_type']) && isset($view_array['query_type'][0]) && $view_array['query_type'][0] == $_POST["query_type"]) {
	} else {
		$view_array['query_type'] = array($_POST["query_type"]);
		$changed = true;
	}
	if (!isset($view_array['post_type']) || $view_array['post_type'] != $_POST["post_types"]) {
		$view_array['post_type'] = $_POST["post_types"];
		$changed = true;
	}
	if (!isset($view_array['taxonomy_type']) || $view_array['taxonomy_type'] != $_POST["taxonomies"]) {
		$view_array['taxonomy_type'] = $_POST["taxonomies"];
		$changed = true;
	}
	if ($changed) {
		$result = update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		echo $result ? $_POST["id"] : false;
	} else {
		echo $_POST["id"];
	}
	die();
}

// Query options save callback function

add_action('wp_ajax_wpv_update_query_options', 'wpv_update_query_options_callback');

function wpv_update_query_options_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_query_options_nonce') ) die("Security check");
	$changed = false;
	$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
	if (!isset($view_array['post_type_dont_include_current_page']) || $_POST["dont"] != $view_array['post_type_dont_include_current_page']) {
		$view_array['post_type_dont_include_current_page'] = $_POST["dont"];
		$changed = true;
	}
	if (!isset($view_array['taxonomy_hide_empty']) || $_POST["hide"] != $view_array['taxonomy_hide_empty']) {
		$view_array['taxonomy_hide_empty'] = $_POST["hide"];
		$changed = true;
	}
	if (!isset($view_array['taxonomy_include_non_empty_decendants']) || $_POST["empty"] != $view_array['taxonomy_include_non_empty_decendants']) {
		$view_array['taxonomy_include_non_empty_decendants'] = $_POST["empty"];
		$changed = true;
	}
	if (!isset($view_array['taxonomy_pad_counts']) || $_POST["pad"] != $view_array['taxonomy_pad_counts']) {
		$view_array['taxonomy_pad_counts'] = $_POST["pad"];
		$changed = true;
	}
	if ($changed) {
		$result = update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		echo $result ? $_POST["id"] : false;
	} else {
		echo $_POST["id"];
	}
	die();
}

// Sorting save callback function

add_action('wp_ajax_wpv_update_sorting', 'wpv_update_sorting_callback');

function wpv_update_sorting_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_ordering_nonce') ) die("Security check");
	$changed = false;
	$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
	if (!isset($view_array['orderby']) || $_POST["orderby"] != $view_array['orderby']) {
		$view_array['orderby'] = $_POST["orderby"];
		$changed = true;
	}
	if (!isset($view_array['order']) || $_POST["order"] != $view_array['order']) {
		$view_array['order'] = $_POST["order"];
		$changed = true;
	}
	if (!isset($view_array['taxonomy_orderby']) || $_POST["taxonomy_orderby"] != $view_array['taxonomy_orderby']) {
		$view_array['taxonomy_orderby'] = $_POST["taxonomy_orderby"];
		$changed = true;
	}
	if (!isset($view_array['taxonomy_order']) || $_POST["taxonomy_order"] != $view_array['taxonomy_order']) {
		$view_array['taxonomy_order'] = $_POST["taxonomy_order"];
		$changed = true;
	}
	if ($changed) {
		$result = update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		echo $result ? $_POST["id"] : false;
	} else {
		echo $_POST["id"];
	}
	die();
}

// Limit and offset save callback function

add_action('wp_ajax_wpv_update_limit_offset', 'wpv_update_limit_offset_callback');

function wpv_update_limit_offset_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_limit_offset_nonce') ) die("Security check");
	$changed = false;
	$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
	if (!isset($view_array['limit']) || $_POST["limit"] != $view_array['limit']) {
		$view_array['limit'] = $_POST["limit"];
		$changed = true;
	}
	if (!isset($view_array['offset']) || $_POST["offset"] != $view_array['offset']) {
		$view_array['offset'] = $_POST["offset"];
		$changed = true;
	}
	if (!isset($view_array['taxonomy_limit']) || $_POST["taxonomy_limit"] != $view_array['taxonomy_limit']) {
		$view_array['taxonomy_limit'] = $_POST["taxonomy_limit"];
		$changed = true;
	}
	if (!isset($view_array['taxonomy_offset']) || $_POST["taxonomy_offset"] != $view_array['taxonomy_offset']) {
		$view_array['taxonomy_offset'] = $_POST["taxonomy_offset"];
		$changed = true;
	}
	if ($changed) {
		$result = update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		echo $result ? $_POST["id"] : false;
	} else {
		echo $_POST["id"];
	}
	die();
}

// Pagination save callback function

add_action('wp_ajax_wpv_update_pagination', 'wpv_update_pagination_callback');

function wpv_update_pagination_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_pagination_nonce') ) die("Security check");
	$changed = false;
	parse_str($_POST['settings'], $settings);
	$defaults = array(
		'pagination' => array(
		'preload_images' => 0,
		'cache_pages' => 0,
		'preload_pages' => 0,
		),
		'rollover' => array(
		'preload_images' => 0,
		),
	);
	$settings = wpv_parse_args_recursive($settings, $defaults);
	$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
	if ( $view_array['posts_per_page'] != $settings['posts_per_page'] ) {
		$view_array['posts_per_page'] = $settings['posts_per_page'];
		$changed = true;
	}
	if ( $view_array['pagination'] != $settings['pagination'] ) {
		$view_array['pagination'] = $settings['pagination'];
		$changed = true;
	}
	if ( $view_array['ajax_pagination'] != $settings['ajax_pagination'] ) {
		$view_array['ajax_pagination'] = $settings['ajax_pagination'];
		$changed = true;
	}
	if ( $view_array['rollover'] != $settings['rollover'] ) {
		$view_array['rollover'] = $settings['rollover'];
		$changed = true;
	}
	if ($changed) {
		$result = update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		echo $result ? $_POST["id"] : false;
	} else {
		echo $_POST["id"];
	}
	die();
}

// Filter Extra save callback function

add_action('wp_ajax_wpv_update_filter_extra', 'wpv_update_filter_extra_callback');

function wpv_update_filter_extra_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_filter_extra_nonce') ) die("Security check");
	$changed = false;
	$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
	if (!isset($view_array['filter_meta_html']) || $_POST["query_val"] != $view_array['filter_meta_html']) {
		$view_array['filter_meta_html'] = $_POST["query_val"];

			wpv_add_controls_labels_to_translation( $_POST["query_val"], $_POST["id"] );

		$changed = true;
	}
	if (!isset($view_array['filter_meta_html_css']) || $_POST["query_css_val"] != $view_array['filter_meta_html_css']) {
		$view_array['filter_meta_html_css'] = $_POST["query_css_val"];
		$changed = true;
	}
	if (!isset($view_array['filter_meta_html_js']) || $_POST["query_js_val"] != $view_array['filter_meta_html_js']) {
		$view_array['filter_meta_html_js'] = $_POST["query_js_val"];
		$changed = true;
	}
	if ($changed) {
		$result = update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		echo $result ? $_POST["id"] : false;
	} else {
		echo $_POST["id"];
	}
	die();
}
//utility function to translate strings
function wpv_add_controls_labels_to_translation( $content, $view_id )
{
	if( function_exists('icl_register_string') )
	{
		/*
		** Array of fields to be checked
		*/
		$tobechecked = array(
			'display_values',
			'default_label',
			'title',
			'auto_fill_default',
			'name');
		/*
		** If there are commas escaped please replace with '|' (pipe char)
		*/
		$content = str_replace("\\\\\,", '|', $content);
		/*
		** Strip all slashes if any left
		*/
		$content = stripslashes( $content );
		/*
		** Make a context out of View title
		*/
		$context = get_post_field( 'post_name', $view_id );
		/*
		** Empty array to store what's already being parsed (when BETWEEN or NOT BETWEEN we can have 2 recorrences of the same labels)
		*/
		$control = array();

		/*
		** Loop through all our fields
		*/
		foreach( $tobechecked as $string )
		{
			/*
			** Make sure we have parameters in the form of param="
			*/
			if( strpos( $content, $string.'="' ) !== false )
			{
				/*
				** Subquery 1: ( (url_param\s*?=\"(.*?)\").*?)? make sure if we have 0 or more occurences of 'url_param="' and take the value (.*?) in a subquery
				** array[3]
				** Subquery 3: this is our main without ? operator (".$string."\s*?=\"(.*?)\"), if there is store (.*?) subquery value in array[5]
				*/
				preg_match_all( "/( (url_param\s*?=\"(.*?)\").*?)?(".$string."\s*?=\"(.*?)\")/", $content, $matches );
				/*
				** If we have a corrsponding match on (".$string."\s*?=\"(.*?)\") this one and first element of result array is not empty loop
				*/
				if( isset( $matches[5] ) && isset( $matches[5][0] ) )
				{
					/*
					** Loop through results and store $key for control
					*/
					foreach( $matches[5] as $key=>$translate )
					{
						/*
						** If we have values we will store first element of the list here to be translated
						*/
						$translate_first = '';
						/*
						** If we have values keep track if the first display_value should be translated or not
						*/

						$should_do = false;

						/*
						** Make sure we do not already have a translatable string for this occurence
						*/
						if( !in_array($translate, $control) )
						{

							/*
							** If we have values loop through them
							*/
							if( $string == 'display_values' )
							{
								$should_do = true;
								/*
								** Keep track of the values already pushed for translation f we have more occurences of same value
								*/

								$trs_values = array();
								/*
								** Loop through values
								*/

									/*
									** Translate only display_values if first value is empty and we didn't push it already
									*/

										$translate_first = explode( ',', $translate );
										foreach( $translate_first as $trs_first )
										{
											$trs_first = str_replace('|', ',', $trs_first );
											array_push($trs_values, $trs_first);
										}

							}

							/**
							** Take the key of the actual record to translate
							**/
							$key_t = array_search( $translate, $matches[5] );

							/**
							** Create a name for label to translate
							**/
							$name = !empty( $matches[3][$key_t] ) ? $matches[3][$key_t] : 'submit';

							/**
							** If eligible for translation do
							**/

								if( $should_do )
								{
									$count_values = 1;
									foreach( $trs_values as $trs )
									{
										icl_register_string( "View ".$context, $name.'_'.$string."_".$count_values, $trs );
										$count_values++;
									}
									$trs_first = '';
								}
								else
								{
									icl_register_string( "View ".$context, $name.'_'.$string, $translate );
								}

								array_push($control, $translate);

						}
					}
				}
			}
		}
	}
}


/*
* Views listing screen
*/

// View create callback function

add_action('wp_ajax_wpv_create_view', 'wpv_create_view_callback');

function wpv_create_view_callback() {
	global $wpdb;
	if (! wp_verify_nonce($_POST["wpnonce"], 'wp_nonce_create_view') ) die("Security check");

	if (!isset($_POST["title"]) || $_POST["title"] == '') $_POST["title"] = __('Unnamed View', 'wp-views');
    $postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $_POST["title"] . "' AND post_type='view'" );
    if ( !empty($postid)  ){
        $result['error'] = 'error';
        $result['error_message'] = __( 'A View with that name already exists. Please use another name.', 'wpv-views' );
        echo json_encode($result);
        die();
    }
	$post = array(
		'post_type'	=> 'view',
		'post_title'	=> $_POST["title"],
		'post_status'	=> 'publish',
		'post_content'	=> "[wpv-filter-meta-html]\n[wpv-layout-meta-html]"
	);
	$id = wp_insert_post( $post );
	if ( 0 != $id ) {
		if (!isset($_POST["kind"])) $_POST["kind"] = 'normal';
		if (!isset($_POST["purpose"])) $_POST["purpose"] = 'full';
		switch($_POST["kind"]) {
		case 'archive':
			$view_normal_defaults = wpv_wordpress_archives_defaults('view_settings');
			$view_normal_layout_defaults = wpv_wordpress_archives_defaults('view_layout_settings');
			update_post_meta($id, '_wpv_settings', $view_normal_defaults);
			update_post_meta($id, '_wpv_layout_settings', $view_normal_layout_defaults);
		break;
		default:
			$view_normal_defaults = wpv_view_defaults('view_settings', $_POST["purpose"]);
			$view_normal_layout_defaults = wpv_view_defaults('view_layout_settings', $_POST["purpose"]);
			if ( $_POST["purpose"] == 'slider' ){
				$temp = wpv_create_new_ct_for_slider_view( $id, $_POST["title"]);
				$ct_post = get_post($temp);
				$view_normal_layout_defaults['layout_meta_html'] =
				str_replace('<wpv-loop>','<wpv-loop>[wpv-post-body view_template="'.$ct_post->post_title.'"]',$view_normal_layout_defaults['layout_meta_html']);
				$view_normal_layout_defaults['included_ct_ids'] = $temp.',';
				update_post_meta($id, '_wpv_first_time_load', 'on');
			}
			update_post_meta($id, '_wpv_settings', $view_normal_defaults);
			update_post_meta($id, '_wpv_layout_settings', $view_normal_layout_defaults);
		break;
		}
		echo $id;
        } else {
		echo 'error';
        }
        die();
}

// View usage callback action

add_action('wp_ajax_wpv_scan_view', 'wpv_scan_view_callback');

function wpv_scan_view_callback() {
    global $wpdb;

    $nonce = $_POST["wpnonce"];
    if (! wp_verify_nonce($nonce, 'work_views_listing') ) die("Security check");

    $view = get_post($_POST["id"]);

    $list = '';
    $list .= '<ul class="posts-list">';

    $q = 'SELECT DISTINCT * FROM `'.$wpdb->prefix.'posts` WHERE
     ID in (SELECT ID FROM `'.$wpdb->prefix.'posts` WHERE post_content like \'%[wpv-view%name="%'.mysql_real_escape_string($view->post_title).'%"]%\' and post_type not in (\'revision\') AND post_status="publish")
     OR
     ID in (SELECT post_id FROM `'.$wpdb->prefix.'postmeta` WHERE meta_value LIKE \'%[wpv-view%name="%'.mysql_real_escape_string($view->post_title).'%"]%\' AND post_status="publish")';
    $res = $wpdb->get_results($q, OBJECT);


   if (!empty($res)) {
        $items = array();
        foreach ($res as $row) {
            $item = array();

            $type = get_post_type_object($row->post_type);

            $type = $type->labels->singular_name;

            $item['post_title'] = "<b>".$type.": </b>".$row->post_title;

            if ($row->post_type=='view')
                $edit_link = get_admin_url()."admin.php?page=views-editor&view_id=".$row->ID;
            else
                $edit_link = get_admin_url()."post.php?post=".$row->ID."&action=edit";

            $item['link'] = $edit_link;

            $items[] = $item;
        }
        echo json_encode($items);
    }


    die();
}

// View duplicate callback function 

add_action('wp_ajax_wpv_duplicate_this_view', 'wpv_duplicate_this_view_callback');

function wpv_duplicate_this_view_callback() {
	if (! wp_verify_nonce($_POST["wpnonce"], 'wpv_duplicate_view_nonce') ) die("Security check");
	global $wpdb;
	$postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . mysql_real_escape_string($_POST["name"]) . "' AND post_type='view'" );
	if ( !empty($postid)  ){
		echo 'error';
		die;
	}

	$old_post_id = $_POST["id"];
	$original_post = get_post($old_post_id, ARRAY_A);

	$original_post['post_title'] = $_POST["name"];
	$original_post['post_date'] = date("Y-m-d H:i:s");

	unset($original_post['ID']);

	$new_post_id = wp_insert_post($original_post);

	$view_array = get_post_meta($old_post_id, '_wpv_settings', true);
	$view_layout_array = get_post_meta($old_post_id, '_wpv_layout_settings', true);
	update_post_meta($new_post_id, '_wpv_settings', $view_array);
	update_post_meta($new_post_id, '_wpv_layout_settings', $view_layout_array);

	echo $_POST["id"];
	die();
}

// Only used in CT for now

add_action('wp_ajax_wpv_admin_menu_views_pager', 'wpv_admin_menu_views_pager_callback');
function wpv_admin_menu_views_pager_callback() {

	$sorting = isset($_POST['sorting']) ? $_POST['sorting'] : '';
    echo wpv_admin_menu_views_listing($_POST['view_query_mode'], $_POST['page'], $_POST["search_term"], $sorting);

    die();
}

/*
* WP Archive listing screen
*/

// Add WP Archive popup in usage arrange callback function

add_action('wp_ajax_wpv_create_usage_archive_view_popup', 'wpv_create_usage_archive_view_popup_callback');

function wpv_create_usage_archive_view_popup_callback(){
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_wp_archive_arrange_usage') ) die("Security check");
    global $WPV_view_archive_loop, $WP_Views;
    $options = $WP_Views->get_options();
    $loops = $WPV_view_archive_loop->_get_post_type_loops();
        ?>
        <div class="wpv-dialog wpv-dialog-change js-wpv-dialog-change">
                <div class="wpv-dialog-header">
                    <h2><?php _e('Name of WordPress Archive for','wpv-views'); ?> <strong><?php echo $_POST['for_whom']; ?></strong></h2>
                    <i class="icon-remove js-dialog-close"></i>
                </div>
		<form id="wpv-add-wp-archive-for-loop-form">
                <div class="wpv-dialog-content">		
		<div class="hidden">
                    <?php
                        foreach($loops as $loop => $loop_name) {
                            foreach ($options as $opt_id=> $opt_name) {
				                if ('view_'.$loop == $opt_id && $opt_name !== 0) {
                                    unset($loops[$loop]);
                                    break;
                                }
                            }
                        }
                    ?>

                    <?php if (!empty($loops)) {  ?>
                        <?php foreach($loops as $loop => $loop_name) { ?>
                            <?php $checked = ( $loop_name == $_POST['for_whom'] ) ? ' checked="checked"' : ''; ?>
                                <input type="checkbox" <?php echo $checked; ?> name="wpv-view-loop-<?php echo $loop; ?>" />
                        <?php }; ?>
                    <?php } ?>

                    <?php
                    $taxonomies = get_taxonomies('', 'objects');
                    $exclude_tax_slugs = array();
			$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );
                        foreach ($taxonomies as $category_slug => $category) {
                           if ( in_array( $category_slug, $exclude_tax_slugs ) ) {
                                    unset($taxonomies[$category_slug]);
                                    continue;
                            };
                            foreach ($options as $opt_id=> $opt_name) {
				if ('view_taxonomy_loop_' . $category_slug == $opt_id && $opt_name !== 0) {
                                    unset($taxonomies[$category_slug]);
                                    break;
                                };
                            };
                        };
                    ?>

                    <?php if (!empty($taxonomies)): ?>
                        <?php foreach ($taxonomies as $category_slug => $category): ?>
                            <?php
                                $name = $category->name;
                                $checked = ( $category->labels->name == $_POST['for_whom'] ) ? ' checked="checked"' : '';
                            ?>
                            <input type="checkbox" <?php echo $checked; ?> name="wpv-view-taxonomy-loop-<?php echo $name; ?>" />
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
		
                    <p>
                        <input type="text" value="" class="js-wpv-new-archive-name wpv-new-archive-name" placeholder="<?php echo htmlentities( __('Content archive name','wpv-views'), ENT_QUOTES ) ?>" name="wpv-new-archive-name">
                    </p>
                    <div class="js-wp-archive-create-error"></div>
                </div>
		</form>
                <div class="wpv-dialog-footer">
                    <button class="button-secondary js-dialog-close" type="button" name="wpv-archive-view-cancel"><?php _e('Cancel', 'wpv-views'); ?></button>
                    <button class="button-secondary js-wpv-add-wp-archive-for-loop" disabled="disabled" name="wpv-archive-view-ok" data-error="<?php echo htmlentities( __('A WordPress Archive with that name already exists. Please use another name.', 'wpv-views'), ENT_QUOTES ); ?>" data-url="<?php echo admin_url( 'admin.php?page=view-archives-editor&amp;view_id='); ?>">
                        <?php _e('Add new WordPress Archive', 'wpv-views'); ?>
                    </button>
                </div>
        </div>
    <?php die();
}

// Create WP Archive in usage arrange callback function

add_action('wp_ajax_wpv_create_usage_archive_view', 'wp_ajax_wpv_create_usage_archive_view_callback');

function wp_ajax_wpv_create_usage_archive_view_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'work_views_listing') ) die("Security check");

        global $wpdb, $WPV_view_archive_loop;
        parse_str($_POST['form'], $form_data);

	// Create archive
	$postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $form_data["wpv-new-archive-name"] . "' AND post_type='view'" );
	if ( !empty($postid)  ){
	echo 'error';
	die();
	}
	$new_archive = array(
	'post_title'    => $form_data["wpv-new-archive-name"],
	'post_type'      => 'view',
	'post_content'  => "[wpv-layout-meta-html]",
	'post_status'   => 'publish',
	'post_author'   => get_current_user_id(),
	'comment_status' => 'closed'
	);
	$post_id = wp_insert_post($new_archive);

	$archive_defaults = wpv_wordpress_archives_defaults('view_settings');
	$archive_layout_defaults = wpv_wordpress_archives_defaults('view_layout_settings');
	update_post_meta($post_id, '_wpv_settings', $archive_defaults);
	update_post_meta($post_id, '_wpv_layout_settings', $archive_layout_defaults);

	$WPV_view_archive_loop->update_view_archive_settings($post_id, $form_data);

	echo $post_id;
	die();
}

// Change Archive usage in usage arrange popup

add_action('wp_ajax_wpv_show_views_for_loop', 'wpv_show_views_for_loop_callback');

function wpv_show_views_for_loop_callback() {
        global $WPV_view_archive_loop, $wpdb, $WP_Views;
	if (! wp_verify_nonce($_POST["wpnonce"], 'wpv_wp_archive_arrange_usage') ) die("Security check");

        $options = $WP_Views->get_options();

        $loops = $WPV_view_archive_loop->_get_post_type_loops();

?>
        <div class="wpv-dialog wpv-dialog-change js-wpv-dialog-change">
            <form id="wpv-archive-view-form-for-loop">
                <div class="wpv-dialog-header">
                    <h2><?php _e('Select WordPress Archive For Loop','wpv-views'); ?></h2>
                    <i class="icon-remove js-dialog-close"></i>
                </div>
                <div class="wpv-dialog-content">
                    <?php wp_nonce_field('wpv_view_edit_nonce', 'wpv_view_edit_nonce'); ?>

                    <input type="hidden" value="<?php echo $_POST["id"]; ?>" name="wpv-archive-loop-key" />

                    <?php
                        $q = ('
                            SELECT DISTINCT wpp.* FROM `'.$wpdb->prefix.'posts` wpp
                            WHERE
                                ID in (SELECT post_id FROM `'.$wpdb->prefix.'postmeta` WHERE `meta_value` like \'%view-query-mode%"archive"%\' AND `meta_key`="_wpv_settings")
                            AND
                                wpp.post_status="publish"
                            AND
                                wpp.post_type="view"
                            ORDER BY wpp.post_date DESC
                        ');

                        $res = $wpdb->get_results($q, OBJECT);

                        ?>
                        <h3><?php _e('Archive views', 'wpv-views'); ?></h3>
                        <ul>
                            <li>
                                <label>
                                    <input type="radio" name="wpv-view-loop-archive" value="0" /> <?php _e('Don\'t use a WordPress Archive for this loop', 'wpv-views'); ?>
                                </label>
                            </li>
                        <?php
                        foreach ($res as $view) {
                            $checked = '';
                            if (isset($options[$_POST["id"]]) && $view->ID == $options[$_POST["id"]]) {
                                $checked = ' checked ';
                            }
                            ?>
                            <li>
                                <label>
                                    <input type="radio" <?php echo $checked; ?> name="wpv-view-loop-archive" value="<?php echo $view->ID; ?>" /> <?php echo $view->post_title; ?>
                                </label>
                            </li>
                            <?php
                        }
                        ?>
                        </ul>

                </div>
                <div class="wpv-dialog-footer">
                    <button class="button-secondary js-dialog-close" type="button" name="wpv-archive-view-cancel"><?php _e('Cancel', 'wpv-views'); ?></button>
                    <button class="button-primary js-update-archive-for-loop" type="button" name="wpv-archive-view-ok">
                        <?php _e('Accept', 'wpv-views'); ?>
                    </button>
                </div>
            </form>
        </div>
<?php
        die();
}

// Change Archive usage in usage arrange

add_action('wp_ajax_wpv_update_archive_for_view', 'wpv_update_archive_for_view_callback');

function wpv_update_archive_for_view_callback() {
	global $WP_Views;
//	global $WPV_view_archive_loop;
	if (! wp_verify_nonce($_POST["wpnonce"], 'wpv_wp_archive_arrange_usage') ) die("Security check");

	$options = $WP_Views->get_options();

	$options[$_POST["loop"]] = $_POST["selected"];
	foreach($options as $key => $value) {
		if ($value == 0) unset($options[$key]);
	}

	$WP_Views->save_options( $options );

	echo 'ok';
	die();
}

// Delete Views and WPA permanently

add_action('wp_ajax_wpv_delete_view_permanent', 'wpv_delete_view_permanent_callback');

function wpv_delete_view_permanent_callback() {
        global $WPV_view_archive_loop, $WP_Views;

	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_remove_view_permanent_nonce') ) die("Security check");

	wp_delete_post($_POST["id"]);

        $options = $WP_Views->get_options();
        WP_Views_archive_loops::clear_options_data($options);
        $WP_Views->save_options($options);

        echo $_POST["id"];
        die();
}

// Add up, down or first WordPress Archive popup

add_action('wp_ajax_wpv_create_wp_archive_button', 'wpv_create_wp_archive_button_callback');

function wpv_create_wp_archive_button_callback() {

	if (! wp_verify_nonce($_POST["wpnonce"], 'work_views_listing') ) die("Security check");

        global $WPV_view_archive_loop;
        echo $WPV_view_archive_loop->_create_view_archive_popup();
        die();
}

// add up, down or first WordPress Archive action
// Uses the same callback as in the usage arrange mode

add_action('wp_ajax_wpv_create_archive_view', 'wp_ajax_wpv_create_usage_archive_view_callback');

// Change usage for Archive in name arrange popup

add_action('wp_ajax_wpv_archive_change_usage_popup', 'wpv_archive_change_usage_popup_callback');

function wpv_archive_change_usage_popup_callback() {
    if (! wp_verify_nonce($_POST["wpnonce"], 'work_views_listing') ) die("Security check");

        global $WPV_view_archive_loop;

        $id = $_POST["id"];

        echo $WPV_view_archive_loop->_create_view_archive_popup($id);
        die();
}

// Change usage for Archive in name arrange action

add_action('wp_ajax_wpv_archive_change_usage', 'wpv_archive_change_usage_callback');

function wpv_archive_change_usage_callback() {
	global $wpdb;
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'work_views_listing') ) die("Security check");

        global $WPV_view_archive_loop;
        parse_str($_POST['form'], $form_data);

	$archive_id = $form_data["wpv-archive-view-id"];

	$WPV_view_archive_loop->update_view_archive_settings($archive_id, $form_data);
	echo 'ok';
	die();
}

/*
* Content Templates
*/

/*
 * Update posts template from Content Template Edit screen Sidebar
 * Added by Gen
 */
add_action('wp_ajax_wpv_ct_update_posts', 'wpv_ct_update_posts_callback');
function wpv_ct_update_posts_callback(){
    if ( !isset($_GET["wpnonce"]) || ! wp_verify_nonce($_GET["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");
    global $wpdb, $WP_Views;
    $options = $WP_Views->get_options();
    if ( isset ($_GET['type']) && isset($_GET['tid']) ){
        $type = $_GET['type'];
        $tid = $options['views_template_for_' . $type];
    }
    else{
      return;
    }

    $posts = $wpdb->get_col( $wpdb->prepare("SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE post_type='%s' AND post_status!='auto-draft'",$type) );
    $count = sizeof( $posts );
    if ( $count > 0 ) {
         $posts = "'" . implode( "','", $posts ) . "'";
         $set_count = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE
                        meta_key='_views_template' AND meta_value='{$options['views_template_for_' . $type]}'
                        AND post_id IN ({$posts})" );
         if ( ( $count - $set_count ) > 0){
             $ptype = get_post_type_object($type);
             $type_label = $ptype->labels->singular_name;
             $message = sprintf( __( '%d %s uses a different Content Template.', 'wpv-views' ), ( $count - $set_count ) , $type_label );
             if ( ( $count - $set_count ) >1 ){
                $type_label = $ptype->labels->name;
                $message = sprintf( __( '%d %s use a different Content Template.', 'wpv-views' ), ( $count - $set_count ) , $type_label );
             }
         ?>

            <div class="wpv-dialog">
                <div class="wpv-dialog-header">
                	<h2><?php _e('Do you want to apply to all?','wpv-views'); ?></h2>
                	<i class="icon-remove js-dialog-close"></i>
                </div>
                <div class="wpv-dialog-content">
                   <?php echo $message; ?>
                </div>
                <div class="wpv-dialog-footer">
                    <button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
                    <button class="button button-primary js-wpv-content-template-update-posts-process"
                    data-type="<?php echo $type;?>"
                    data-id="<?php echo $tid;?>">
                    <?php echo sprintf( __( 'Update %s now', 'wpv-views' ), $type_label ) ?></button>
                </div>
            </div>
         <?php
         }
     }

        die();
}

/*
 * Add new Content Template
 * Added by Gen
 */
add_action('wp_ajax_wpv_ct_create_new', 'wpv_ct_create_new_callback');
function wpv_ct_create_new_callback(){
   if ( !isset($_GET["wpnonce"]) || ! wp_verify_nonce($_GET["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");
   global $wpdb, $WP_Views;
   $options = $WP_Views->get_options();
   $post_types_array = wpv_get_pt_tax_array();
   $post_type_names = array(
    'single_post'=> __( 'Single pages', 'wpv-views' ),
    'taxonomy_post' => __( 'Taxonomy archives', 'wpv-views' ),
    'archive_post' => __( 'Post archives', 'wpv-views' )
   );
   $ct_title = $ct_selected = '';
   if ( isset($_GET['ct_title']) ){
       $ct_title = $_GET['ct_title'];
   }
   if ( isset($_GET['ct_selected']) ){
       $ct_selected = $_GET['ct_selected'];
   }

   ?>
    <div class="wpv-dialog js-wpv-dialog-add-new-content-template wpv-dialog-add-new-content-template">
        <form method="" id="wpv-add-new-content-template-form">
	        <div class="wpv-dialog-header">
	            <h2><?php _e('Add new Content Template','wpv-views'); ?></h2>
	            <i class="icon-remove js-dialog-close"></i>
	        </div>
	        <div class="wpv-dialog-content">
	            <p><strong><?php _e('What content will this template be for?','wpv-views') ?></strong></p>

                <p>
                	<label>
                		<input type="checkbox" class="js-dont-assign" <?php echo $ct_selected == ''? ' checked="checked"':''?> name="wpv-new-content-template-post-type[]" value="0" /> <?php _e("Don't assign to any post type",'wpv-views') ?>
                	</label>
                </p>

                <?php
                foreach ($post_types_array as $post_type_name => $post_types) {
                    $open_ul = 0;
                    
                    for ($i=0;$i<count($post_types);$i++){
                        $show_hidden = 0;
                        $type = $post_types[$i][0];
                        $label = $post_types[$i][1];
                        $input_name = 'views_template_for_' . $type;
                        $type_used = false;
                        $type_current = false;
                        $is_selected = '';
                        if ( $input_name == $ct_selected){
                            $is_selected  = ' checked="checked"';
                        }

                        if ( $post_type_name == 'single_post' ){
                            if ( !isset($options['views_template_for_' . $type]) ){
                                $options['views_template_for_' . $type] = 0;
                                $WP_Views->save_options( $options );
                            }
                            if ( isset($options['views_template_for_' . $type]) && $options['views_template_for_' . $type] != 0 ){
                                $type_used = true;
                            }
                            if ( $open_ul == 0 && !isset( $$post_type_name)){
                               for ($j=0;$j<count($post_types);$j++){
                                   $temp_type = $post_types[$j][0];
                               }
                           }
                        }
                        if ( $post_type_name == 'taxonomy_post' ){
                           if ( !isset($options['views_template_loop_' . $type]) ){
                                $options['views_template_loop_' . $type] = 0;
                                $WP_Views->save_options( $options );
                           }
                           if ( isset($options['views_template_loop_' . $type]) && $options['views_template_loop_' . $type] != 0 ){
                                $type_used = true;
                           }
						   $input_name = 'views_template_loop_' . $type;
                           if ( $open_ul == 0 && !isset( $$post_type_name)){
                               for ($j=0;$j<count($post_types);$j++){
                                   $temp_type = $post_types[$j][0];
                               }
                           }
                        }
                        if ( $post_type_name == 'archive_post' ){
                           if ( !isset($options['views_template_archive_for_' . $type]) ){
                                $options['views_template_archive_for_' . $type] = 0;
                                $WP_Views->save_options( $options );
                           }
                           if ( isset($options['views_template_archive_for_' . $type]) && $options['views_template_archive_for_' . $type] != 0 ){
                                $type_used = true;
                           }
						   $input_name = 'views_template_archive_for_' . $type;
                           if ( $open_ul == 0 && !isset( $$post_type_name)){
                               for ($j=0;$j<count($post_types);$j++){
                                   $temp_type = $post_types[$j][0];
                               }
                           }
                        }
                        if ( !isset( $$post_type_name ) ){
                            $$post_type_name = 1;

                            if ( $open_ul == 0){
                                $hide = '';
                                if ( $post_type_name != 'single_post'){ ?>
                                        </ul>
                                    </div> <!-- .wpv-content-template-dropdown-list -->
                                    <?php
                                }
                            }
                            ?>
                            <p>
                                <span class="js-wpv-content-template-open wpv-content-template-open" title="<?php echo htmlentities( __( "Click to toggle", 'wpv-views' ), ENT_QUOTES ); ?>">
                                    <?php echo $post_type_names[$post_type_name]; ?>:
                                    <i class="icon-caret-down"></i>
                                </span>
                            </p>
                            <?php
                            if ( $open_ul == 0){
                                $hide = '';
                                if ( $show_hidden == 0){
                                     $hide = 'hidden';
                                } ?>
                                <div class="js-wpv-content-template-dropdown-list wpv-content-template-dropdown-list <?php echo $hide ?>">
                                    <ul>
                                <?php
                                $open_ul = 1;
                            }


                        }

                        ?>
                        <li>
                            <label>
                                <input type="checkbox"<?php echo $is_selected;?> name="wpv-new-content-template-post-type[]"
                                data-title="<?php echo htmlentities( $label, ENT_QUOTES ); ?>" value="<?php echo htmlentities( $input_name, ENT_QUOTES );?>" />
                                <?php echo $label; ?>
                            </label>
                        </li>
                    <?php
                    }
                } ?>
                    </ul>
                </div> <!-- .wpv-content-template-dropdown-list -->
                <p>
                	<strong><?php _e('Name this Content Template','wpv-views') ?></strong>
                </p>
	            <p>
	                <input type="text" value="<?php echo htmlentities( $ct_title, ENT_QUOTES ); ?>" class="js-wpv-new-content-template-name wpv-new-content-template-name" placeholder="<?php echo htmlentities( __('Content template name','wpv-views'), ENT_QUOTES ) ?>" name="wpv-new-content-template-name">
	            </p>
                <div class="js-error-container">
                </div>
	        </div> <!-- .wpv-dialog-content -->
	        <div class="wpv-dialog-footer">
	            <button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
	            <button class="button button-primary js-create-new-temlate"><?php _e('Create a template','wpv-views') ?></button>
	        </div>
        </form>

    </div> <!-- wpv-dialog -->
    <?php
     die();
}
/*
 * Save new Content template
 * Added by Gen
 */
add_action('wp_ajax_wpv_ct_create_new_save', 'wpv_ct_create_new_save_callback');
function wpv_ct_create_new_save_callback(){
    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");

    global $wpdb, $WP_Views;
    $options = $WP_Views->get_options();


        $name = $title = '';
        if ( isset($_POST['name']) ){
            $name = mysql_real_escape_string($_POST['name']);
        }
        if ( isset($_POST['title']) ){
           $title = $_POST['title'];
        }
        $type = $_POST['type'];
        $old_post = get_page_by_title( $name , OBJECT, 'view-template');
        if ( is_object($old_post) ){
           print json_encode( array('error', __( 'A Content Template with that name already exists. Please use another name.', 'wpv-views' )) );
           die();
        }

        $new_template = array(
          'post_title'    => $name,
          'post_type'      => 'view-template',
          'post_content'  => '',
          'post_status'   => 'publish',
          'post_author'   => 1, // TODO check why author here
//          'post_name' => sanitize_title( $name )
        );

        $post_id = wp_insert_post( $new_template );
        update_post_meta( $post_id, '_wpv-content-template-decription', '');
        if ( $type[0] != '0' ){
             for ($i=0;$i<count($type);$i++){
                 $options[$type[$i]] = $post_id;
             }
             $WP_Views->save_options( $options );
        }
        print json_encode( array($post_id) );

   die();
}
add_action('wp_ajax_wpv_ct_check_name_exists', 'wpv_ct_check_name_exists_callback');
function wpv_ct_check_name_exists_callback(){
    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'set_view_template') ) die("Undefined Nonce.");

    global $wpdb, $post;

        $name = $title = '';
        if ( isset($_POST['title']) ){
            $name = mysql_real_escape_string($_POST['title']);
        }
        $id = $_POST['id'];
        $postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $name . "' AND post_type='view-template' AND ID!=". $id ." " );
        if ( !empty($postid)  ){
           print json_encode( array('error', __( 'A Content Tempalte with that name already exists. Please use another name.', 'wpv-views' )) );
           die();
        }
        print json_encode( array('Ok', '') );
   die();
}

// Delete content template
add_action('wp_ajax_wpv_delete_ct', 'wpv_delete_ct_callback');
function wpv_delete_ct_callback(){

    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");

    global $wpdb, $WP_Views;
    $options = $WP_Views->get_options();
    $tid = $_POST['id'];
    foreach ($options as $key => $value) {
         if ($value == $tid){
            $options[$key] = 0;
         }
    }
    $WP_Views->save_options( $options );
    wp_delete_post($tid);
	echo $tid;
    die();
}

//Duplicate Content template
add_action('wp_ajax_wpv_duplicate_ct', 'wpv_duplicate_ct_callback');
function wpv_duplicate_ct_callback(){
    global $wpdb;
    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");
        
        $postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . mysql_real_escape_string($_POST["name"]) . "'  AND post_type='view-template'" );
        if ( !empty($postid)  ){
               print json_encode( array('error', __( 'A Content Template with that name already exists. Please use another name.', 'wpv-views' ) ) );
               die();
        }
        
        $old_post_id = $_POST["id"];
        $original_post = get_post($old_post_id, ARRAY_A);
        unset($original_post['ID']);
        $new_template = array(
          'post_title'    => $_POST["name"],
          'post_type'      => 'view-template',
          'post_content'  => $original_post['post_content'],
          'post_status'   => 'publish',
          'post_author'   => 1 // TODO check why author here
        );
        $new_post_id = wp_insert_post($new_template);
        update_post_meta( $new_post_id, '_wpv-content-template-decription', '');
        $old_post_meta = get_post_meta($old_post_id);
        foreach ($old_post_meta as $key => $value) {
            if ($key == '_edit_lock'){
                continue;
            }
            update_post_meta($new_post_id, $key, $value[0]);
        }
        print json_encode( array('ok') );

    die();
}
//Assing Content template to view popup
add_action('wp_ajax_wpv_assign_ct_to_view', 'wpv_assign_ct_to_view_callback');
function wpv_assign_ct_to_view_callback(){
    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'wpv-ct-inline-edit') ) die("Undefined Nonce.");

        $view_id = $_POST['view_id'];
        $meta = get_post_meta( $view_id, '_wpv_layout_settings', true);
        $templates = array();
        if ( isset($meta['included_ct_ids']) && $meta['included_ct_ids'] != '' ){
            $templates = explode( ',', $meta['included_ct_ids']);
        }
       ?>

       <div class="wpv-dialog js-wpv-dialog-add-new-content-template">
                <form method="" id="wpv-add-new-content-template-form">
                <div class="wpv-dialog-header">
                    <h2><?php _e('Add new Content Template','wpv-views') ?></h2>
                    <i class="icon-remove js-dialog-close"></i>
                </div>
                <div class="wpv-dialog-content">
                    <p class="<?php echo count($templates) < 1 ? 'hidden' : '' ?>">
                        <input type="radio" name="wpv-ct-type" value="2" id="js-wpv-ct-type-existing-asigned">
                        <label for="js-wpv-ct-type-existing-asigned"><?php _e('A Content Template that is already connected to this View','wpv-views') ?>: </label>
                        <select id="js-wpv-ct-add-id-assigned">
                            <option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
                            <?php
                             $not_in = '';
                             for ($i=0; $i<count($templates)-1; $i++){
                                 $template_post = get_post($templates[$i]);
                                 if ( is_object($template_post) ){
                                    $not_in .=  $template_post->ID.',';
                                    echo '<option value="'.$template_post->ID.'">'. $template_post->post_title .'</option>';
                                 }
                             }
                            ?>
                        </select>
                    </p>
                    <?php $query =  get_posts(array( 'post_type' => 'view-template', 'exclude'=> $not_in, 'orderby' => 'title', 'order' => 'ASC', 'posts_per_page' => '-1' ));
                    ?>
                    <p class="<?php echo count($query) < 1 ? 'hidden' : '' ?>">
                        <input type="radio" name="wpv-ct-type" value="0" id="js-wpv-ct-type-existing">
                        <label for="js-wpv-ct-type-existing"><?php _e('Connect an existing Content template to this View','wpv-views') ?>: </label>
                        <select id="js-wpv-ct-add-id">
                            <option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
                            <?php

                            foreach( $query as $temp_post ) :
                                echo '<option value="'.$temp_post->ID.'">'. $temp_post->post_title .'</option>';
                            endforeach;
                            ?>
                        </select>
                    </p>
                    <p>
                        <input type="radio" name="wpv-ct-type" value="1" id="js-wpv-ct-type-new">
                        <label for="js-wpv-ct-type-new"><?php _e('Create a new Content Template for this View','wpv-views') ?>: </label>
                        <input type="text" id="js-wpv-ct-type-new-name" placeholder="Name">
                    </p>
                    <div class="js-add-new-ct-error-container"></div>
                    <p id="js-wpv-add-to-editor-line">
                        <input type="checkbox" class="js-wpv-add-to-editor-check" name="wpv-ct-add-to-editor" value="1" id="js-wpv-ct-add-to-editor-btn" checked="checked">
                        <label for="js-wpv-ct-add-to-editor-btn"><?php _e('Insert shortcode to editor','wpv-views') ?></label>
                    </p>
                </div>
                <div class="wpv-dialog-footer">
                    <button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
                    <button class="button button-primary js-create-new-temlate"><?php _e('Add template','wpv-views') ?></button>
                </div>
                </form>
         </div>

       <?php

    die();
}

// Delete the view template assigment
add_action('wp_ajax_wpv_remove_content_template_from_view', 'wpv_remove_content_template_from_view_callback');
function wpv_remove_content_template_from_view_callback() {

    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'wpv-ct-inline-edit') ) die("Undefined Nonce.");
    $view_id = $_POST['view_id'];
    $id = $_POST['id'];
    ?>

   <div class="wpv-dialog js-wpv-dialog-remove-content-template-from-view">
            <form method="" id="wpv-remove-content-template-from-view-form">
            <div class="wpv-dialog-header">
                <h2><?php _e('Remove the Content Template from the view','wpv-views') ?></h2>
                <i class="icon-remove js-dialog-close"></i>
            </div>
            <div class="wpv-dialog-content">
                <p>
                    <?php _e("This will remove the link between your view and the Content Template.  The Content Template will not be deleted.") ?>
                </p>
            </div>
            <div class="wpv-dialog-footer">
            	<p class="dont-show-again">
            		<input type="checkbox" id="dont-show-again" />
            		<label for="dont-show-again"><?php _e("Don't show this message again",'wpv-views') ?></label>
            	</p>
                <button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
                <button class="button button-primary js-remove-template-from-view" data-id="<?php echo $id; ?>" data-viewid="<?php echo $view_id; ?>"><?php _e('Remove','wpv-views') ?></button>
            </div>
            </form>
     </div>

   <?php

    die();
}
add_action('wp_ajax_wpv_remove_content_template_from_view_process', 'wpv_remove_content_template_from_view_process_callback');
function wpv_remove_content_template_from_view_process_callback() {

    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'wpv-ct-inline-edit') ) die("Undefined Nonce.");
    $view_id = $_POST['view_id'];
    $id = $_POST['id'];

    $meta = get_post_meta( $view_id, '_wpv_layout_settings', true);
    $templates = $exists = '';
    if ( isset($meta['included_ct_ids']) ){
         $templates = str_replace( $id.',', '', $meta['included_ct_ids']);
    }
    if ( $exists == ''){
        $meta['included_ct_ids'] = $templates;
        print update_post_meta($view_id, '_wpv_layout_settings', $meta );
    }

    die();
}


//ct-change-types
add_action('wp_ajax_ct_change_types', 'ct_change_types_callback');
function ct_change_types_callback(){
   if ( !isset($_GET["wpnonce"]) || ! wp_verify_nonce($_GET["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");
   global $wpdb, $WP_Views;
   $options = $WP_Views->get_options();
   $post_types_array = wpv_get_pt_tax_array();
   $post_type_names = array(
    'single_post'=> __( 'Single pages', 'wpv-views' ),
    'taxonomy_post' => __( 'Taxonomy archives', 'wpv-views' ),
    'archive_post' => __( 'Post archives', 'wpv-views' )
   );
   $id = $_GET['id'];
   $show_hidden = 0;
   ?>
    <div class="wpv-dialog js-wpv-dialog-add-new-content-template wpv-dialog-add-new-content-template">
        <form method="" id="wpv-add-new-content-template-form">
        <div class="wpv-dialog-header">
            <h2><?php _e('Change Types','wpv-views') ?></h2>
            <i class="icon-remove js-dialog-close"></i>
        </div>
        <div class="wpv-dialog-content">
            <p><?php _e('What content will this template be for?','wpv-views') ?></p>
            <div>
                <?php
                foreach ($post_types_array as $post_type_name => $post_types) {
                    $open_ul = 0;

                    for ($i=0;$i<count($post_types);$i++){
                        $show_hidden = 0;
                        $type = $post_types[$i][0];
                        $label = $post_types[$i][1];
                        $input_name = 'views_template_for_' . $type;
                        $type_used = false;
                        $type_current = false;
                        if ( $post_type_name == 'single_post' ){
                            if ( !isset($options['views_template_for_' . $type]) ){
                                $options['views_template_for_' . $type] = 0;
                                $WP_Views->save_options( $options );
                            }
                            if ( isset($options['views_template_for_' . $type]) && $options['views_template_for_' . $type] != 0 ){
                                $type_used = true;
                            }
                            if ( isset($options['views_template_for_' . $type]) && $options['views_template_for_' . $type] == $id ){
                                $type_used = false;
                                $type_current = true;
                            }
                            if ( $open_ul == 0 && !isset( $$post_type_name)){
                               for ($j=0;$j<count($post_types);$j++){
                                   $temp_type = $post_types[$j][0];
                                   if ( isset($options['views_template_for_' . $temp_type]) && $options['views_template_for_' . $temp_type] == $id ){
                                       $show_hidden = 1;
                                       $j = count($post_types)+1;
                                   }
                               }
                           }
                        }
                        if ( $post_type_name == 'taxonomy_post' ){
                           if ( !isset($options['views_template_loop_' . $type]) ){
                                $options['views_template_loop_' . $type] = 0;
                                $WP_Views->save_options( $options );
                           }
                           if ( isset($options['views_template_loop_' . $type]) && $options['views_template_loop_' . $type] != 0 ){
                                $type_used = true;
                           }
                           if ( isset($options['views_template_loop_' . $type]) && $options['views_template_loop_' . $type] == $id ){
                                $type_used = false;
                                $type_current = true;
                           }
						   $input_name = 'views_template_loop_' . $type;
                           if ( $open_ul == 0 && !isset( $$post_type_name)){
                               for ($j=0;$j<count($post_types);$j++){
                                   $temp_type = $post_types[$j][0];
                                   if ( isset($options['views_template_loop_' . $temp_type]) && $options['views_template_loop_' . $temp_type] == $id ){
                                       $show_hidden = 1;
                                       $j = count($post_types)+1;
                                   }
                               }
                           }
                        }
                        if ( $post_type_name == 'archive_post' ){
                           if ( !isset($options['views_template_archive_for_' . $type]) ){
                                $options['views_template_archive_for_' . $type] = 0;
                                $WP_Views->save_options( $options );
                           }
                           if ( isset($options['views_template_archive_for_' . $type]) && $options['views_template_archive_for_' . $type] != 0 ){
                                $type_used = true;
                           }
                           if ( isset($options['views_template_archive_for_' . $type]) && $options['views_template_archive_for_' . $type] == $id ){
                                $type_used = false;
                                $type_current = true;
                           }
						   $input_name = 'views_template_archive_for_' . $type;
                           if ( $open_ul == 0 && !isset( $$post_type_name)){
                               for ($j=0;$j<count($post_types);$j++){
                                   $temp_type = $post_types[$j][0];
                                   if ( isset($options['views_template_archive_for_' . $temp_type]) && $options['views_template_archive_for_' . $temp_type] == $id ){
                                       $show_hidden = 1;
                                       $j = count($post_types)+1;
                                   }
                               }
                           }
                        }

                        if ( !isset( $$post_type_name ) ){
                            $$post_type_name = 1;

                            if ( $open_ul == 0){
                                $hide = '';
                                if ( $post_type_name != 'single_post'){ ?>
                                        </ul>
                                    </div> <!-- .wpv-content-template-dropdown-list -->
                                    <?php
                                }
                            }
                            ?>
                            <p>
                                <span class="js-wpv-content-template-open wpv-content-template-open" title="<?php echo htmlentities( __( "Click to toggle", 'wpv-views' ), ENT_QUOTES ); ?>">
                                    <?php echo $post_type_names[$post_type_name]; ?>:
                                    <i class="icon-caret-down"></i>
                                </span>
                            </p>
                            <?php
                            if ( $open_ul == 0){
                                $hide = '';
                                if ( $show_hidden == 0){
                                     $hide = 'hidden';
                                } ?>
                                <div class="js-wpv-content-template-dropdown-list wpv-content-template-dropdown-list <?php echo $hide ?>">
                                    <ul>
                                <?php
                                $open_ul = 1;
                            }


                        }

                        ?>
                        <li>
                            <label<?php echo !$type_used?' class="wpv-ct-post-type-used"':'';?>>
                                <input type="checkbox" name="wpv-new-content-template-post-type[]"
                                <?php echo $type_current? ' checked="checked"' : '';?>
                                data-title="<?php echo $label; ?>" value="<?php echo $input_name; ?>" />
                                <?php echo $label; ?>
                            </label>
                        </li>
                    <?php
                    }
                } ?>
                    </ul>
                </div> <!-- .wpv-content-template-dropdown-list -->
            </div>

        </div>
        <div class="wpv-dialog-footer">
            <button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
            <button class="button button-primary js-ct-change-type-process" data-id="<?php echo $id; ?>"><?php _e('Change','wpv-views') ?></button>
        </div>
        </form>
         </div>
    <?php
     die();
}

//Change Content template
add_action('wp_ajax_ct_change_types_process', 'ct_change_types_process_callback');
function ct_change_types_process_callback(){

    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");

    global $wpdb, $WP_Views;
    $options = $WP_Views->get_options();
        $id = $_POST["view_template_id"];
        if ( isset($_POST['type']) ){
            $type = $_POST['type'];
        }else{
            $type = array();
        }
        foreach ($options as $key => $value) {
             if ($value == $id){
                $options[$key] = 0;
             }
        }
        for ($i=0;$i<count($type);$i++){
                 $options[$type[$i]] = $id;
        }
        $WP_Views->save_options( $options );

        echo 'ok';

    die();
}
//Change Content template
add_action('wp_ajax_ct_reload_pages', 'ct_reload_pages_callback');
function ct_reload_pages_callback(){

    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");

        $query = '';
        $page = 1;
        if ( isset($_POST['query']) ){
            $query = $_POST['query'];
        }
        if ( isset($_POST['page']) ){
            $page = $_POST['page'];
        }
        $out = wpv_admin_menu_views_pager('0',$page,'ct', $query);
        echo $out;
    die();
}

//Search hook for Archive, Views, Content Template listings
function wpv_listing_custom_search( $description_field, $args ){
    $query_s = addslashes($_POST['query']);
    $new_args = $args;
    $args['posts_per_page'] = '-1';
    $args['s'] = $query_s;
    $query = new WP_Query( $args );
    $unique_ids = array();
    while ($query->have_posts()) :
        $query->the_post();
        $unique_ids[] = get_the_id();
    endwhile;
    unset($args['s']);
    $args['meta_query'] =array(
       array(
          'key' => $description_field,
          'value' => $query_s,
          'compare' => 'LIKE'
        )
    );
    $query2 = new WP_Query( $args );
    while ($query2->have_posts()) :
        $query2->the_post();
        $unique_ids[] = get_the_id();
    endwhile;
    unset($args['meta_query']);
    
    $unique = array_unique($unique_ids);
    if ( count($unique) == 0 ){
        $new_args['post__in'] = array('-1');
    }else{
        $new_args['post__in'] = $unique;
    }
    $query = new WP_Query( $new_args );
    return $query;
}


//Content template sorting
add_action('wp_ajax_ct_change_view', 'ct_change_view_callback');
function ct_change_view_callback(){

     if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");

     $sort = $_POST['view'];
     $search_query = '';


     $page = 0;
     $args = array( 'post_type' => 'view-template' , 'posts_per_page' => WPV_ITEMS_PER_PAGE, 'orderby' => 'title', 'order' => 'ASC');
     if (isset($_POST["orderby"]) && $_POST["orderby"] === 'date') {
		$args['orderby'] = 'date';
		$args['order'] = 'DESC';
	}
     if ( isset ($_POST['page']) ){
         $args['paged'] = $_POST['page'];
     }
     if ( isset($_POST['query']) && !empty($_POST['query']) ){
         $query = wpv_listing_custom_search('_wpv-content-template-decription', $args);
     }
     else{
        $query = new WP_Query( $args );
     }


     if ( $sort == 'name' ){
        //$query = new WP_Query( $args );
        if ($query->found_posts == 0){
            echo '<tr class="js-wpv-ct-list-row"><td colspan="3">'.__('No Content Templates matched your criteria.','wpv-views').'</td></tr>';
        }
        else{
            while ($query->have_posts()) :
                $query->the_post();
                echo wpv_admin_menu_content_template_listing_row( get_the_id() );
            endwhile;
        }
     }
     else{
        echo wpv_admin_menu_content_template_listing_by_type_row( $sort, $page );
     }
    die();
}

//Content template sorting
add_action('wp_ajax_ct_change_types_pt', 'ct_change_types_pt_callback');
function ct_change_types_pt_callback(){
    if ( !isset($_GET["wpnonce"]) || ! wp_verify_nonce($_GET["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");
    global $wpdb, $WP_Views;
    $query = new WP_Query('post_type=view-template&posts_per_page=-1');
    $sort = $_GET['sort'];
    $post_type = $_GET['pt'];
    $no_type = __('Don’t use any Content Template for this Post Type','wpv-views');
    $head_text = __('Change Post Type','wpv-views');
    if ( isset($_GET['msg']) && $_GET['msg'] == '2'){
        $no_type = __('Don’t use any Content Template for this Taxonomy','wpv-views');
        $head_text = __('Change Taxonomy','wpv-views');
    }
    $options = $WP_Views->get_options();
    ?>
    <div class="wpv-dialog js-wpv-dialog-add-new-content-template wpv-dialog-add-new-content-template">
        <form method="" id="wpv-add-new-content-template-form">
        <div class="wpv-dialog-header">
            <h2><?php echo $head_text ?></h2>
            <i class="icon-remove js-dialog-close"></i>
        </div>
        <div class="wpv-dialog-content">
            <div><?php // echo '<pre>';print_r($query);echo '</pre>'; ?>
                <ul>
                <li><label>
                    <input type="radio" name="wpv-new-post-type-content-template" value="0" />
                     <?php echo $no_type; ?>
                     </label>
                </li>
                <?php
                while ($query->have_posts()) :

                    $query->the_post();
                    $id = get_the_id();
                    $current = '';
                    if ( isset($options[$post_type]) && $id == $options[$post_type] ){
                        $current = ' checked="checked"';
                    }
                   ?>
                     <li>
                            <label>
                                <input type="radio" name="wpv-new-post-type-content-template" <?php echo $current;?> value="<?php echo $id;?>" />
                                <?php the_title();?>
                            </label>
                     </li>
                    <?php

                endwhile; ?>
                    </ul>
           </div>

        </div>
        <div class="wpv-dialog-footer">
            <button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
            <button class="button button-primary js-ct-change-types-pt-process" data-pt="<?php echo $post_type?>" data-sort="<?php echo $sort?>"><?php _e('Change','wpv-views') ?></button>
        </div>
        </div>
    <?php
    die();
}



//Change Content template
add_action('wp_ajax_ct_change_types_pt_process', 'ct_change_types_pt_process_callback');
function ct_change_types_pt_process_callback(){

    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");
    global $wpdb, $WP_Views;
        $options = $WP_Views->get_options();
        $pt = $_POST["pt"];
        $sort = $_POST['sort'];
        if ( isset($_POST['value']) ){
            $value = $_POST['value'];
        }
        else{
            $value = 0;
        }
        $options[$pt] = $value;

        $WP_Views->save_options( $options );
        $out = wpv_admin_menu_content_template_listing_by_type_row( $sort );
        echo $out;

    die();
}

//Assign new Content template to view
add_action('wp_ajax_wpv_add_view_template', 'wpv_add_view_template_callback');
function wpv_add_view_template_callback() {
    global $wpdb;
    //add new content template
    if ( !isset($_POST["wpnonce"]) || !wp_verify_nonce($_POST["wpnonce"], 'wpv-ct-inline-edit') ) die("Undefined Nonce.");
    if ( isset($_POST['template_name']) ){
        $postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . mysql_real_escape_string($_POST['template_name']) . "' AND post_type='view-template'" );
        if ( !empty($postid)  ){
               echo 'error_name'; die();
        }
        $new_template = array(
          'post_title'    => $_POST['template_name'],
          'post_type'      => 'view-template',
          'post_content'  => '',
          'post_status'   => 'publish',
          'post_author'   => 1,// TODO check why author here
       //   'post_name' => sanitize_title( $_POST['template_name'] )
        );
        $ct_post_id = wp_insert_post( $new_template );
        update_post_meta( $ct_post_id, '_wpv_view_template_mode', 'raw_mode');
        update_post_meta( $ct_post_id, '_wpv-content-template-decription', '');
    }
    else{
       $ct_post_id = $_POST['template_id'];
    }

    $post = get_post($ct_post_id);

    if ( !is_object($post) ){
        echo 'error'; die();
    }
    $meta = get_post_meta( $_POST['view_id'], '_wpv_layout_settings', true);
    $templates = $exists = '';
    if ( isset($meta['included_ct_ids']) ){
        $reg = '/'.$ct_post_id.'\,/';
        if ( preg_match($reg, $meta['included_ct_ids']) ){
            $exists = 1;
        }
        $templates = str_replace( $ct_post_id.',', '', $meta['included_ct_ids']);
    }
    if ( $exists == ''){
        $meta['included_ct_ids'] = $templates.$ct_post_id.',';
        update_post_meta($_POST['view_id'], '_wpv_layout_settings', $meta );
        echo wpv_list_view_ct_item($post, $ct_post_id, $_POST['view_id']);
    }
    else {
        echo '1';
    }

    die();
}

//Update Content Template (inline)
add_action('wp_ajax_wpv_ct_update_inline', 'wpv_ct_update_inline_callback');
function wpv_ct_update_inline_callback() {
    //add new content template
    if ( !isset($_POST["wpnonce"]) || !wp_verify_nonce($_POST["wpnonce"], 'wpv-ct-inline-edit') ) die("Undefined Nonce.");
    $my_post = array();
    $my_post['ID'] = $_POST['ct_id'];
    $my_post['post_content'] = $_POST['ct_value'];
    print wp_update_post( $my_post );
    die();
}

//Info message when CT assigned to all post types for listing page
add_action('wp_ajax_set_view_template_listing', 'set_view_template_listing_callback');
function set_view_template_listing_callback() {
    //add new content template
    if ( !isset($_POST["wpnonce"]) || ! wp_verify_nonce($_POST["wpnonce"], 'work_view_template') ) die("Undefined Nonce.");
    global $wpdb;
    $view_template_id = $_POST['view_template_id'];
    $type = $_POST['type'];
    //list($join, $cond) = WPV_template::_get_wpml_sql( $type, $_POST['lang'] );
    $posts = $wpdb->get_col( "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts}  WHERE post_type='{$type}'" );

    $count = sizeof( $posts );
    $updated_count = 0;
    if ( $count > 0 ) {
      foreach ( $posts as $post ) {
          $template_selected = get_post_meta( $post,
                            '_views_template', true );
          if ( $template_selected != $view_template_id ) {
              update_post_meta( $post, '_views_template',$view_template_id );
              $updated_count += 1;
          }
       }
    }
    echo '<div class="wpv-dialog wpv-dialog-change js-wpv-dialog-change">
                <div class="wpv-dialog-header">
                    <h2>Templates updated</h2>
                </div>
                <div class="wpv-dialog-content">
                    <p>'. sprintf(__('All %ss were successfully updated', 'wpv-views'), $type) .'</p>
                </div>
                <div class="wpv-dialog-footer">
                    <button class="button-secondary js-dialog-close">'. esc_js( __('Close','wpv-views') ) .'</button>
                </div>
            </div>';
    die();
}

//Load Content Template editor
add_action('wp_ajax_wpv_ct_loader_inline', 'wpv_ct_loader_inline_callback');
function wpv_ct_loader_inline_callback() {
    //add new content template
    if ( !isset($_POST["wpnonce"]) || !wp_verify_nonce($_POST["wpnonce"], 'wpv-ct-inline-edit') ) die("Undefined Nonce.");
    do_action('views_ct_inline_editor');
    $post = get_post($_POST['id']);
    define("CT_INLINE", "1");
    $out = '';
    if ( is_object($post) && isset($post->ID) ){
        ob_start();
        $ct_id = $post->ID;


    ?>

       	<div class="code-editor-toolbar js-code-editor-toolbar">
	       <ul class="js-wpv-v-icon js-wpv-v-icon-<?php echo $ct_id; ?>">
	            <?php wpv_add_v_icon_to_codemirror( 'wpv-ct-inline-editor-'.$ct_id, true ); ?>
				<li>
					<button class="button-secondary js-code-editor-toolbar-button js-wpv-media-manager" data-id="<?php echo $ct_id; ?>" data-content="<?php echo 'wpv-ct-inline-editor-'.$ct_id; ?>">
						<i class="icon-picture"></i>
						<span class="button-label"><?php _e('Media','wpv-views'); ?></span>
					</button>
				</li>
				<li>
				  <?php wpv_add_cred_to_codemirror( 'wpv-ct-inline-editor-'.$ct_id ); ?>
				</li>
	       </ul>
      	</div>
		<textarea name="name" rows="10" id="wpv-ct-inline-editor-<?php echo $ct_id; ?>"><?php echo $post->post_content;?></textarea></p>
		<p class="update-button-wrap">
		   <button class="button js-wpv-ct-update-inline js-wpv-ct-update-inline-<?php echo $ct_id; ?>" data-unsaved="<?php echo htmlentities( __('Not saved', 'wpv-views'), ENT_QUOTES ); ?>" data-id="<?php echo $ct_id; ?>"><?php _e('Update','wpv-views'); ?></button>
		</p>

    <?php
    $out = ob_get_contents();
    ob_end_clean();
    print $out;
    }else{
       print 'error';
    }

    die();
}

//Update Content Template (inline)
add_action('wp_ajax_close_ct_help_box', 'close_ct_help_box_callback');
function close_ct_help_box_callback() {
    //add new content template
    if ( !isset($_POST["wpnonce"]) || !wp_verify_nonce($_POST["wpnonce"], 'set_view_template') ) die("Undefined Nonce.");
    $close = 1;
    if ( isset($_POST['close_this']) ){
        $close = $_POST['close_this'];
    }
    update_option('wpv_content_template_show_help',$close);
    die();
}

