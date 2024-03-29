<?php

if(is_admin()){
	
	/**
	* Add the ID filter to the list and to the popup select
	*/
	
	add_filter('wpv_filters_add_filter', 'wpv_filters_add_filter_id', 1,1);
	add_action('wpv_add_filter_list_item', 'wpv_add_filter_id_list_item', 1, 1);

	function wpv_filters_add_filter_id($filters) {
		$filters['post_id'] = array('name' => __('Post id', 'wpv-views'),
						'present' => 'id_mode',
						'callback' => 'wpv_add_new_filter_id_list_item'
						);

		return $filters;
	}
	
	/**
	* Create ID filter callback
	*/

	function wpv_add_new_filter_id_list_item() {
		$args = array(
			'id_mode' => array('by_ids')
		);
		wpv_add_filter_id_list_item($args);
	}
	
	/**
	* Render ID filter item in the filters list
	*/

	function wpv_add_filter_id_list_item($view_settings) {
		if (isset($view_settings['id_mode'][0])) {
			$li = wpv_get_list_item_ui_post_id(null, $view_settings);
			echo '<li id="js-row-post_id" class="js-filter-row js-filter-row-simple js-filter-for-posts js-filter-id js-filter-row-post_id">' . $li . '</li>';
		}
	}
	
	/**
	* Render ID filter item content in the filters list
	*/

	function wpv_get_list_item_ui_post_id($selected, $view_settings = null) {

		if (isset($view_settings['id_mode']) && is_array($view_settings['id_mode'])) {
			$view_settings['id_mode'] = $view_settings['id_mode'][0];
		}

		ob_start();
		wpv_render_id_options(array('mode' => 'edit', 'view_settings' => $view_settings));
		$data = ob_get_clean();
		$td = "<p class='wpv-filter-id-edit-summary js-wpv-filter-summary js-wpv-filter-id-summary'>\n";
		$td .= wpv_get_filter_id_summary_txt($view_settings);
		$td .= "</p>\n<p class='edit-filter js-wpv-filter-edit-controls'>\n<button class='button-secondary js-wpv-filter-edit-open js-wpv-filter-id-edit-open'>". __('Edit','wpv-views') ."</button>\n<i class='icon-remove-sign js-filter-remove' data-nonce='". wp_create_nonce( 'wpv_view_filter_id_delete_nonce' ) . "'></i>\n</p>";
		$td .= "<div id=\"wpv-filter-id-edit\" class=\"wpv-filter-edit js-wpv-filter-edit\">\n";
		$td .= '<fieldset>';
		$td .= '<p><strong>' . __('Post Ids', 'wpv-views') . ':</strong></p>';
		$td .= '<div id="wpv-filter-id" class="js-filter-id-list">' . $data . '</div>';
		$td .= '</fieldset>';
		ob_start();
		?>
		<p>
			<input class="button-secondary js-wpv-filter-edit-ok js-wpv-filter-id-edit-ok" type="button" value="<?php echo htmlentities( __('Close', 'wpv-views'), ENT_QUOTES ); ?>" data-save="<?php echo htmlentities( __('Save', 'wpv-views'), ENT_QUOTES ); ?>" data-close="<?php echo htmlentities( __('Close', 'wpv-views'), ENT_QUOTES ); ?>" data-success="<?php echo htmlentities( __('Updated', 'wpv-views'), ENT_QUOTES ); ?>" data-unsaved="<?php echo htmlentities( __('Not saved', 'wpv-views'), ENT_QUOTES ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_id_nonce' ); ?>" />
		</p>
		<p class="wpv-custom-fields-help">
                        <?php echo sprintf(__('%sLearn about filtering by Post ID%s', 'wpv-views'),
                                        '<a class="wpv-help-link" href="' . WPV_FILTER_BY_POST_ID_LINK . '" target="_blank">',
                                        ' &raquo;</a>'
                                        ); ?>
        </p>
		<?php
		$td .= ob_get_clean();
		$td .= '</div>';

		return $td;
	}
	
	/**
	* Update ID filter callback
	*/

	add_action('wp_ajax_wpv_filter_id_update', 'wpv_filter_id_update_callback');

	function wpv_filter_id_update_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_id_nonce') ) die("Security check");
		if ( empty( $_POST['filter_id'] ) ) {
			echo $_POST['id'];
			die();
		}
		parse_str($_POST['filter_id'], $filter_id);
		$change = false;
		$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
		if ( !isset( $filter_id['post_id_ids_list'] ) || '' == $filter_id['post_id_ids_list'] ) {
			$filter_id['post_id_ids_list'] = '';
		}
		if ( !isset( $view_array['id_mode'] ) || $filter_id['id_mode'] != $view_array['id_mode'] ) {
			$change = true;
			$view_array['id_mode'] = $filter_id['id_mode'];
		}
		if ( !isset( $view_array['post_id_ids_list'] ) || sanitize_text_field($filter_id['post_id_ids_list']) != $view_array['post_id_ids_list'] ) {
			$change = true;
			$view_array['post_id_ids_list'] = sanitize_text_field($filter_id['post_id_ids_list']);
		}
		if ( !isset( $view_array['post_ids_url'] ) || sanitize_text_field($filter_id['post_ids_url']) != $view_array['post_ids_url'] ) {
			$change = true;
			$view_array['post_ids_url'] = sanitize_text_field($filter_id['post_ids_url']);
		}
		if ( !isset( $view_array['post_ids_shortcode'] ) || sanitize_text_field($filter_id['post_ids_shortcode']) != $view_array['post_ids_shortcode'] ) {
			$change = true;
			$view_array['post_ids_shortcode'] = sanitize_text_field($filter_id['post_ids_shortcode']);
		}
		if ( $change ) {
			$result = update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		}
		$filter_id['id_mode'] = $filter_id['id_mode'][0];
		echo wpv_get_filter_id_summary_txt($filter_id);
		die();
	}
	
	/**
	* Update ID filter summary callback
	*/

	// TODO This might not be needed here, maybe for summary filter
	add_action('wp_ajax_wpv_filter_id_sumary_update', 'wpv_filter_id_sumary_update_callback');

	function wpv_filter_id_sumary_update_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_id_nonce') ) die("Security check");
		parse_str($_POST['filter_id'], $filter_id);
		$filter_id['id_mode'] = $filter_id['id_mode'][0];
		echo wpv_get_filter_id_summary_txt($filter_id);
		die();
	}
	
	/**
	* Delete ID filter callback
	*/

	add_action('wp_ajax_wpv_filter_post_id_delete', 'wpv_filter_id_delete_callback');

	function wpv_filter_id_delete_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_id_delete_nonce') ) die("Security check");
		$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
		if ( isset( $view_array['id_mode'] ) ) {
			unset( $view_array['id_mode'] );
		}
		if ( isset( $view_array['post_id_ids_list'] ) ) {
			unset( $view_array['post_id_ids_list'] );
		}
		if ( isset( $view_array['post_ids_url'] ) ) {
			unset( $view_array['post_ids_url'] );
		}
		if ( isset( $view_array['post_ids_shortcode'] ) ) {
			unset( $view_array['post_ids_shortcode'] );
		}
		update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		echo $_POST['id'];
		die();

	}
    
	/**
	* Add a filter to show the filter on the summary
	*/

	add_filter('wpv-view-get-summary', 'wpv_id_summary_filter', 5, 3);

	function wpv_id_summary_filter($summary, $post_id, $view_settings) {
		if(isset($view_settings['query_type']) && $view_settings['query_type'][0] == 'posts' && isset($view_settings['id_mode'])) {
			$view_settings['id_mode'] = $view_settings['id_mode'][0];
			
			$result = wpv_get_filter_id_summary_txt($view_settings, true);
			if ($result != '' && $summary != '') {
				$summary .= '<br />';
			}
			$summary .= $result;
		}
		
		return $summary;
	}

}

/**
* Render ID filter options
*/

function wpv_render_id_options($args) {
	global $wpdb;

	$edit = isset($args['mode']) && $args['mode'] == 'edit';

	$view_settings = isset($args['view_settings']) ? $args['view_settings'] : array();

	$defaults = array('id_mode' => 'by_ids',
			  'post_id_ids_list' =>'',
			  'post_ids_url' => 'post_ids',
			  'post_ids_shortcode' => 'ids');
	$view_settings = wp_parse_args($view_settings, $defaults);

	    ?>
	    <ul>
        <?php if ($edit): // only one instance of this filter by view ?>
			<input type="hidden" name="_wpv_settings[post_id]" value="1"/>
		<?php endif; ?>
		<li>
		    <?php $checked = $view_settings['id_mode'] == 'by_ids' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="id_mode[]" value="by_ids" <?php echo $checked; ?> />
            	&nbsp;<?php _e('One of these IDs ', 'wpv-views'); ?></label>
		    <input type='text' name="post_id_ids_list" value="<?php echo esc_attr($view_settings['post_id_ids_list']); ?>" size="15" />
		</li>

        <li>
		    <?php $checked = $view_settings['id_mode'] == 'by_url' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="id_mode[]" value="by_url" <?php echo $checked; ?>>&nbsp;
				<?php _e('Value set by this URL parameter: ', 'wpv-views'); ?></label>
		    <input type='text' class="js-wpv-filter-id-url js-wpv-filter-validate" data-type="url" data-class="js-wpv-filter-id-url" name="post_ids_url" value="<?php echo $view_settings['post_ids_url']; ?>" size="10" />
		</li>

        <li>
		    <?php $checked = $view_settings['id_mode'] == 'shortcode' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="id_mode[]" value="shortcode" <?php echo $checked; ?>>&nbsp;
			<?php _e('Value set by View shortcode attribute: ', 'wpv-views'); ?></label>
		    <input type='text' class="js-wpv-filter-id-shortcode js-wpv-filter-validate" data-type="shortcode" data-class="js-wpv-filter-id-shortcode" name="post_ids_shortcode" value="<?php echo $view_settings['post_ids_shortcode']; ?>" size="10" />
		</li>

	    </ul>

	    <div class="wpv_id_helper"></div>

	    <?php
}

/**
* Render ID filter summary text
*/

function wpv_get_filter_id_summary_txt($view_settings, $short=false) {
	global $wpdb;
	if (isset($_GET['post'])) {$view_name = get_the_title( $_GET['post']);} else {$view_name = 'view-name';}
	ob_start();

	switch ($view_settings['id_mode']) {

		case 'by_ids':
			if (isset($view_settings['post_id_ids_list']) && '' != $view_settings['post_id_ids_list']){
			$ids_list = $view_settings['post_id_ids_list'];
			} else {
			$ids_list = '<i>' . __('None set', 'wpv-views') . '</i>';
			}
			echo sprintf(__('Select posts with the listed <strong>IDs</strong>: %s', 'wpv-views'), $ids_list);
			break;
		case 'by_url':
			if (isset($view_settings['post_ids_url']) && '' != $view_settings['post_ids_url']){
			$url_ids = $view_settings['post_ids_url'];
			} else {
			$url_ids = '<i>' . __('None set', 'wpv-views') . '</i>';
			}

			echo sprintf(__('Select posts with the IDs determined by the URL parameter <strong>"%s"</strong>', 'wpv-views'), $url_ids);
			echo sprintf(__(' eg. yoursite/page-with-this-view/?<strong>%s</strong>=1', 'wpv-views'), $url_ids);
			break;
		case 'shortcode':
			if (isset($view_settings['post_ids_shortcode']) && '' != $view_settings['post_ids_shortcode']) {
			$id_short = $view_settings['post_ids_shortcode'];
			} else {
			$id_short = 'None';
			}
			echo sprintf(__('Select posts which IDs is set by the View shortcode attribute <strong>"%s"</strong>', 'wpv-views'), $id_short);
			echo sprintf(__(' eg. [wpv-view name="%s" <strong>%s</strong>="1"]', 'wpv-views'), $view_name, $id_short);

			break;
		}

	$data = ob_get_clean();

	if ($short) {
		// this happens on the Views table under Filter column
		if (substr($data, -1) == '.') {
			$data = substr($data, 0, -1);
		}
	}

	return $data;
}