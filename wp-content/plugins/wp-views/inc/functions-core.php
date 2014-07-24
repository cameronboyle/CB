<?php

define ('WPV_ITEMS_PER_PAGE', 20); // TODO move to constants.php, maybe use WPV_DEFAULT_LIST_ITEMS instead

/**
* Update messages for regular edit screens
*
* @param $messages
* @return $messages
*/

add_filter('post_updated_messages', 'wpv_post_updated_messages_filter', 9999);

function wpv_post_updated_messages_filter( $messages ) {
	global $post;

	$post_type = get_post_type();
	if ( $post_type == 'view' ) {
		$messages['view'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __('View updated.', 'wpv-views'),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('View updated.', 'wpv-views'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('View restored to revision from %s', 'wpv-views'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __('View published.', 'wpv-views'),
			7 => __('View saved.', 'wpv-views'),
			8 => __('View submitted.', 'wpv-views'),
			9 => sprintf( __('View scheduled for: <strong>%1$s</strong>.', 'wpv-views'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
			10 => __('View draft updated', 'wpv-views'),
			);
	}
	if ( $post_type == 'view-template' ) {
		$messages['view-template'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __('Content template updated.', 'wpv-views'),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('Content template updated.', 'wpv-views'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('Content template restored to revision from %s', 'wpv-views'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __('Content template published.', 'wpv-views'),
			7 => __('Content template saved.', 'wpv-views'),
			8 => __('Content template submitted.', 'wpv-views'),
			9 => sprintf( __('Content template scheduled for: <strong>%1$s</strong>.', 'wpv-views'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
			10 => __('Content template draft updated', 'wpv-views'),
			);
	}
	return $messages;
}

/**
* 
* Function wpv_redirect_admin_listings
*
* Prevents users from accessing the natural listing pages that WordPress creates for Views and Content Templates
* and redirects them to the new listing pages
*
*/

add_action('admin_init', 'wpv_redirect_admin_listings');

function wpv_redirect_admin_listings(){
	global $pagenow;
	/* Check current admin page. */
	if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'view' ) {
		wp_redirect(admin_url('/admin.php?page=views', 'http'), 301);
		exit;
	} elseif ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'view-template' ) {
		wp_redirect(admin_url('/admin.php?page=view-templates', 'http'), 301);
		exit;
	}
}

function wpv_render_checkboxes( $values, $selected, $name ) { // TODO only used in old Status Filter, safe to remove
	$checkboxes = '<ul>';
	foreach ( $values as $value ) {

		if ( in_array( $value, $selected ) ) {
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}
		$checkboxes .= '<li><label><input type="checkbox" name="_wpv_settings[' . $name . '][]" value="' . $value . '"' . $checked . ' />&nbsp;' . $value . '</label></li>';

	}
	$checkboxes .= '</ul>';

	return $checkboxes;
}

function wpv_render_filter_td( $row, $id, $name, $summary_function, $selected, $data ) { // TODO only used in old Status Filter, safe to remove

	$td = '<td><img src="' . WPV_URL . '/res/img/delete.png" onclick="on_delete_wpv_filter(\'' . $row . '\')" style="cursor: pointer" />';
	$td .= '<td class="wpv_td_filter">';
	$td .= "<div id=\"wpv-filter-" . $id . "-show\">\n";
	$td .= call_user_func($summary_function, $selected);
	$td .= "</div>\n";
	$td .= "<div id=\"wpv-filter-" . $id . "-edit\" style='background:" . WPV_EDIT_BACKGROUND . ";display:none'>\n";

	$td .= '<fieldset>';
	$td .= '<legend><strong>' . $name . ':</strong></legend>';
	$td .= '<div>' . $data . '</div>';
	$td .= '</fieldset>';
	ob_start();
	?>
		<input class="button-primary" type="button" value="<?php echo __('OK', 'wpv-views'); ?>" name="<?php echo __('OK', 'wpv-views'); ?>" onclick="wpv_show_filter_<?php echo $id; ?>_edit_ok()"/>
		<input class="button-secondary" type="button" value="<?php echo __('Cancel', 'wpv-views'); ?>" name="<?php echo __('Cancel', 'wpv-views'); ?>" onclick="wpv_show_filter_<?php echo $id; ?>_edit_cancel()"/>
	<?php
	$td .= ob_get_clean();
	$td .= '</div></td>';

	return $td;
}

// TODO move this AJAX calls to wpv-admin-ajax.php

/**
* Disable pagination result hint message
*/

add_action('wp_ajax_wpv_pagination_hint_result_disable', 'wpv_pagination_hint_result_disable_callback');

function wpv_pagination_hint_result_disable_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce( $nonce, 'wpv_view_pagination_hint_result_dismiss_nonce' ) ) die("Security check");
	$user_ID = get_current_user_id();
	$user_help_setting = get_user_meta( $user_ID, 'wpv_view_editor_help_dismiss' );
	if ( isset( $user_help_setting[0]['pagination'] ) && $user_help_setting[0]['pagination'] == 'disable' ) {
		echo true;
	} else {
		$user_help_setting['pagination'] = 'disable';
		$result = update_user_meta( $user_ID, 'wpv_view_editor_help_dismiss', $user_help_setting );
		echo $result;
	}
	die();
}

/**
* Disable parametric search hint message TODO check if this is deprecated
*/

add_action('wp_ajax_wpv_parametric_hint_disable', 'wpv_parametric_hint_disable_callback');

function wpv_parametric_hint_disable_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce( $nonce, 'wpv_view_parametric_hint_dismiss_nonce' ) ) die("Security check");
	$user_ID = get_current_user_id();
	$user_help_setting = get_user_meta( $user_ID, 'wpv_view_editor_help_dismiss' );
	if ( isset( $user_help_setting[0]['parametric_search'] ) && $user_help_setting[0]['parametric_search'] == 'disable' ) {
		echo true;
	} else {
		$user_help_setting['parametric_search'] = 'disable';
		$result = update_user_meta( $user_ID, 'wpv_view_editor_help_dismiss', $user_help_setting );
		echo $result;
	}
	die();
}

/**
* Disable inline Content Template hint message
*/

add_action('wp_ajax_wpv_content_template_hint_disable', 'wpv_content_template_hint_disable_callback');

function wpv_content_template_hint_disable_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce( $nonce, 'wpv_view_content_template_hint_dismiss_nonce' ) ) die("Security check");
	$user_ID = get_current_user_id();
	$user_help_setting = get_user_meta( $user_ID, 'wpv_view_editor_help_dismiss' );
	if ( isset( $user_help_setting[0]['content_template'] ) && $user_help_setting[0]['content_template'] == 'disable' ) {
		echo true;
	} else {
		$user_help_setting['content_template'] = 'disable';
		$result = update_user_meta( $user_ID, 'wpv_view_editor_help_dismiss', $user_help_setting );
		echo $result;
	}
	die();
}

/**
* Disable Layout wizard hint message
*/

add_action('wp_ajax_wpv_layout_wizard_hint_disable', 'wpv_layout_wizard_hint_disable_callback');

function wpv_layout_wizard_hint_disable_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce( $nonce, 'wpv_view_layout_wizard_hint_dismiss_nonce' ) ) die("Security check");
	$user_ID = get_current_user_id();
	$user_help_setting = get_user_meta( $user_ID, 'wpv_view_editor_help_dismiss' );
	if ( isset( $user_help_setting[0]['layout_wizard'] ) && $user_help_setting[0]['layout_wizard'] == 'disable' ) {
		echo true;
	} else {
		$user_help_setting['layout_wizard'] = 'disable';
		$result = update_user_meta( $user_ID, 'wpv_view_editor_help_dismiss', $user_help_setting );
		echo $result;
	}
	die();
}

/**
* Set default View settings and layout settings
*
* @param $settings field: view_settings or view_layout_settings
* @param $purpose purpose of the view: all, pagination, slide, parametric or full
* @return array() with desired values
*/

function wpv_view_defaults( $settings = 'view_settings', $purpose = 'full' ) {
	$defaults = array(
		// set the initial values for the View settings
		// Note: taxonomy_type is set in wpv-section-query-type.php to use the first available taxonomy
		'view_settings' => array(
			'view-query-mode'			=> 'normal',
			'view_description'			=> '',
			'view_purpose'				=> 'full',
			'query_type'				=> array('posts'),
			'taxonomy_type'				=> array('category'),
			'post_type_dont_include_current_page'	=> true,
			'taxonomy_hide_empty'			=> true,
			'taxonomy_include_non_empty_decendants'	=> true,
			'taxonomy_pad_counts'			=> true, // check this setting application
			'orderby'				=> 'post_date',
			'order'					=> 'DESC',
			'taxonomy_orderby'			=> 'name',
			'taxonomy_order'			=> 'DESC',
			'limit'					=> -1,
			'offset'				=> 0,
			'taxonomy_limit'			=> -1,
			'taxonomy_offset'			=> 0,
			'posts_per_page'			=> 10,
			'pagination'				=> array(
								'disable',
								'mode'				=> 'none',
								'preload_images'		=> true,
								'cache_pages'			=> true,
								'preload_pages'			=> true,
								'pre_reach'			=> 1,
								'page_selector_control_type'	=> 'drop_down',
								'spinner'			=> 'default',
								'spinner_image'			=> '',
								'spinner_image_uploaded'	=> '',
								), // this needs carefull review
			'ajax_pagination'			=> array(
								'disable',
								'style'				=> 'fade',
								'duration'			=> 500,
								),
			'rollover'				=> array(
								'preload_images'		=> true,
								'posts_per_page'		=> 1,
								'speed'				=> 5,
								'effect'			=> 'fade',
								'duration'			=> 500,
								),
			'filter_meta_html_state'		=> array(
								'html'				=> 'on',
								'css'				=> 'off',
								'js'				=> 'off',
								'img'				=> 'off',
								),
			'filter_meta_html'			=> "[wpv-filter-start hide=\"false\"]\n[wpv-filter-controls][/wpv-filter-controls]\n[wpv-filter-end]",
			'filter_meta_html_css'			=> '',
			'filter_meta_html_js'			=> '',
			'layout_meta_html_state'		=> array(
								'html'				=> 'on',
								'css'				=> 'off',
								'js'				=> 'off',
								'img'				=> 'off',
								),
			'layout_meta_html_css'			=> '',
			'layout_meta_html_js'			=> '',
		),
		'view_layout_settings' => array( // almost all of this settings are only needed to create the layout on the fly, so they are not needed here
			'additional_js'				=> '',
			'layout_meta_html'			=> "[wpv-layout-start]
	[wpv-items-found]
	<!-- wpv-loop-start -->
		<wpv-loop>
		</wpv-loop>
	<!-- wpv-loop-end -->
	[/wpv-items-found]
	[wpv-no-items-found]
		[wpml-string context=\"wpv-views\"]<strong>No items found</strong>[/wpml-string]
	[/wpv-no-items-found]
[wpv-layout-end]",
		),
	);
	switch( $purpose ) {
		case 'all':
			$defaults['view_settings']['sections-show-hide'] = array(
				'pagination'		=> 'off',
				'filter-extra'		=> 'off',
			);
			$defaults['view_settings']['view_purpose'] = 'all';
			break;
		case 'pagination':
			$defaults['view_settings']['pagination'][0] = 'enable';
			$defaults['view_settings']['pagination']['mode'] = 'paged';
			$defaults['view_settings']['sections-show-hide'] = array(
				'limit-offset'		=> 'off',
			);
			$defaults['view_settings']['view_purpose'] = 'pagination';
			break;
		case 'slider':
			$defaults['view_settings']['pagination'][0] = 'enable';
			$defaults['view_settings']['pagination']['mode'] = 'rollover';
			$defaults['view_settings']['sections-show-hide'] = array(
				'limit-offset'		=> 'off',
			);
			$defaults['view_settings']['view_purpose'] = 'slider';
			break;
		case 'parametric':
			$defaults['view_settings']['sections-show-hide'] = array(
				'pagination'		=> 'off',
			);
			$defaults['view_settings']['view_purpose'] = 'parametric';
			break;
		case 'full':
		default:
			$defaults['view_settings']['sections-show-hide'] = array(
			
			);
			$defaults['view_settings']['view_purpose'] = 'full';
			break;
	}
	return $defaults[$settings];
}

/**
* Set default WordPress Archives settings and layout settings
*
* @param $settings field: view_settings or view_layout_settings
* @return array() with desired values
*/

function wpv_wordpress_archives_defaults( $settings = 'view_settings' ) {
	$defaults = array(
		'view_settings' => array(
			'view-query-mode'			=> 'archive',
			'sections-show-hide'			=> array(
									'content'		=> 'off',
								)
		),
		'view_layout_settings' => array( // almost all of this settings are only needed to create the layout on the fly, so they are not needed here
			'additional_js'				=> '',
			'layout_meta_html'			=> "[wpv-layout-start]
	[wpv-items-found]
	<!-- wpv-loop-start -->
		<wpv-loop>
		</wpv-loop>
	<!-- wpv-loop-end -->
	[/wpv-items-found]
	[wpv-no-items-found]
		[wpml-string context=\"wpv-views\"]<strong>No posts found</strong>[/wpml-string]
	[/wpv-no-items-found]
[wpv-layout-end]",
		),
	);
	return $defaults[$settings];
}

/**
*
* Display pagination in admin listing pages
*
* @param $context the admin page where it will be rendered: 'views', 'view-templates', 'view-archives'
* @param $wpv_found_items (int)
* @param $wpv_items_per_page (int)
* @param $mod_url (array)
* 
*/

function wpv_admin_listing_pagination( $context = 'views', $wpv_found_items, $wpv_items_per_page = WPV_ITEMS_PER_PAGE, $mod_url = array() ) {
	$page = ( isset( $_GET["paged"] ) ) ? (int) $_GET["paged"] : 1;
	$pages_count = ceil( (int) $wpv_found_items / (int) $wpv_items_per_page );
	if ( $pages_count > 1 ) {
		$items_start = ( ( ( $page - 1 ) * (int) $wpv_items_per_page ) + 1 );
		$items_end = ( ( ( $page - 1 ) * (int) $wpv_items_per_page ) + (int) $wpv_items_per_page );
		if ( $page == $pages_count ) {
			$items_end = $wpv_found_items;
		}
		$mod_url_defaults = array(
			'orderby' => '',
			'order' => '',
			'search' => '',
			'items_per_page' => ''
		);
		$mod_url = wp_parse_args($mod_url, $mod_url_defaults);
		?>
		<div class="wpv-listing-pagination tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num">
					<?php _e('Displaying ', 'wpv-views'); echo $items_start; ?> - <?php echo $items_end; _e(' of ', 'wpv-views'); echo $wpv_found_items; ?>
				</span>
				<?php if ( $page > 1 ) { ?>
					<a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $context . $mod_url['orderby'] . $mod_url['order'] . $mod_url['search'] . $mod_url['items_per_page']; ?>&amp;paged=<?php echo $page - 1; ?>" class="wpv-filter-navigation-link">&laquo; <?php echo __('Previous page','wpv-views'); ?></a>
				<?php } ?>
				<?php
				for ( $i = 1; $i <= $pages_count; $i++ ) {
					$active = 'wpv-filter-navigation-link-inactive';
					if ( $page == $i ) $active = 'js-active active current'; ?>
					<a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $context . $mod_url['orderby'] . $mod_url['order'] . $mod_url['search'] . $mod_url['items_per_page']; ?>&amp;paged=<?php echo $i; ?>" class="<?php echo $active; ?>"><?php echo $i; ?></a>
				<?php } ?>
				<?php if ( $page < $pages_count ) { ?>
					<a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $context . $mod_url['orderby'] . $mod_url['order'] . $mod_url['search'] . $mod_url['items_per_page']; ?>&amp;paged=<?php echo $page + 1; ?>" class="wpv-filter-navigation-link"><?php echo __('Next page','wpv-views'); ?> &raquo;</a>
				<?php } ?>
				<?php _e('Items per page', 'wpv-views'); ?>
				<select class="js-items-per-page">
					<option value="10"<?php if ( $wpv_items_per_page == '10' ) echo ' selected="selected"'; ?>>10</value>
					<option value="20"<?php if ( $wpv_items_per_page == '20' ) echo ' selected="selected"'; ?>>20</value>
					<option value="50"<?php if ( $wpv_items_per_page == '50' ) echo ' selected="selected"'; ?>>50</value>
				</select>
				<a href="#" class="js-wpv-display-all-items"><?php _e('Display all items', 'wpv-views'); ?></a>
			</div><!-- .tablenav-pages -->
		</div><!-- .wpv-listing-pagination -->
	<?php } else if ( ( WPV_ITEMS_PER_PAGE != $wpv_items_per_page ) && ( $wpv_found_items > WPV_ITEMS_PER_PAGE ) ) { ?>
		<div class="wpv-listing-pagination tablenav">
			<div class="tablenav-pages">
				<a href="#" class="js-wpv-display-default-items"><?php _e('Display 20 items per page', 'wpv-views'); ?></a>
			</div><!-- .tablenav-pages -->
		</div><!-- .wpv-listing-pagination -->
	<?php }
}

// NOT needed for Views anymore

function _wpv_get_all_views($view_query_mode) {
	global $wpdb, $WP_Views;
	
	$q = ('
        SELECT ID, post_title FROM ' . $wpdb->prefix . 'posts
        WHERE
            post_status="publish"
        AND
            post_type="view"
    ');
	
	$all_views = $wpdb->get_results( $q);
	foreach($all_views as $key => $view) {
		$settings = $WP_Views->get_view_settings($view->ID);
		if($settings['view-query-mode'] != $view_query_mode) {
			unset($all_views[$key]);
		}
	}
	
	return $all_views;

}

function _wpv_get_all_view_ids( $view_query_mode ) {
	global $wpdb, $WP_Views;
	$q = ( 'SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE post_status="publish" AND post_type="view"' );
	$all_views = $wpdb->get_results( $q );
	$view_ids = array();
	foreach ( $all_views as $key => $view ) {
		$settings = $WP_Views->get_view_settings( $view->ID );
		if( $settings['view-query-mode'] != $view_query_mode ) {
			unset( $all_views[$key] );
		} else {
			$view_ids[] = $view->ID;
		}
	}
	return $view_ids;
}

function _wpv_field_views_by_search($all_views, $search_term) {
/*
	if ( !empty( $search_term ) ) {
		foreach($all_views as $key => $view) {
			// check the search
			$description = get_post_meta($view->ID, '_wpv_description', true);
			if (strpos($description, $search_term) === FALSE && strpos($view->post_title, $search_term) === FALSE) {
				unset($all_views[$key]);
			}
		}
	}
*/
	foreach($all_views as $key => $view) {
		$all_views[$key] = $view->ID;
	}

	$all_views = implode(',', $all_views);
		
	return $all_views;
}

/**
* Check the existence of a kind of View NOT needed for Views anymore
*
* @param $query_mode kind of View object: normal or archive
* @return boolean
*/

function wpv_check_items_exists( $query_mode ) {
	$all_views = _wpv_get_all_views($query_mode);
	
    return count( $all_views ) != 0;
}

/**
* Check the existence of a kind of View (normal or archive)
*
* @param $query_mode kind of View object: normal or archive
* @return array() of relevant Views if they exists or false if not
*/

function wpv_check_views_exists( $query_mode ) {
	$all_views_ids = _wpv_get_all_view_ids($query_mode);
	if ( count( $all_views_ids ) != 0 ) {
		return $all_views_ids;
	} else {
		return false;
	}
}

/**
* Creates the query for listing Views and WordPress Archives
*
* NOT needed for Views anymore
* NOT needed for WPA by name anymore
*
* @param $query_mode kind of View object: normal or archive
* @param $page number of page being displayed
* @param $search_term search term if exists
* @return elements rows
*/

function wpv_admin_menu_views_listing( $view_query_mode, $page = 1, $search_term = '', $order = '' ) {
    global $wpdb, $WP_Views;
    $content = '';
	
    $view_query_mode = esc_sql( $view_query_mode );
    if ( !empty( $search_term ) ) {
        $search_term = esc_sql( $search_term );
    }

	$all_views = _wpv_get_all_views($view_query_mode);
	$all_views = _wpv_field_views_by_search($all_views, $search_term);
	
	$wpv_args = array(
		'post_type' => 'view',
		'post__in' => explode(',', $all_views),
		'posts_per_page' => WPV_ITEMS_PER_PAGE,
		'paged' => $page,
		'order' => 'ASC',
		'orderby' => 'title'
	);
	
	if ( $order === 'date' ) {
		$wpv_args['orderby'] = 'date';
		$wpv_args['order'] = 'DESC';
	}
	
	if ( $view_query_mode == 'normal' ) {
		$wpv_args['posts_per_page'] = WPV_ITEMS_PER_PAGE;
	} else {
		$wpv_args['posts_per_page'] = '-1';
	}
	
	$new_args = $wpv_args;
	$wpv_args['posts_per_page'] = '-1';
	$wpv_args['s'] = $search_term;
	$query = new WP_Query( $wpv_args );
	$unique_ids = array();
	while ($query->have_posts()) :
		$query->the_post();
		$unique_ids[] = get_the_id();
	endwhile;
	unset($wpv_args['s']);
	$wpv_args['meta_query'] =array(
	array(
		'key' => '_wpv_description',
		'value' => $search_term,
		'compare' => 'LIKE'
		)
	);
	$query2 = new WP_Query( $wpv_args );
	while ($query2->have_posts()) :
		$query2->the_post();
		$unique_ids[] = get_the_id();
	endwhile;
	unset($wpv_args['meta_query']);
	
	$unique = array_unique($unique_ids);
	if ( count($unique) == 0 ){
		$new_args['post__in'] = array('-1');
	}else{
		$new_args['post__in'] = $unique;
	}
	
	$wpv_query = new WP_Query( $new_args );
	
	// $wpv_query = new WP_Query( $wpv_args );
	$wpv_count_posts = $wpv_query->post_count;
	
	if ($wpv_count_posts == 0) {
		$content = '<tr class="js-wpv-view-list-row"><td colspan=3>'.__('No Views matched your criteria.','wpv-views').'</td></tr>';
	} else {
		while ($wpv_query->have_posts()) :
			$wpv_query->the_post();
			$wpv_id = get_the_id();
			switch ( $view_query_mode ) {
				case 'normal': $content .= wpv_admin_menu_views_listing_row( $wpv_id ); break;
				case 'archive': $content .= wpv_admin_menu_archive_listing_row( $wpv_id ); break;
			}
		endwhile;
	}
	
    return $content;
}

/**
* Creates the pagination for listing Views and WordPress Archives NOTE only used in CT now
*
* @param $query_mode kind of View object: normal or archive
* @param $page number of page being displayed
* @param $section kind of object this pagination is being applyed to: view or ct
* @param $search_term search term if exists
* @return elements rows
*/

function wpv_admin_menu_views_pager( $view_query_mode, $page = 1, $section = 'views', $search_term = '' ) {
    global $wpdb;
    $view_query_mode = esc_sql( $view_query_mode );
    $search_term = esc_sql( $search_term );
    $page = (int) $page;
    $query_add = $query_add2 = $search_addon = '';
    if ( $section == 'views' ) { // Query for views listing pagination
		
		$all_views = _wpv_get_all_views($view_query_mode);
		$all_views = _wpv_field_views_by_search($all_views, $search_term);
		
		$wpv_args = array(
			'post_type' => 'view',
			'post__in' => explode(',', $all_views),
			'posts_per_page' => -1,
			'paged' => $page
		);
		
		$new_args = $wpv_args;
		$wpv_args['posts_per_page'] = '-1';
		$wpv_args['s'] = $search_term;
		$query = new WP_Query( $wpv_args );
		$unique_ids = array();
		while ($query->have_posts()) :
			$query->the_post();
			$unique_ids[] = get_the_id();
		endwhile;
		unset($wpv_args['s']);
		$wpv_args['meta_query'] =array(
		array(
			'key' => '_wpv_description',
			'value' => $search_term,
			'compare' => 'LIKE'
			)
		);
		$query2 = new WP_Query( $wpv_args );
		while ($query2->have_posts()) :
			$query2->the_post();
			$unique_ids[] = get_the_id();
		endwhile;
		unset($wpv_args['meta_query']);
		
		$unique = array_unique($unique_ids);
		if ( count($unique) == 0 ){
			$new_args['post__in'] = array('-1');
		}else{
			$new_args['post__in'] = $unique;
		}
		
		$wpv_query = new WP_Query( $new_args );
		
		// $wpv_query = new WP_Query( $wpv_args );
		$wpv_count_posts = $wpv_query->post_count;
    }
    elseif ( $section == 'ct' ) {
        $join = $cond = '';
        if ( defined('ICL_LANGUAGE_CODE') ){    
            list($join, $cond) = WPV_template::_get_wpml_sql( 'view-template', ICL_LANGUAGE_CODE );
        }    
       //print_r($);
        //$posts = $wpdb->get_col( "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} {$join} WHERE post_type='{$type}' {$cond}" );
        if ( !empty( $search_term ) ){
            $query_add .= ' AND ( `' . $wpdb->prefix . 'posts`.post_title like "%' . $search_term . '%"';
            $query_add .= ' OR `' . $wpdb->prefix . 'posts`.ID IN (select wpmeta.post_id from `' . $wpdb->prefix . 'postmeta` wpmeta where `' . $wpdb->prefix . 'posts`.ID = wpmeta.post_id AND wpmeta.meta_key="_wpv-content-template-decription" AND wpmeta.meta_value LIKE "%' . $search_term . '%" )';
            $query_add .= ') ';
        }
        $q = ('
        SELECT DISTINCT count(`' . $wpdb->prefix . 'posts`.ID) as views_count FROM `' . $wpdb->prefix . 'posts` '.$join.'
        WHERE `' . $wpdb->prefix . 'posts`.post_status="publish" AND `' . $wpdb->prefix . 'posts`.post_type="view-template" 
        ' . $query_add . $cond );
    }
    if ( isset( $q ) ) {
        $views_count = $wpdb->get_var( $q );
    } else {
        $views_count = 0;
        if ( isset ( $wpv_count_posts ) ) $views_count = $wpv_count_posts;
    }
    $content = '';
    
    if ( $views_count > 0 ) {
        $pages_count = ceil( $views_count / WPV_ITEMS_PER_PAGE );
        if ( $pages_count > 1 ){
        $content .= '<p>' . __('Go to page:','wpv-views') . '</p>';
        $content .= '<ul>';
        $content .= '<li>
                <a href="#" class="js-wpv-listing-pagination-nav wpv-filter-navigation-link js-wpv-listing-pagination-nav-prev hidden">&laquo; ' . __('Previous page','wpv-views') . '</a>
        </li>';

        for ( $i = 1; $i <= $pages_count; $i++ ) {
            $active = 'wpv-filter-navigation-link-inactive';
            if ( $page == $i )
                 $active = 'js-active active';
            $content .= '<li><a href="#" class="js-wpv-listing-pagination-nav ' . $active . '" data-page-num="' . $i . '" >' . $i . '</a></li>';
        }

        $content .= '<li>
                <a href="#" class="js-wpv-listing-pagination-nav wpv-filter-navigation-link js-wpv-listing-pagination-nav-next"> ' . __('Next page','wpv-views') . ' &raquo;</a>
        </li>';

        $content .= '</ul>';
        }
        //return $pages_count;
    }

    return $content;
}

/**
* Cleans the WordPress Media popup to be used in Views and WordPress Archives
*
* @param $strings elements to be included
* @return $strings without the unwanted sections
*/

add_filter( 'media_view_strings', 'custom_media_uploader' );

function custom_media_uploader( $strings ) {
	if ( isset( $_GET['page'] ) && ( 'view-archives-editor' == $_GET['page'] || 'views-editor' == $_GET['page'] ) ) {
		unset( $strings['createGalleryTitle'] ); //Create Gallery
	}
	return $strings;
}

/**
* Add View button to codemirror editor
*
* @param $editor_id ID for the relevant textarea, to be set as active editor
* @param $inline TODO document this
* @return $strings without the unwanted sections
*/

function wpv_add_v_icon_to_codemirror( $editor_id, $inline = false ) {
    
    global $WP_Views;
    $view = '';
    if ( isset($_GET['view_id']) ){
        $view = $_GET['view_id'];
    }
    $is_taxonomy = false;
    $post_hidden = '';
    $tax_hidden = ' hidden';
    $meta = get_post_meta( $view, '_wpv_settings', true);
    
    if ( isset($meta['query_type']) && $meta['query_type'][0] == 'taxonomy'){
           $is_taxonomy = true;
           $post_hidden = ' hidden';
           $tax_hidden = '';
    }
    
    
    
    $WP_Views->editor_addon = new Editor_addon('wpv-views', 
            __('Insert Views Shortcodes', 'wpv-views'), 
            WPV_URL . '/res/js/views_editor_plugin.js', 
            WPV_URL . '/res/img/bw_icon16.png');

    if ( !$inline ){ echo '<div class="wpv-vicon-for-posts'. $post_hidden .'">';}
    
    add_short_codes_to_js( array('post', 'taxonomy', 'post-view', 'view-form'), $WP_Views->editor_addon );
    $WP_Views->editor_addon->add_form_button('', $editor_id , true, true, true);
    if ( !$inline ){echo '</div>';  }
    
    if ( !$inline ){
        echo '<div class="wpv-vicon-for-taxonomy'. $tax_hidden .'">';
        remove_filter('editor_addon_menus_wpv-views', 'wpv_post_taxonomies_editor_addon_menus_wpv_views_filter', 11);
        add_filter('editor_addon_menus_wpv-views', 'wpv_layout_taxonomy_V');
    
        $WP_Views->editor_addon->add_form_button('', $editor_id, true, true, true);
                            
        remove_filter('editor_addon_menus_wpv-views', 'wpv_layout_taxonomy_V');
        add_filter('editor_addon_menus_wpv-views', 'wpv_post_taxonomies_editor_addon_menus_wpv_views_filter', 11);
        echo '</div>';
    }
}

/**
* Add CRED button to codemirror editor
*
* @param $editor_id ID for the relevant textarea, to be set as active editor
* @return $strings without the unwanted sections
*/

function wpv_add_cred_to_codemirror( $editor_id ){
      echo apply_filters('wpv_meta_html_add_form_button', '', '#'.$editor_id);
}

//Update defaults and create new CT for slider view
function wpv_create_new_ct_for_slider_view($id, $view_title){
         global $wpdb;
     $i = 0; $add = '';
     
     while ( $i == 0 ){
         
        $ct_name = $view_title.' - slide'.$add;
        $total = $wpdb->get_var( $wpdb->prepare(
        'SELECT count(ID) FROM ' . $wpdb->posts . ' WHERE post_title = %s AND post_type=\'view-template\'',
        $ct_name
        ));
       
        $add++;
        if ( $total <= 0){
            $i=1;
        }
     }    
    
     $new_template = array(
          'post_title'    => $ct_name,
          'post_type'      => 'view-template',
          'post_content'  => '[wpv-post-link]',
          'post_status'   => 'publish',
          'post_author'   => 1
     );

     $post_id = wp_insert_post( $new_template );
     update_post_meta( $post_id, '_wpv_view_template_mode', 'raw_mode');
     update_post_meta( $post_id, '_wpv-content-template-decription', '');
     return $post_id;
}
