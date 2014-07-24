<?php

/*
* We can enable this to hide the Content selection section
*/

// add_filter('wpv_sections_query_show_hide', 'wpv_show_hide_content_selector', 1,1);

function wpv_show_hide_content_selector($sections) {
	$sections['content-selection'] = array(
		'name'		=> __('Content selection', 'wpv-views'),
		);
	return $sections;
}

add_action('view-editor-section-query', 'add_view_content_selection', 10, 2);

function add_view_content_selection($view_settings, $view_id) {
    global $views_edit_help;
	$hide = '';
	if (isset($view_settings['sections-show-hide']) && isset($view_settings['sections-show-hide']['content-selection']) && 'off' == $view_settings['sections-show-hide']['content-selection']) {
		$hide = ' hidden';
	}?>
	<div class="wpv-setting-container wpv-settings-content-selection js-wpv-settings-content-selection<?php echo $hide; ?>">
		<div class="wpv-settings-header">
			<h3>
				<?php _e('Content selection', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['content_section']['title']; ?>" data-content="<?php echo $views_edit_help['content_section']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<ul>
				<?php if (!isset( $view_settings['query_type'] ) ) $view_settings['query_type'][0] = 'posts'; ?>
				<li>
					<label for="wpv-settings-query-type"><?php _e('This View will display', 'wpv-views'); ?></label>
					<select name="_wpv_settings[query_type][]" id="wpv-settings-query-type" class="js-wpv-query-type">
					<?php $checked = $view_settings['query_type'][0]=='posts' ? ' selected="selected"' : ''; ?>
					<option id="wpv-settings-query-type-posts" value="posts"<?php echo $checked; ?>><?php _e('Posts','wpv-views') ?></option>
					<?php $checked = $view_settings['query_type'][0]=='taxonomy' ? ' selected="selected"' : ''; ?>
					<option id="wpv-settings-query-type-taxonomy" value="taxonomy"<?php echo $checked; ?>><?php _e('Taxonomy','wpv-views') ?></option>
					</select>

				</li>
				<li>
					<ul class="wpv-settings-query-type-posts">
						<?php $post_types = get_post_types(array('public'=>true), 'objects');
						if ( !isset( $view_settings['post_type'] ) ) $view_settings['post_type']= array();
						foreach($view_settings['post_type'] as $type) {
							if (!isset($post_types[$type])) {
								unset($view_settings['post_type'][$type]);
								}
						}
						?>
						<?php foreach($post_types as $p): ?>
							<li><!-- review the use of $p->name here -->
								<?php $checked = in_array($p->name, $view_settings['post_type']) ? ' checked="checked"' : ''; ?>
								<input type="checkbox" id="wpv-settings-post-type-<?php echo $p->name ?>" name="_wpv_settings[post_type][]" class="js-wpv-query-post-type" value="<?php echo $p->name ?>"<?php echo $checked; ?> />
								<label for="wpv-settings-post-type-<?php echo $p->name ?>"><?php echo $p->labels->name ?></label>
							</li>
						<?php endforeach; ?>
					</ul>
					<ul class="wpv-settings-query-type-taxonomy">
						<?php $taxonomies = get_taxonomies('', 'objects');
						$exclude_tax_slugs = array();
						$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );
						if ( !isset( $view_settings['taxonomy_type'] ) ) $view_settings['taxonomy_type']= array();
						foreach($view_settings['taxonomy_type'] as $type) {
							if (!isset($taxonomies[$type])) {
								unset($view_settings['taxonomy_type'][$type]);
								}
						}
						?>
						<?php foreach($taxonomies as $tax_slug => $tax):?>
							<?php
							if ( in_array($tax_slug, $exclude_tax_slugs ) ) {
								continue; // Take out taxonomies that are in our compatibility black list
							}
							if ( !$tax->show_ui ) {
								continue; // Only show taxonomies with show_ui set to TRUE
							}
							?>
							<?php if (sizeof($view_settings['taxonomy_type']) == 0) { // we need to check at least the first available taxonomy if no one is set
								$view_settings['taxonomy_type'][] = $tax->name;
							}
							$checked = @in_array($tax->name, $view_settings['taxonomy_type']) ? ' checked="checked"' : ''; ?>
							<li>
								<input type="radio" id="wpv-settings-post-taxonomy-<?php echo $tax->name ?>" name="_wpv_settings[taxonomy_type][]" class="js-wpv-query-taxonomy-type" value="<?php echo $tax->name ?>"<?php echo $checked; ?> />
								<label for="wpv-settings-post-taxonomy-<?php echo $tax->name ?>"><?php echo $tax->labels->name ?></label>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			</ul>
			<p class="update-button-wrap">
				<button data-success="<?php echo htmlentities( __('Content selection updated', 'wpv-views'), ENT_QUOTES ); ?>" data-unsaved="<?php echo htmlentities( __('Content selection not saved', 'wpv-views'), ENT_QUOTES ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_query_type_nonce' ); ?>" class="js-wpv-query-type-update button-secondary" disabled="disabled"><?php _e('Update', 'wpv-views'); ?></button>
			</p>
		</div>
	</div>
<?php }