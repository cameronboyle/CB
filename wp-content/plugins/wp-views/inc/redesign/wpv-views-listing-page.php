<?php

function wpv_admin_menu_views_listing_page() {
	?>
	<div class="wrap toolset-views">

		<div class="wpv-views-listing-page">
			<?php $has_views = wpv_check_views_exists('normal'); ?>
			<?php wp_nonce_field( 'work_views_listing', 'work_views_listing' ); ?>
			<div id="icon-views" class="icon32"></div>
			<h2 class="wpv-page-title">
				<?php _e('Views', 'wpv-views') ?>
				<a href="#" class="add-new-h2 js-wpv-views-add-new-top <?php if (!$has_views) echo 'hidden'; ?>"><?php _e('Add new View','wpv-views') ?></a>
				<?php wp_nonce_field('wp_nonce_create_view_wrapper', 'wp_nonce_create_view_wrapper'); ?>
			</h2>
                
			<?php if ( $has_views ) { ?>
				<form id="posts-filter" action="" method="get">
					<p class="search-box">
						<label class="screen-reader-text" for="post-search-input"><?php _e('Search Views','wpv-views'); ?>:</label>
						<?php $search_term = isset( $_GET["search"] ) ? urldecode( sanitize_text_field($_GET["search"]) ) : ''; ?>
						<input type="search" id="post-search-input" name="search" value="<?php echo $search_term; ?>" />
						<input type="submit" name="" id="search-submit" class="button" value="<?php echo htmlentities( __('Search Views','wpv-views'), ENT_QUOTES ); ?>" />
						<input type="hidden" name="paged" value="1" />
					</p>
				</form>
				<?php
				wpv_admin_view_listing_table($has_views);
			} else { ?>
				<div class="wpv-view-not-exist js-wpv-view-not-exist">
				<p><?php _e('Views load content from the database and display on the site.'); ?></p>
				<p><a class="button js-wpv-views-add-first" href="#"><i class="icon-plus"></i><?php _e('Create your first View','wpv-views');?></a></p>
				</div>
			<?php } ?>

		</div> <!-- .wpv-views-listing-page" -->

	</div> <!-- .toolset-views" -->

	<div class="popup-window-container">

		<div class="wpv-dialog create-view-form-dialog js-create-view-form-dialog">
			<?php wp_nonce_field('wp_nonce_create_view', 'wp_nonce_create_view'); ?>
			<input class="js-view-new-redirect" name="view_creation_redirect" type="hidden" value="<?php echo admin_url( 'admin.php?page=views-editor&amp;view_id='); ?>" />
			<div class="wpv-dialog-header">
				<h2><?php _e('Add a new View','wpv-views') ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
			<div class="wpv-dialog-content no-scrollbar">
				<p>
					<?php _e('A View loads content from the database and displays with your HTML.', 'wpv-views'); ?>
				</p>
				<p>
					<strong><?php _e(' What kind of display do you want to create?','wpv-views'); ?></strong>
				</p>
				<ul>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_all" value="all" />
							<label for="view_purpose_all"><?php _e('Display all results','wpv-views'); ?></label>
						</p>
						<p class="tip"><?php _e('The View will output all the results returned from the query section.', 'wpv-views'); ?></p>
					</li>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_pagination" value="pagination" />
							<label for="view_purpose_pagination"><?php _e('Display the results with pagination','wpv-views'); ?></label>
						</p>
						<p class="tip"><?php _e('The View will display the query results in pages.', 'wpv-views'); ?></p>
					</li>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_slider" value="slider" />
							<label for="view_purpose_slider"><?php _e('Display the results as a slider','wpv-views'); ?></label>
						</p>
						<p class="tip"><?php _e('The View will display the query results as slides.', 'wpv-views'); ?></p>
					</li>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_parametric" value="parametric" />
							<label for="view_purpose_parametric"><?php _e('Display the results as a parametric search','wpv-views'); ?></label>
						</p>
						<p class="tip"><?php _e('Visitors will be able to search through your content using different search criteria.', 'wpv-views'); ?></p>
					</li>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_full" value="full" />
							<label for="view_purpose_full"><?php _e('Full custom display mode','wpv-views'); ?></label>
						</p>
						<p class="tip"><?php _e('See all the View controls open and set up things manually..', 'wpv-views'); ?></p>
					</li>
				</ul>

				<p>
					<strong><label for="view_new_name"><?php _e('Name this View','wpv-views'); ?></label></strong>
				</p>
				<p>
					<input type="text" name="view_new_name" id="view_new_name" class="js-new-post_title" placeholder="<?php echo htmlentities( __('Enter title here', 'wpv-views'), ENT_QUOTES ); ?>" data-highlight="<?php echo htmlentities( __('Now give this View a name', 'wpv-views'), ENT_QUOTES ); ?>" />
				</p>
				<div class="js-error-container">

				</div>

			</div>
			<div class="wpv-dialog-footer">
				<?php wp_nonce_field('wp_nonce_create_view', 'wp_nonce_create_view'); ?>
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-primary js-create-new-view"><?php _e('Create View','wpv-views') ?></button>
			</div>
		</div> <!-- .create-view-form-dialog -->

		<div class="wpv-dialog js-delete-view-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Delete View','wpv-views') ?></h2>
			</div>
			<div class="wpv-dialog-content">
				<p><?php _e('Are you sure want delete this View?','wpv-views') ?></p>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-primary js-remove-view-permanent" data-nonce="<?php echo wp_create_nonce( 'wpv_remove_view_permanent_nonce' ); ?>"><?php _e('Delete','wpv-views') ?></button>
			</div>
		</div> <!-- .js-delete-view-dialog -->

		<div class="wpv-dialog js-duplicate-view-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Duplicate View','wpv-views') ?></h2>
			</div>
			<div class="wpv-dialog-content">
                <p>
                    <label for="duplicated_view_name"><?php _e('Name this View','wpv-views'); ?></label>
                    <input type="text" value="" class="js-duplicated-view-name" placeholder="<?php _e('Enter name here','wpv-views') ?>" name="duplicated_view_name">
                </p>
                <div class="js-view-duplicate-error"></div>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-secondary js-duplicate-view" disabled="disabled" data-nonce="<?php echo wp_create_nonce( 'wpv_duplicate_view_nonce' ); ?>" data-error="<?php echo htmlentities( __('A View with that name already exists. Please use another name.', 'wpv-views'), ENT_QUOTES ); ?>"><?php _e('Duplicate','wpv-views') ?></button>
			</div>
		</div> <!-- .js-duplicate-view-dialog -->

	</div> <!-- .popup-window-container" -->
<?php }

function wpv_admin_view_listing_table($views_ids) {
	
	$mod_url = array(
		'orderby' => '',
		'order' => '',
		'search' => '',
		'items_per_page' => ''
	);

	$paged_url = '';
	
	$wpv_args = array(
		'post_type' => 'view',
		'post__in' => $views_ids,
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
				'key' => '_wpv_description',
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
	
	$wpv_query = new WP_Query( $wpv_args );
	$wpv_count_posts = $wpv_query->post_count;
	$wpv_found_posts = $wpv_query->found_posts;
	if ( $wpv_count_posts > 0 ) {
	?>
	<table class="wpv-views-listing js-wpv-views-listing widefat">
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
			<th><a href="<?php echo admin_url('admin.php'); ?>?page=views&amp;orderby=title&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="title"><?php _e('Title','wpv-views') ?> <i class="icon-sort-by-alphabet<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
			<th class="js-wpv-col-two"><?php _e('Content to load','wpv-views') ?></th>
			<th class="js-wpv-col-three"><?php _e('Action','wpv-views') ?></th>
			<th><?php _e('Where this View is inserted?','wpv-views') ?></th>
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
			<th><a href="<?php echo admin_url('admin.php'); ?>?page=views&amp;orderby=date&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="date"><?php _e('Date','wpv-views') ?> <i class="icon-sort-by-attributes<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
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
			<th><a href="<?php echo admin_url('admin.php'); ?>?page=views&amp;orderby=title&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="title"><?php _e('Title','wpv-views') ?> <i class="icon-sort-by-alphabet<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
			<th class="js-wpv-col-two"><?php _e('Content to load','wpv-views') ?></th>
			<th class="js-wpv-col-three"><?php _e('Action','wpv-views') ?></th>
			<th><?php _e('Where this View is inserted?','wpv-views') ?></th>
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
			<th><a href="<?php echo admin_url('admin.php'); ?>?page=views&amp;orderby=date&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="date"><?php _e('Date','wpv-views') ?> <i class="icon-sort-by-attributes<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
		</tr>
		</tfoot>

		<tbody class="js-wpv-views-listing-body">
		<?php
		
		while ($wpv_query->have_posts()) :
			$wpv_query->the_post();
			$post_id = get_the_id();
			$post = get_post($post_id);
			$meta = get_post_meta($post_id, '_wpv_settings');
			$view_description = get_post_meta($post_id, '_wpv_description', true);
			?>
			<tr id="wpv_view_list_row_<?php echo $post->ID; ?>" class="js-wpv-view-list-row">
				<td class="post-title page-title column-title">
					<h3 class="row-title">
						<a href="admin.php?page=views-editor&amp;view_id=<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></a>
					</h3>
					<?php if (isset($view_description) && '' != $view_description): ?>
						<p class="desc">
						<?php echo nl2br($view_description)?>
						</p>
					<?php endif; ?>
				</td>
				<td>
					<?php echo wpv_create_content_summary_for_listing($post->ID); ?>
				</td>
				<td>
					<select class="js-views-actions" name="list_views_action_<?php echo $post->ID; ?>" id="list_views_action_<?php echo $post->ID; ?>" data-view-id="<?php echo $post->ID; ?>">
						<option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
						<option value="delete"><?php _e('Delete','wpv-views') ?></option>
						<option value="duplicate"><?php _e('Duplicate','wpv-views') ?></option>
					</select>
				</td>
				<td>
					<button class="button js-scan-button" data-view-id="<?php echo $post->ID; ?>">
						<?php _e('Scan','wp-views') ?>
					</button>
					<p class="js-nothing-message hidden"><?php _e('Nothing found','wpv-views');?><p>
				</td>
				<td>
					<?php echo get_the_time(get_option('date_format'), $post->ID); ?>
				</td>
			</tr>
			<?php
		endwhile;
		?>
		</tbody>
	</table>
	
	<p class="add-new-view" >
		<a class="button js-wpv-views-add-new" href="#">
			<i class="icon-plus"></i><?php _e('Add new View','wpv-views') ?>
		</a>
	</p>
	
	<?php
		wpv_admin_listing_pagination( 'views', $wpv_found_posts, $wpv_args["posts_per_page"], $mod_url );
	?>
	
	<?php } else { // No Views matches the criteria ?>
		<div class="wpv-views-listing views-empty-list">
			<p><?php echo __('No Views matched your criteria.','wpv-views'); ?> <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=views<?php echo $mod_url['orderby'] . $mod_url['order'] . $mod_url['items_per_page']; ?>&amp;paged=1"><?php _e('Return', 'wpv-views'); ?></a></p>
		</div>
	<?php } ?>

<?php }

function wpv_admin_menu_views_listing_row($post_id) {

	ob_start();
	$post = get_post($post_id);
	$meta = get_post_meta($post_id, '_wpv_settings');
	$view_description = get_post_meta($post_id, '_wpv_description', true);
	?>
	<tr id="wpv_view_list_row_<?php echo $post->ID; ?>" class="js-wpv-view-list-row">
		<td class="post-title page-title column-title">
			<h3 class="row-title">
				<a href="admin.php?page=views-editor&amp;view_id=<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></a>
			</h3>
			<?php if (isset($view_description) && '' != $view_description): ?>
				<p class="desc">
                    <?php echo nl2br($view_description)?>
                </p>
			<?php endif; ?>
		</td>
		<td>
			<?php echo wpv_create_content_summary_for_listing($post->ID); ?>
		</td>
		<td>
			<select class="js-views-actions" name="list_views_action_<?php echo $post->ID; ?>" id="list_views_action_<?php echo $post->ID; ?>" data-view-id="<?php echo $post->ID; ?>">
				<option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
				<option value="delete"><?php _e('Delete','wpv-views') ?></option>
				<option value="duplicate"><?php _e('Duplicate','wpv-views') ?></option>
			</select>
		</td>
		<td>
			<button class="button js-scan-button" data-view-id="<?php echo $post->ID; ?>">
				<?php _e('Scan','wp-views') ?>
			</button>
            <p class="js-nothing-message hidden"><?php _e('Nothing found','wpv-views');?><p>
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