<?php

function wpv_admin_menu_content_templates_listing_page() { ?>
	<div class="wrap toolset-views">
		<div class="wpv-views-listing-page">

			<div id="icon-views" class="icon32"></div>
			<h2 class="wpv-page-title">
				<?php _e('Content Templates', 'wpv-views') ?>
			</h2>
	<?php
	wp_nonce_field( 'work_view_template', 'work_view_template' );

	if ( isset( $_GET["arrangeby"] ) && sanitize_text_field( $_GET["arrangeby"] ) == 'usage' ) {
		$usage = 'single';
		if ( isset( $_GET["usage"] ) ) $usage = sanitize_text_field($_GET["usage"]);
		wpv_admin_content_template_listing_usage($usage);
	} else {
		wpv_admin_content_template_listing_name();
	}
	?>

		<?php
			// TODO: The script below should be moved to the separate file and maybe use another localiation method
			// TODO: Add strings to wp_localize_script
		?>
		<script type="text/javascript">
			jQuery(function($) {

				WpvPagination.ajaxData = { // set data object for AJAX request
					action: 'ct_change_view',
					view: 'name',
					wpnonce : jQuery('#work_view_template').attr('value')
				};


			 });

			 var wpv_close = '<?php echo esc_js( __('Close','wpv-views') ); ?>';
			 var wpv_title = '<?php echo esc_js( __('Title','wpv-views') ); ?>';
			 var wpv_used_on = '<?php echo esc_js( __('Used on','wpv-views') ); ?>';
			 var wpv_ct_msg1 = '<?php echo esc_js( __('Content Template was successfully updated','wpv-views') ); ?>';
			 var wpv_ct_msg6 = '<p><?php echo esc_js( __('Content Templates let you design single pages.','wpv-views') ); ?></p>';
		</script>

		</div> <!-- .wpv-views-listing-page -->

	</div> <!-- .toolset-views -->
<?php }

function wpv_admin_content_template_listing_name() {

	$mod_url = array(
		'orderby' => '',
		'order' => '',
		'search' => '',
		'items_per_page' => ''
	);
	
	$paged_url = '';

	$wpv_args = array(
		'post_type' => 'view-template',
		'posts_per_page' => WPV_ITEMS_PER_PAGE,
		'order' => 'ASC',
		'orderby' => 'title'
	);

	if ( isset( $_GET["search"] ) && '' != $_GET["search"] ) {
		$s_param = urldecode(sanitize_text_field($_GET["search"]));
		$new_args = $wpv_args;
		$unique_ids = array();

		$new_args['posts_per_page'] = '-1';
		$new_args['s'] = $s_param;
		$query_1 = new WP_Query( $new_args );

		while ($query_1->have_posts()) :
			$query_1->the_post();
			$unique_ids[] = get_the_id();
		endwhile;

		unset($new_args['s']);

		$new_args['meta_query'] =array(
			array(
				'key' => '_wpv-content-template-decription',
				'value' => $s_param,
				'compare' => 'LIKE'
			)
		);
		$query_2 = new WP_Query( $new_args );

		while ($query_2->have_posts()) :
			$query_2->the_post();
			$unique_ids[] = get_the_id();
		endwhile;

		$unique = array_unique($unique_ids);

		if ( count($unique) == 0 ){
			$wpv_args['post__in'] = array('-1');
		}else{
			$wpv_args['post__in'] = $unique;
		}

		$mod_url['search'] = '&amp;search=' . sanitize_text_field($_GET["search"]);
	}

	if ( isset( $_GET["items_per_page"] ) && '' != $_GET["items_per_page"] ) {
		$wpv_args['posts_per_page'] = (int) $_GET["items_per_page"];
		$mod_url['items_per_page'] = '&amp;items_per_page=' . (int) $_GET["items_per_page"];
	}

	if ( isset( $_GET["orderby"] ) && '' != $_GET["orderby"] ) {
		$wpv_args['orderby'] = sanitize_text_field($_GET["orderby"]);
		$mod_url['orderby'] = '&amp;orderby=' . sanitize_text_field($_GET["orderby"]);
		if ( isset( $_GET["order"] ) && '' != $_GET["order"] ) {
			$wpv_args['order'] = sanitize_text_field($_GET["order"]);
			$mod_url['order'] = '&amp;order=' . sanitize_text_field($_GET["order"]);
		}
	}

	if ( isset( $_GET["paged"] ) && '' != $_GET["paged"]) {
		$wpv_args['paged'] = (int) $_GET["paged"];
		$paged_url = '&amp;paged=' . (int) $_GET["paged"];
	}

	$query = new WP_Query( $wpv_args );
	$count_posts = $query->post_count;
	$found_posts = $query->found_posts;

	if ( $count_posts != 0 ): // only add the search form in arrange by name ?>
		<form id="posts-filter" action="" method="get">
			<p class="search-box">
				<label class="screen-reader-text" for="post-search-input"><?php _e('Search Views:', 'wpv-views') ?></label>
				<?php $search_term = isset( $_GET["search"] ) ? urldecode( sanitize_text_field($_GET["search"]) ) : ''; ?>
				<input type="search" id="ct-post-search-input" name="search" value="<?php echo $search_term; ?>">
				<input type="submit" name="" id="ct-search-submit" class="button" value="<?php echo htmlentities( __('Search Content Templates', 'wpv-views'), ENT_QUOTES ); ?>">
				<input type="hidden" name="paged" value="1" />
			</p>
		</form>
	<?php endif; ?>

	<?php if ($count_posts == 0) { //When no posts found
		if ( isset( $_GET["search"] ) && '' != $_GET["search"] ) { ?>
			<div class="wpv-views-listing views-empty-list">
				<p><?php echo __('No Content Templates matched your criteria.','wpv-views'); ?> <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=view-templates<?php echo $mod_url['orderby'] . $mod_url['order'] . $mod_url['items_per_page']; ?>&amp;paged=1"><?php _e('Return', 'wpv-views'); ?></a></p>
			</div>
		<?php } else { ?>
			<p class="wpv-view-not-exist">
			  <?php _e('Content Templates let you design single pages.','wpv-views'); ?>
			</p>
			<p class="add-new-view">
				<button class="button js-add-new-content-template"
				data-target="<?php echo admin_url('admin-ajax.php')?>?action=wpv_ct_create_new">
					<i class="icon-plus"></i><?php _e('Add new Content Template','wpv-views') ?>
				</button>
			</p>
		<?php } ?>
	<?php } else { ?>

		<div class="wpv-views-listing-arrange">
			<p><?php _e('Arrange by','wpv-views'); ?>: </p>
			<ul>
				<li data-sortby="name" class="active"><?php _e('Name','wpv-views') ?></li>
				<li data-sortby="usage-single"><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;arrangeby=usage&amp;usage=single"><?php _e('Usage for single page','wpv-views') ?></a></li>
				<li data-sortby="usage-post-archives"><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;arrangeby=usage&amp;usage=post-archives"><?php _e('Usage for custom post archives','wpv-views') ?></a></li>
				<li data-sortby="usage-taxonomy-archives"><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;arrangeby=usage&amp;usage=taxonomy-archives"><?php _e('Usage for taxonomy archives','wpv-views') ?></a></li>
			</ul>
		</div>

		<table class="wpv-views-listing widefat">

		<!-- section for: sort by name -->
			<thead>
				<tr>
					<?php
					$column_active = '';
					$column_sort_to = 'ASC';
					$column_sort_now = 'ASC';
					if ( $wpv_args['orderby'] === 'title' ) {
						$column_active = ' views-list-sort-active';
						$column_sort_to = ( $wpv_args['order'] === 'ASC' ) ? 'DESC' : 'ASC';
						$column_sort_now = $wpv_args['order'];
					}
					?>
					<th><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;orderby=title&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="title"><?php _e('Title','wpv-views') ?> <i class="icon-sort-by-alphabet<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
					<th class="js-wpv-col-two"><?php _e('Used on','wpv-views') ?></th>
					<th><?php _e('Action','wpv-views') ?></th>
					<?php
					$column_active = '';
					$column_sort_to = 'DESC';
					$column_sort_now = 'DESC';
					if ( $wpv_args['orderby'] === 'date' ) {
						$column_active = ' views-list-sort-active';
						$column_sort_to = ( $wpv_args['order'] === 'ASC' ) ? 'DESC' : 'ASC';
						$column_sort_now = $wpv_args['order'];
					}
					?>
					<th><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;orderby=date&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="date"><?php _e('Date','wpv-views') ?> <i class="icon-sort-by-attributes<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<?php
					$column_active = '';
					$column_sort_to = 'ASC';
					$column_sort_now = 'ASC';
					if ( $wpv_args['orderby'] === 'title' ) {
						$column_active = ' views-list-sort-active';
						$column_sort_to = ( $wpv_args['order'] === 'ASC' ) ? 'DESC' : 'ASC';
						$column_sort_now = $wpv_args['order'];
					}
					?>
					<th><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;orderby=title&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="title"><?php _e('Title','wpv-views') ?> <i class="icon-sort-by-alphabet<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
					<th class="js-wpv-col-two"><?php _e('Used on','wpv-views') ?></th>
					<th><?php _e('Action','wpv-views') ?></th>
					<?php
					$column_active = '';
					$column_sort_to = 'DESC';
					$column_sort_now = 'DESC';
					if ( $wpv_args['orderby'] === 'date' ) {
						$column_active = ' views-list-sort-active';
						$column_sort_to = ( $wpv_args['order'] === 'ASC' ) ? 'DESC' : 'ASC';
						$column_sort_now = $wpv_args['order'];
					}
					?>
					<th><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;orderby=date&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="date"><?php _e('Date','wpv-views') ?> <i class="icon-sort-by-attributes<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
				</tr>
			</tfoot>
			<!-- / section for: sort by name -->

			<tbody class="js-wpv-views-listing-body">
				<?php

				while ($query->have_posts()) :
					$query->the_post();
					$meta = get_post_meta(get_the_id(), '_wpv_settings');
					echo wpv_admin_menu_content_template_listing_row( get_the_id() );
				endwhile;
				?>
			</tbody>
		</table>

		<p class="add-new-view">
			<button class="button js-add-new-content-template"
			data-target="<?php echo admin_url('admin-ajax.php')?>?action=wpv_ct_create_new">
				<i class="icon-plus"></i><?php _e('Add new Content Template','wpv-views') ?>
			</button>
		</p>

	<?php } ?>

	<?php
		wpv_admin_listing_pagination( 'view-templates', $found_posts, $wpv_args["posts_per_page"], $mod_url );
	?>

	<div class="popup-window-container">

		<div class="wpv-dialog js-remove-content-template-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Delete Content Template','wpv-views'); ?></h2>
			</div>
			<div class="wpv-dialog-content">
				<p><?php _e('Are you sure want delete this Content Template?','wpv-views'); ?></p>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views'); ?></button>
				<button class="button button-primary js-remove-template-permanent"><?php _e('Delete','wpv-views'); ?></button>
			</div>
		</div>

		<div class="wpv-dialog js-duplicate-ct-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Duplicate Content Template','wpv-views') ?></h2>
			</div>
			<div class="wpv-dialog-content">
		<p>
			<label for="duplicated_ct_name"><?php _e('Name this Content Template','wpv-views'); ?></label>
			<input type="text" value="" class="js-duplicated-ct-name" placeholder="<?php _e('Enter name here','wpv-views') ?>" name="duplicated_ct_name">
		</p>
		<div class="js-ct-duplicate-error"></div>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-secondary js-duplicate-ct" disabled><?php _e('Duplicate','wpv-views') ?></button>
			</div>
		</div> <!-- .js-duplicate-view-dialog -->

	</div>
	<?php
}

function wpv_admin_menu_content_template_listing_row( $post_id ) {

	ob_start();
	$post = get_post($post_id);
	$wpv_content_template_decription  = get_post_meta($post->ID, '_wpv-content-template-decription', true);
	?>
	<tr id="wpv_ct_list_row_<?php echo $post->ID; ?>" class="js-wpv-ct-list-row">

		<td class="post-title page-title column-title">
			<h3 class="row-title">
				<a href="post.php?post=<?php echo $post->ID; ?>&amp;action=edit"><?php echo $post->post_title; ?></a>
			</h3>
			<?php if ( !empty($wpv_content_template_decription) ): ?>
				<p class="desc">
					<?php echo nl2br($wpv_content_template_decription)?>
				</p>
			<?php endif; ?>
		</td>
		<td>
			<?php echo wpv_content_template_used_for_list( $post->ID );?>
		</td>
		<td>
			<select class="js-list-ct-action" name="list_ct_action_<?php echo $post->ID; ?>" id="list_ct_action_<?php echo $post->ID; ?>"
				data-ct-id="<?php echo $post->ID; ?>" data-ct-name="<?php echo htmlentities( $post->title, ENT_QUOTES ); ?>">
				<option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
				<option value="change"><?php _e('Change usage','wpv-views') ?></option>
				<option value="duplicate" data-msg="<?php echo htmlentities( __('Enter new title','wpv-views'), ENT_QUOTES ); ?>"><?php _e('Duplicate','wpv-views') ?></option>
				<option value="delete"><?php _e('Delete','wpv-views') ?></option>
			</select>
		</td>
		<td>
			<?php echo get_the_time(get_option('date_format'), $post->ID); ?>
		</td>
	</tr>

	<?php
	$row = ob_get_contents();
	ob_end_clean();

	return $row;

}

function wpv_admin_content_template_listing_usage($usage = 'single') {
	?>
	<div class="wpv-views-listing-arrange">
		<p><?php _e('Arrange by','wpv-views'); ?>: </p>
		<ul>
			<li data-sortby="name"><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates"><?php _e('Name','wpv-views') ?></a></li>
			<?php $checked = false;
			if ($usage == 'single') $checked = true;
			?>
			<li data-sortby="usage-single"<?php if ($checked) { echo '  class="active"'; } ?>><?php if (!$checked) { ?><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;arrangeby=usage&amp;usage=single"><?php } ?><?php _e('Usage for single page','wpv-views') ?><?php if (!$checked) { ?></a><?php } ?></li>
			<?php $checked = false;
			if ($usage == 'post-archives') $checked = true;
			?>
			<li data-sortby="usage-post-archives"<?php if ($checked) { echo '  class="active"'; } ?>><?php if (!$checked) { ?><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;arrangeby=usage&amp;usage=post-archives"><?php } ?><?php _e('Usage for custom post archives','wpv-views') ?><?php if (!$checked) { ?></a><?php } ?></li>
			<?php $checked = false;
			if ($usage == 'taxonomy-archives') $checked = true;
			?>
			<li data-sortby="usage-taxonomy-archives"<?php if ($checked) { echo '  class="active"'; } ?>><?php if (!$checked) { ?><a href="<?php echo admin_url('admin.php'); ?>?page=view-templates&amp;arrangeby=usage&amp;usage=taxonomy-archives"><?php } ?><?php _e('Usage for taxonomy archives','wpv-views') ?><?php if (!$checked) { ?></a><?php } ?></li>
		</ul>
	</div>

	<table class="wpv-views-listing widefat">

			<thead>
				<tr>
					<th><?php _e('Used on','wpv-views') ?></th>
					<th><?php _e('Title','wpv-views') ?></th>
					<th><?php _e('Action','wpv-views') ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php _e('Used on','wpv-views') ?></th>
					<th><?php _e('Title','wpv-views') ?></th>
					<th><?php _e('Action','wpv-views') ?></th>
				</tr>
			</tfoot>
			<!-- / section for: sort by name -->

			<tbody class="js-wpv-views-listing-body">
				<?php
				echo wpv_admin_menu_content_template_listing_by_type_row('usage-' . $usage);
				?>
			</tbody>
		</table>
	<?php
}

function wpv_content_template_used_for_list( $ct_id ){
	global $WP_Views, $wpdb;
	$list = '';
	$show_single = $show_loop = $show_tax = 0;
	$options = $WP_Views->get_options();
	$post_types_array = wpv_get_pt_tax_array();

	for ( $i=0; $i<count($post_types_array['single_post']); $i++ ) {
		$type = $post_types_array['single_post'][$i][0];
		$label = $post_types_array['single_post'][$i][1];
		if ( isset($options['views_template_for_' . $type]) && $options['views_template_for_' . $type] == $ct_id)   {
			$list .= '<li>' . $label;
				$posts = $wpdb->get_col( "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE post_type='{$type}' AND post_status!='auto-draft'" );
			$count = sizeof( $posts );
				if ( $count > 0 ) {
					$posts = "'" . implode( "','", $posts ) . "'";
					$set_count = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE
					meta_key='_views_template' AND meta_value='{$options['views_template_for_' . $type]}'
					AND post_id IN ({$posts})" );
					if ( ( $count - $set_count ) > 0){
					  $list .= '<a class="icon-warning-sign js-apply-for-all-posts js-alret-icon-hide-'.$type.'"
					  data-target="'.admin_url('admin-ajax.php').'?action=wpv_ct_update_posts&type='.$type.'&tid='.$ct_id.'"></a>';
					}
				}
			$list .= '</li>';
		}
	}

	for ($i=0;$i<count($post_types_array['archive_post']);$i++){
		$type = $post_types_array['archive_post'][$i][0];
		$label = '<li>' . $post_types_array['archive_post'][$i][1];
		if ( isset($options['views_template_archive_for_' . $type]) && $options['views_template_archive_for_' . $type] == $ct_id)   {
			$list .= $label . ' ' . __('Archive','wpv-views') . '</li>';
		 }
	}

	for ($i=0;$i<count($post_types_array['taxonomy_post']);$i++){
		$type = $post_types_array['taxonomy_post'][$i][0];
		$label = $post_types_array['taxonomy_post'][$i][1];
		if ( isset($options['views_template_loop_' . $type]) && $options['views_template_loop_' . $type] == $ct_id)   {
			$list .= $label . ' ' . __('Taxonomy','wpv-views') . '</li>';
		 }
	}
	if ( !empty($list) ){
		$list = '<ul>' . $list . '</ul>';
	}
	else{
	   $list = '<p>' . __('No Post types/Taxonomies assigned','wpv-views') . '</p>';
	}
	return $list;
}

function wpv_get_pt_tax_array(){
   static $post_types_array;
   static $taxonomies_array;
   static $wpv_posts_array;
   if ( !is_array($post_types_array) ){
	   $post_types = get_post_types( array('public' => true), 'objects' );
   }
   if ( !is_array($taxonomies_array) ){
	   $taxonomies = get_taxonomies( '', 'objects' );
   }
   $exclude_tax_slugs = array();
	$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );

   if ( is_array($wpv_posts_array) ){
	   return $wpv_posts_array;
   }

   foreach ( $post_types as $post_type ) {
	   $wpv_posts_array['single_post'][] = array( $post_type->name, $post_type->label );
	   $wpv_posts_array['archive_post'][] = array( $post_type->name, $post_type->label );
   }

   foreach ( $taxonomies as $category_slug => $category ) {
	   if ( in_array($category_slug, $exclude_tax_slugs ) ) {
				continue;
	   }
	   if ( !$category->show_ui ) {
			continue; // Only show taxonomies with show_ui set to TRUE
		}
		$wpv_posts_array['taxonomy_post'][] = array( $category->name, $category->labels->name );
   }

   return $wpv_posts_array;
}

function wpv_admin_menu_content_template_listing_by_type_row( $sort, $page = 0){ // TODO check if the action URL parameter is needed when creating a CT
	global $WP_Views, $post, $wpdb;
	$options = $WP_Views->get_options();
	$post_types = get_post_types( array('public' => true), 'objects' );
	$post_types_array = wpv_get_pt_tax_array();
	ob_start();
	if ( $sort == 'usage-single' ){
		for ($i=0;$i<count($post_types_array['single_post']);$i++){
			$type = $post_types_array['single_post'][$i][0];
			$label = $post_types_array['single_post'][$i][1];


		?>

			<tr id="wpv_ct_list_row_<?php echo $type; ?>" class="js-wpv-ct-list-row">
				<td class="post-title page-title column-title">
					<h3 class="row-title">
						<?php echo $label;?>
					</h3>
				</td>
				<td>
					<ul>
						<?php
						$add_button = '<a class="button" data-disabled="1"
						href="post-new.php?post_type=view-template&action=wpv_ct_create_new&post_title='. urlencode(__('Content template for ','wpv-views'). $label ) .'&ct_selected=views_template_for_' . $type.'&toggle=1,0,0">
						<i class="icon-plus"></i>'. __('Add a new Content Template for this post type','wpv-views').'</a>';

						if ( isset($options['views_template_for_' . $type] ) && $options['views_template_for_' . $type] != 0)   {
							$post = get_post( $options['views_template_for_' . $type] );
							if ( is_object($post) ){
								?>
								<a href="post.php?post=<?php echo $post->ID;?>&action=edit"><?php echo $post->post_title;?></a>
								<?php
								$posts = $wpdb->get_col( "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE post_type='{$type}' AND post_status!='auto-draft'" );
								$count = sizeof( $posts );
								if ( $count > 0 ) {
									$posts = "'" . implode( "','", $posts ) . "'";
									$set_count = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE
									meta_key='_views_template' AND meta_value='{$options['views_template_for_' . $type]}'
									AND post_id IN ({$posts})" );
									if ( ( $count - $set_count ) > 0){
									 echo ' <span class="js-alret-icon-hide-'.$type.'"><a class="icon-warning-sign js-apply-for-all-posts"
									  data-target="'.admin_url('admin-ajax.php').'?action=wpv_ct_update_posts&type='.$type.'&tid='.$post->ID.'"></a> '. ( $count - $set_count ) .' '. $label .' use a different template '.
									  '<a class="js-apply-for-all-posts" href=""
									  data-target="'.admin_url('admin-ajax.php').'?action=wpv_ct_update_posts&type='.$type.'&tid='.$post->ID.'">Update now</a></span>';
									}
								}

							}
							else{
								echo $add_button;
							}
						}
						else{
						   echo $add_button;
						}
						?>
					</ul>
				</td>
				<td>
					<select class="js-list-ct-action">
						<option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
						<option value="change_pt" data-msg="1" data-sort="<?php echo $sort;?>" data-pt="<?php echo 'views_template_for_' . $type;?>"><?php _e('Change Content Template','wpv-views') ?></option>
					</select>
				</td>

			</tr>
	<?php

		}
	}
	?>
  <?php
	if ( $sort == 'usage-post-archives' ){

		 for ($i=0;$i<count($post_types_array['archive_post']);$i++){
			$type = $post_types_array['archive_post'][$i][0];
			$label = $post_types_array['archive_post'][$i][1];
			 $add_button = '<a class="button" data-disabled="1"
						href="post-new.php?post_type=view-template&action=wpv_ct_create_new&post_title='. urlencode(__('Content template for ','wpv-views'). $label ) .'&ct_selected=views_template_archive_for_' . $type.'&toggle=0,1,0">
						<i class="icon-plus"></i>'. __('Add a new Content Template for this post type','wpv-views').'</a>';
		?>

			<tr id="wpv_ct_list_row_<?php echo $type; ?>" class="js-wpv-ct-list-row">
				<td class="post-title page-title column-title">
					<h3 class="row-title">
						<?php echo $label;?>
					</h3>
				</td>
				<td>
					<ul>
						<?php
						if ( isset($options['views_template_archive_for_' . $type] ) && $options['views_template_archive_for_' . $type] != 0)   {
							$post = get_post( $options['views_template_archive_for_' . $type] );
							if ( is_object($post) ){
								?>
								<a href="post.php?post=<?php echo $post->ID;?>&action=edit"><?php echo $post->post_title;?></a>
								<?php
							}
							else{
								echo $add_button;
							}
						}
						else{
						   echo $add_button;
						}
						?>
					</ul>
				</td>
				<td>
					<select class="js-list-ct-action">
						<option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
						<option value="change_pt" data-msg="1" data-sort="<?php echo $sort;?>" data-pt="<?php echo 'views_template_archive_for_' . $type;?>"><?php _e('Change Content Template','wpv-views') ?></option>
					</select>
				</td>

			</tr>
	<?php
		}
	}

	if ( $sort == 'usage-taxonomy-archives' ){

		for ($i=0;$i<count($post_types_array['taxonomy_post']);$i++){
			$type = $post_types_array['taxonomy_post'][$i][0];
			$label = $post_types_array['taxonomy_post'][$i][1];
			$add_button = '<a class="button js-wpv-ct-create-new-for-usage" data-disabled="1"
						data-title="' . urlencode(__('Content template for ','wpv-views'). $label ) . '"
						data-usage="views_template_loop_' . $type . '"
						href="post-new.php?post_type=view-template&action=wpv_ct_create_new&post_title='. urlencode(__('Content template for ','wpv-views'). $label ) .'&ct_selected=views_template_loop_' . $type.'&toggle=0,0,1">
						<i class="icon-plus"></i>'. __('Add a new Content Template for this taxonomy','wpv-views').'</a>';
		?>

			<tr id="wpv_ct_list_row_<?php echo $type; ?>" class="js-wpv-ct-list-row">
				<td class="post-title page-title column-title">
					<h3 class="row-title">
						<?php echo $label;?>
					</h3>
				</td>
				<td>
					<ul>
						<?php
						if ( isset($options['views_template_loop_' . $type]) && $options['views_template_loop_' . $type] != 0)   {
							$post = get_post( $options['views_template_loop_' . $type] );
							if ( is_object($post) ){
								?>
								<a href="post.php?post=<?php echo $post->ID;?>&action=edit"><?php echo $post->post_title;?></a>
								<?php
							}
							else{
								 echo $add_button;
							}
						}
						else{
							echo $add_button;
						}
						?>
					</ul>
				</td>
				<td>
					<select class="js-list-ct-action">
						<option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
						<option value="change_pt" data-msg="2" data-sort="<?php echo $sort;?>" data-pt="<?php echo 'views_template_loop_' . $type;?>"><?php _e('Change Content Template','wpv-views') ?></option>
					</select>
				</td>

			</tr>
	<?php
		}
	}

	$row = ob_get_contents();
	ob_end_clean();

	return $row;
}