<?php

if(is_admin()){
	
	/**
	* Add the post relationship filter to the list and to the popup select
	*/
	
	add_filter('wpv_filters_add_filter', 'wpv_filters_add_filter_relationship', 1, 2);
	add_action('wpv_add_filter_list_item', 'wpv_add_filter_post_relationship_list_item', 1, 1);

	function wpv_filters_add_filter_relationship($filters, $post_type) {
		if (function_exists('wpcf_pr_get_belongs')) {
			$filters['post_relationship'] = array('name' => __('Post relationship - Post is a child of', 'wpv-views'),
							'present' => 'post_relationship_mode',
							'callback' => 'wpv_add_new_filter_post_relationship_list_item',
							'args' => $post_type
							);

		}
		return $filters;
	}
	
	/**
	* Create post relationship filter callback
	*/

	function wpv_add_new_filter_post_relationship_list_item($post_type) {
		if (function_exists('wpcf_pr_get_belongs')) {
			$args = array(
				'post_relationship_mode' => array('current_page'),
				'post_type'=> $post_type
			);
			wpv_add_filter_post_relationship_list_item($args);
		}
	}
	
	/**
	* Render post relationship filter item in the filters list
	*/

	function wpv_add_filter_post_relationship_list_item($view_settings) {
		if (function_exists('wpcf_pr_get_belongs')) {
			if (isset($view_settings['post_relationship_mode'][0])) {
				$li = wpv_get_list_item_ui_post_post_relationship(null, $view_settings);
				echo '<li id="js-row-post_relationship" class="js-filter-row js-filter-row-simple js-filter-for-posts js-filter-post-relationship js-filter-row-post_relationship">' . $li . '</li>';
			}
		}
	}
	
	/**
	* Render post relationship filter item content in the filters list
	*/

	function wpv_get_list_item_ui_post_post_relationship($selected, $view_settings = null) {

		if (isset($view_settings['post_relationship_mode']) && is_array($view_settings['post_relationship_mode'])) {
			$view_settings['post_relationship_mode'] = $view_settings['post_relationship_mode'][0];
		}

		ob_start();
		wpv_render_post_relationship(array('mode' => 'edit',
				'view_settings' => $view_settings));
		$data = ob_get_clean();

		$td = "<p class='wpv-filter-post-relationship-edit-summary js-wpv-filter-summary js-wpv-filter-post-relationship-summary'>\n";
		$td .= wpv_get_filter_post_relationship_summary_txt($view_settings);
		$td .= "</p>\n<p class='edit-filter js-wpv-filter-edit-controls'>\n<span><button class='button-secondary js-wpv-filter-edit-open js-wpv-filter-post-relationship-edit-open'>". __('Edit','wpv-views') ."</button>\n<i class='icon-remove-sign js-filter-remove' data-nonce='". wp_create_nonce( 'wpv_view_filter_post_relationship_delete_nonce' ) . "'></i>\n</p>";
		if (!isset($view_settings['post_type'])) $view_settings['post_type'] = array();
		$td .= wpv_get_post_relationship_test($view_settings['post_type']);
		$td .= "<div id=\"wpv-filter-post-relationship-edit\" class=\"wpv-filter-edit js-wpv-filter-edit\">\n";

		$td .= '<fieldset>';
		$td .= '<p><strong>' . __('Post Relationship - Post is a child of', 'wpv-views') . ':</strong></p>';
		$td .= '<div>' . $data . '</div>';
		$td .= '</fieldset>';
		ob_start();
		?>
		<p>
			<input class="button-secondary js-wpv-filter-edit-ok js-wpv-filter-post-relationship-edit-ok" type="button" value="<?php echo htmlentities( __('Close', 'wpv-views'), ENT_QUOTES ); ?>" data-save="<?php echo htmlentities( __('Save', 'wpv-views'), ENT_QUOTES ); ?>" data-close="<?php echo htmlentities( __('Close', 'wpv-views'), ENT_QUOTES ); ?>" data-success="<?php echo htmlentities( __('Updated', 'wpv-views'), ENT_QUOTES ); ?>" data-unsaved="<?php echo htmlentities( __('Not saved', 'wpv-views'), ENT_QUOTES ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_post_relationship_nonce' ); ?>" />
		</p>
		<?php
		$td .= ob_get_clean();
		$td .= '</div>';

		return $td;
	}
	
	/**
	* Update post relationship post select list based on post type
	*/

	add_action('wp_ajax_wpv_get_post_relationship_post_select', 'wpv_get_post_relationship_post_select_callback');

	function wpv_get_post_relationship_post_select_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_post_relationship_post_type_nonce') ) die("Security check");
		wpv_show_posts_dropdown($_POST['post_type'], 'post_relationship_id');
		die();
	}
	
	/**
	* Update post relationship filter callback
	*/

	add_action('wp_ajax_wpv_filter_post_relationship_update', 'wpv_filter_post_relationship_update_callback');

	function wpv_filter_post_relationship_update_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_post_relationship_nonce') ) die("Security check");
		if ( empty( $_POST['post_relationship_mode'] ) ) {
			echo $_POST['id'];
			die();
		}
		$change = false;
		$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
		if (!isset($_POST['post_relationship_id'])) $_POST['post_relationship_id'] = 0;
		if ( !isset( $view_array['post_relationship_mode'] ) || $_POST['post_relationship_mode'] != $view_array['post_relationship_mode'] ) {
			$change = true;
			$view_array['post_relationship_mode'] = array($_POST['post_relationship_mode']);
		}
		if ( !isset( $view_array['post_relationship_id'] ) || $_POST['post_relationship_id'] != $view_array['post_relationship_id'] ) {
			$change = true;
			$view_array['post_relationship_id'] = $_POST['post_relationship_id'];
		}
		if ( $change ) {
			$result = update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		}
		echo wpv_get_filter_post_relationship_summary_txt(
			array(
				'post_relationship_mode'	=> $_POST['post_relationship_mode'],
				'post_relationship_id'		=> $_POST['post_relationship_id']
			)
		);
		die();
	}
	
	/**
	* Update post relationship filter summary callback
	*/

	// TODO This might not be needed here, maybe for summary filter
	add_action('wp_ajax_wpv_filter_post_relationship_sumary_update', 'wpv_filter_post_relationship_sumary_update_callback');

	function wpv_filter_post_relationship_sumary_update_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_post_relationship_nonce') ) die("Security check");
		if (!isset($_POST['post_relationship_id'])) $_POST['post_relationship_id'] = 0;
		echo wpv_get_filter_post_relationship_summary_txt(
			array(
				'post_relationship_mode'	=> $_POST['post_relationship_mode'],
				'post_relationship_id'		=> $_POST['post_relationship_id']
			)
		);
		die();
	}
	
	/**
	* Check post relationship based on queried post types
	*/

	add_action('wp_ajax_wpv_update_post_relationship_test', 'wpv_update_post_relationship_test_callback');

	function wpv_update_post_relationship_test_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_post_relationship_nonce') ) die("Security check");
		echo wpv_get_post_relationship_test($_POST['post_types']);
		die();
	}
	
	/**
	* Delete post relationship filter callback
	*/

	add_action('wp_ajax_wpv_filter_post_relationship_delete', 'wpv_filter_post_relationship_delete_callback');

	function wpv_filter_post_relationship_delete_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_post_relationship_delete_nonce') ) die("Security check");
		$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
		if ( isset( $view_array['post_relationship_mode'] ) ) {
			unset( $view_array['post_relationship_mode'] );
		}
		if ( isset( $view_array['post_relationship_id'] ) ) {
			unset( $view_array['post_relationship_id'] );
		}
		update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		echo $_POST['id'];
		die();

	}
	
	/**
	* Add a filter to show the filter on the summary
	*/
    
	add_filter('wpv-view-get-summary', 'wpv_post_relationship_summary_filter', 5, 3);

	function wpv_post_relationship_summary_filter($summary, $post_id, $view_settings) {
		if(isset($view_settings['query_type']) && $view_settings['query_type'][0] == 'posts' && isset($view_settings['post_relationship_mode'])) {

		$view_settings['post_relationship_mode'] = $view_settings['post_relationship_mode'][0];

			$result = wpv_get_filter_post_relationship_summary_txt($view_settings, true);
			if ($result != '' && $summary != '') {
				$summary .= '<br />';
			}
			$summary .= $result;
		}

		return $summary;
	}
}

/**
* Render post relationship filter options
*/

function wpv_render_post_relationship($args) {

    global $wpdb;

    $edit = isset($args['mode']) && $args['mode'] == 'edit';

    $view_settings = isset($args['view_settings']) ? $args['view_settings'] : array();

    $defaults = array('post_relationship_mode' => 'current_page',
                      'post_relationship_id' => 0);
    $view_settings = wp_parse_args($view_settings, $defaults);

	?>

        <ul>
            <li>
                <?php $checked = $view_settings['post_relationship_mode'] == 'current_page' ? 'checked="checked"' : ''; ?>
                <label><input type="radio" class="js-post-relationship-mode" name="post_relationship_mode[]" value="current_page" <?php echo $checked; ?> />&nbsp;<?php _e('Post where this View is inserted', 'wpv-views'); ?></label>
            </li>

            <li>
                <?php $checked = $view_settings['post_relationship_mode'] == 'parent_view' ? 'checked="checked"' : ''; ?>
                <label><input type="radio" class="js-post-relationship-mode" name="post_relationship_mode[]" value="parent_view" <?php echo $checked; ?> />&nbsp;<?php _e('Post set by parent View', 'wpv-views'); ?></label>
            </li>

            <li>
                <?php $checked = $view_settings['post_relationship_mode'] == 'this_page' ? 'checked="checked"' : ''; ?>
                <label><input type="radio" class="js-post-relationship-mode" name="post_relationship_mode[]" value="this_page" <?php echo $checked; ?> />&nbsp;<?php _e('Specific:', 'wpv-views'); ?></label>

                <select id="wpv_post_relationship_post_type" class="js-post-relationship-post-type" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_post_relationship_post_type_nonce' ); ?>">
                <?php
                    $post_types = get_post_types( array('public' => true), 'objects');
                    if ($view_settings['post_relationship_id'] == 0) {
						if ($edit && isset($_POST['post_relationship_type'])) {
							$selected_type = $_POST['post_relationship_type'];
						} else {
							$selected_type = 'page';
						}
                    } else {
                        $selected_type = $wpdb->get_var($wpdb->prepare("
                                SELECT post_type FROM {$wpdb->prefix}posts WHERE ID=%d", $view_settings['post_relationship_id']));
                        if (!$selected_type) {
                            $selected_type = 'page';
                        }
                    }
                    foreach ($post_types as $post_type) {
                        $selected = $selected_type == $post_type->name ? ' selected="selected"' : '';
                        echo '<option value="' . $post_type->name . '"' . $selected . '>' . $post_type->labels->singular_name . '</option>';
                    }
                ?>
                </select>

                <?php $post_relationship_select_name = 'post_relationship_id' ?>
                <?php wpv_show_posts_dropdown($selected_type, $post_relationship_select_name, $view_settings['post_relationship_id']); ?>

            </li>
        </ul>
        <p>
			<a class="wpv-help-link" target="_blank" href="http://wp-types.com/documentation/user-guides/querying-and-displaying-child-posts/">
				<?php _e('Querying and Displaying Child Posts', 'wpv-views'); ?>
			</a>
		</p>
	<?php
}

/**
* Render post relationship filter summary text
*/

function wpv_get_filter_post_relationship_summary_txt($view_settings, $short=false) {
	global $wpdb;

	ob_start();

	if ($view_settings['post_relationship_mode'] == 'current_page') {
		_e('Select related posts that are a <strong>child</strong> of the <strong>post where this View is inserted</strong>.', 'wpv-views');
		} else if ($view_settings['post_relationship_mode'] == 'parent_view') {
		_e('Select related posts that are a <strong>child</strong> to the <strong>Post set by parent View</strong>.', 'wpv-views');
	} else {
		if (isset($view_settings['post_relationship_id']) && $view_settings['post_relationship_id'] > 0) {
		$selected_title = $wpdb->get_var($wpdb->prepare("
			SELECT post_title FROM {$wpdb->prefix}posts WHERE ID=%d", $view_settings['post_relationship_id']));
		} else {
		$selected_title = 'None';
		}
		echo sprintf(__('Select related posts that are a child of <strong>%s</strong>.', 'wpv-views'), $selected_title);
	}

	$data = ob_get_clean();

		if ($short) {
			if (substr($data, -1) == '.') {
				$data = substr($data, 0, -1);
			}
		}

	return $data;

}

/**
* Check post relationship given the queried post types
*/

function wpv_get_post_relationship_test($selected_types) { // TODO those notices should be added using javascript
	if (function_exists('wpcf_pr_get_belongs')) {
		$post_types = get_post_types('', 'objects');
		$return = '';
		foreach ($selected_types as $post_type) {
			$related = wpcf_pr_get_belongs($post_type);
			if ($related === false) {
				$return .= '<p class="toolset-alert filter-info-post-relationship"><i class="icon-warning-sign"></i> '. sprintf(__('Post type <strong>%s</strong> doesn\'t belong to any other post type', 'wpv-views'), $post_types[$post_type]->labels->singular_name) . '</p>';
			}
			if (is_array($related) && count($related)) {
				$keys = array_keys($related);
				$related = array();
				foreach($keys as $key) {
					$related[] = $post_types[$key]->labels->singular_name;
				}
			}
			if (is_array($related) && count($related) == 1) {
				$related = implode(', ', $related);
				$return .= '<p class="toolset-alert toolset-alert-info filter-info-post-relationship"><i class="icon-ok"></i> '. sprintf(__('Post type <strong>%s</strong> is a child of <strong>%s</strong> post type', 'wpv-views'), $post_types[$post_type]->labels->singular_name, $related) . '</p>';
			}
			if (is_array($related) && count($related) > 1) {
				$last = array_pop($related);
				$related = implode(', ', $related);
				$related .= __(' and ') . $last;
				$return .= '<p class="toolset-alert toolset-alert-info filter-info-post-relationship"><i class="icon-ok"></i> '. sprintf(__('Post type <strong>%s</strong> is a child of <strong>%s</strong> post types', 'wpv-views'), $post_types[$post_type]->labels->singular_name, $related) . '</p>';
			}
		}
		return $return;
	}
}

/**
* RTest post relationship DEPRECATED maybe
*/


function wpv_ajax_wpv_get_post_relationship_info() { // TODO check if this is deprecated
    if (wp_verify_nonce($_POST['wpv_nonce'], 'wpv_get_posts_select_nonce')) {
	    if (function_exists('wpcf_pr_get_belongs') && isset($_POST['post_types'])) {
			$post_types = get_post_types('', 'objects');

			$output_done = false;
			foreach ($_POST['post_types'] as $post_type) {

				$related = wpcf_pr_get_belongs($post_type);
				if ($related === false) {
					echo sprintf(__('Post type <strong>%s</strong> doesn\'t belong to any other post type', 'wpv-views'), $post_types[$post_type]->labels->singular_name, $related);
					echo '<br />';
					$output_done = true;
				}
				if (is_array($related) && count($related)) {
					$keys = array_keys($related);
					$related = array();

					foreach($keys as$key) {
						$related[] = $post_types[$key]->labels->singular_name;
					}

				}
				if (is_array($related) && count($related) == 1) {
					$related = implode(', ', $related);
					echo sprintf(__('Post type <strong>%s</strong> is a child of <strong>%s</strong> post type', 'wpv-views'), $post_types[$post_type]->labels->singular_name, $related);
					echo '<br />';
					$output_done = true;
				}
				if (is_array($related) && count($related) > 1) {
					$last = array_pop($related);
					$related = implode(', ', $related);
					$related .= __(' and ') . $last;
					echo sprintf(__('Post type <strong>%s</strong> is a child of <strong>%s</strong> post types', 'wpv-views'), $post_types[$post_type]->labels->singular_name, $related);
					echo '<br />';
					$output_done = true;
				}
			}
			if ($output_done) {
				echo '<br />';
			}
		}

	}
	die();
}