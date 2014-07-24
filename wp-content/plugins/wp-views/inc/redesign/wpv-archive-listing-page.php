<?php

function wpv_admin_archive_listing_name($views_ids = array()) {

	global $WPV_view_archive_loop;
	
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
	
	// $wpv_query = new WP_Query( $wpv_args );
	$wpv_count_posts = $wpv_query->post_count;
	$wpv_found_posts = $wpv_query->found_posts;
	if ( $wpv_count_posts > 0 ) {
	?>
	<div class="wpv-views-listing-arrange js-wpv-views-listing-arrange">
		<p><?php _e('Arrange by','wpv-views'); ?>: </p>
		<ul>
		<li data-sortby="name" class="active"><?php _e('Name','wpv-views') ?></li>
		<li data-sortby="usage"><a href="<?php echo admin_url('admin.php'); ?>?page=view-archives&amp;arrangeby=usage"><?php _e('Usage','wpv-views') ?></a></li>
		</ul>
	</div>
				
        <table id="wpv_view_list" class="js-wpv-views-listing wpv-views-listing wpv-views-listing-by-name widefat">
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
			<th><a href="<?php echo admin_url('admin.php'); ?>?page=view-archives&amp;orderby=title&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="title"><?php _e('Title','wpv-views') ?> <i class="icon-sort-by-alphabet<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
                    <th><?php _e('Archive usage','wpv-views') ?></th>
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
			<th><a href="<?php echo admin_url('admin.php'); ?>?page=view-archives&amp;orderby=date&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="date"><?php _e('Date','wpv-views') ?> <i class="icon-sort-by-attributes<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
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
			<th><a href="<?php echo admin_url('admin.php'); ?>?page=view-archives&amp;orderby=title&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="title"><?php _e('Title','wpv-views') ?> <i class="icon-sort-by-alphabet<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
                    <th><?php _e('Archive usage','wpv-views') ?></th>
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
			<th><a href="<?php echo admin_url('admin.php'); ?>?page=view-archives&amp;orderby=date&amp;order=<?php echo $column_sort_to . $mod_url['search'] . $mod_url['items_per_page'] . $paged_url; ?>" class="js-views-list-sort views-list-sort<?php echo $column_active; ?>" data-orderby="date"><?php _e('Date','wpv-views') ?> <i class="icon-sort-by-attributes<?php if ( $column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
                </tr>
            </tfoot>

            <tbody class="js-wpv-views-listing-body">
                <?php
		while ($wpv_query->have_posts()) :
			$wpv_query->the_post();
			$post_id = get_the_id();
			echo wpv_admin_menu_archive_listing_row($post_id);
		endwhile;
		?>
            </tbody>
        </table>
	<?php
	if ( $WPV_view_archive_loop->check_archive_loops_exists() ) { ?>
		<p class="add-new-view js-add-new-view">
			<a class="button js-wpv-views-archive-add-new wpv-views-archive-add-new" data-target="<?php echo admin_url('admin-ajax.php');?>?action=wpv_create_wp_archive_button" href="">
				<i class="icon-plus"></i><?php _e('Add new WordPress Archive','wpv-views') ?>
			</a>
		</p>
	<?php } ?>
	
	<?php
		wpv_admin_listing_pagination( 'view-archives', $wpv_found_posts, $wpv_args["posts_per_page"], $mod_url );
	?>
	
	<?php } else { // No Views matches the criteria ?>
		<div class="wpv-views-listing views-empty-list">
			<p><?php echo __('No WordPress Archives matched your criteria.','wpv-views'); ?> <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=view-archives<?php echo $mod_url['orderby'] . $mod_url['order'] . $mod_url['items_per_page']; ?>&amp;paged=1"><?php _e('Return', 'wpv-views'); ?></a></p>
		</div>
	<?php }
}



function wpv_admin_archive_listing_usage() {
    ?>
        <table id="wpv_view_list_usage" class="js-wpv-views-listing wpv-views-listing wpv-views-listing-by-usage widefat">

            <thead>
                <tr>
                    <th class="js-wpv-col-one"><?php _e('Used for','wpv-views') ?></th>
                    <th class="js-wpv-col-two"><?php _e('Title','wpv-views') ?></th>
                    <th><?php _e('Action','wpv-views') ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="js-wpv-col-one"><?php _e('Used for','wpv-views') ?></th>
                    <th class="js-wpv-col-two"><?php _e('Title','wpv-views') ?></th>
                    <th><?php _e('Action','wpv-views') ?></th>
                </tr>
            </tfoot>

            <tbody class="js-wpv-views-listing-body">

                <?php
                    global $WPV_view_archive_loop;
                    global $WP_Views;

                    $options = $WP_Views->get_options();

                    $loops = $WPV_view_archive_loop->_get_post_type_loops();

                    $pt = get_post_types();

                    $usage_list = array();
                    $taxonomies = get_taxonomies('', 'objects');
                    $exclude_tax_slugs = array();
			$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );
                    foreach ($taxonomies as $category_slug => $category){
			if ( in_array( $category_slug, $exclude_tax_slugs ) ) {
				continue;
			}
			if ( !$category->show_ui ) {
				continue; // Only show taxonomies with show_ui set to TRUE
			}
                        $name = $category->name;
                        $usage_list['view_taxonomy_loop_'.$name] = $category->labels->name;
                    };
                    foreach ($loops as $slug => $name){
                        $usage_list['view_'.$slug] = $name;
                    }

                    foreach ($options as $slug => $name){ // hwy in hell are we adding CT here?
                    //    if (preg_match('/^views_template_for_(.*)/', $slug))
                   //         $usage_list[$slug] = $name;
                    }

                    // var_dump($usage_list);
                    foreach ($usage_list as $key => $value) {

                        //Juan please review this code. This part hide custom post types. Commented.
                        /*if (preg_match('/^view_cpt_(.*)/', $key, $out)) {
                                continue;
                        }*/

                        $loop_name = '';
                        $post = null;
                        if (isset($options[$key])) {

                            $post = get_post($options[$key]);

                            // taxonomy
                            if (preg_match('/^view_taxonomy_loop_(.*)/', $key, $out)) {
                                $args=array(
                                'name' => $out[1]
                                );
                                $taxonomy=get_taxonomies($args, 'objects');
                                $loop_name = $taxonomy[$out[1]]->labels->singular_name;
                            } /*elseif (preg_match('/^views_template_for_(.*)/', $key, $out)) {

                                $pt = get_post_type_object( $out[1] );
                                $loop_name = $pt->labels->singular_name;

                            // loop
                            }*/
                            elseif (preg_match('/^view_(.*)/', $key, $out)) {
                                $loop_name = $loops[$out[1]];
                            }
                        } else {
                            $loop_name = $value;
                        }
                    ?>

                    <tr class="js-wpv-view-list-row">
                    <td>
                        <h3 class="row-title"><?php echo $loop_name ?></h3>
                    </td>
                    <?php if (is_null($post)): ?>
                        <td colspan="3">
                            <a class="button js-create-view-for-archive" data-forwhom="<?php echo $loop_name; ?>" href="#"><i class="icon-plus"></i><?php _e('Create a View for this archive');?></a>
                        </td>
                    <?php else: ?>
                    <td>
                        <a href="admin.php?page=view-archives-editor&view_id=<?php echo $post->ID?>"><?php echo $post->post_title; ?></a>
                    </td>

                    <td>
                        <select class="js-list-views-usage-action" name="list_views_usage_action_<?php echo $post->ID; ?>" id="list_views_usage_action_<?php echo $post->ID; ?>" data-view-id="<?php echo $key; ?>">
                            <option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
                            <option value="change_usage"><?php _e('Change','wpv-views') ?></option>
                        </select>
                    </td>

                    <!--<td>
                            <button class="button js-add-new-content-template" data-id="<?php echo $post->ID; ?>"><i class="icon-exchange"></i>  <?php _e('Change','wp-views') ?></button>
                    </td>-->
                    <?php endif; ?>
                    </tr>
                <?php }

                ?>
            </tbody>
        </table>
        <?php
}

function wpv_admin_archive_listing_page() {
	global $WPV_view_archive_loop;
	?>
	<div class="wrap toolset-views">

        <div class="wpv-views-listing-page wpv-views-listing-archive-page" data-none-message="<?php _e("This View isn't being used for any archive loops.",'wpv-views') ?>">

        	<div id="icon-views" class="icon32"></div>
            <?php $has_items = wpv_check_views_exists('archive'); ?>
        	<h2 class="wpv-page-title">
        		<?php _e('WordPress Archives', 'wpv-views') ?>
        		<?php if ( $WPV_view_archive_loop->check_archive_loops_exists() ) { ?>
        		<a href="#" data-target="<?php echo admin_url('admin-ajax.php');?>?action=wpv_create_wp_archive_button" class="add-new-h2 js-wpv-views-archive-add-new wpv-views-archive-add-new">
				<?php _e('Add new WordPress Archive','wpv-views') ?>
			</a>
			<?php } ?>
        	</h2>

            <?php
            wp_nonce_field( 'work_views_listing', 'work_views_listing' );
            wp_nonce_field( 'wpv_remove_view_permanent_nonce', 'wpv_remove_view_permanent_nonce' );
            
		if ( isset( $_GET["arrangeby"] ) && sanitize_text_field( $_GET["arrangeby"] ) == 'usage' ) {
			wp_nonce_field( 'wpv_wp_archive_arrange_usage', 'wpv_wp_archive_arrange_usage' );
			?>
			<?php if ( !$WPV_view_archive_loop->check_archive_loops_exists() ) {?>
				<p id="js-wpv-no-archive" class="toolset-alert toolset-alert-info">
					<?php _e('All loops have a WordPress Archive assigned','wpv-views'); ?>
				</p>
			<?php } ?>
			<div class="wpv-views-listing-arrange js-wpv-views-listing-arrange">
				<p><?php _e('Arrange by','wpv-views'); ?>: </p>
				<ul>
				<li data-sortby="name"><a href="<?php echo admin_url('admin.php'); ?>?page=view-archives"><?php _e('Name','wpv-views') ?></a></li>
				<li data-sortby="usage" class="active"><?php _e('Usage','wpv-views') ?></li>
				</ul>
			</div>
			<?php
			wpv_admin_wordpress_archives_listing_table_by_usage();
			if ( $WPV_view_archive_loop->check_archive_loops_exists() ) { ?>
				<p class="add-new-view js-add-new-view">
					<a class="button js-wpv-views-archive-add-new wpv-views-archive-add-new" data-target="<?php echo admin_url('admin-ajax.php');?>?action=wpv_create_wp_archive_button" href="admin.php?page=view-archives-new">
						<i class="icon-plus"></i><?php _e('Add new WordPress Archive','wpv-views') ?>
					</a>
				</p>
			<?php }
		} else {
			if ( $has_items ) {
				?>
				<form id="posts-filter" action="" method="get" class="<?php // if ( !$WPV_view_archive_loop->check_archive_loops_exists() ) echo 'hidden'; WHY hide the search when all loops have been asigned? ?>">
					<p class="search-box">
						<label class="screen-reader-text" for="post-search-input"><?php _e('Search WordPress Archives','wpv-views'); ?>:</label>
						<?php $search_term = isset( $_GET["search"] ) ? urldecode( sanitize_text_field($_GET["search"]) ) : ''; ?>
						<input type="search" id="post-search-input" name="search" value="<?php echo $search_term; ?>" />
						<input type="submit" name="" id="search-submit" class="button" value="<?php echo htmlentities( __('Search WordPress Archives','wpv-views'), ENT_QUOTES ); ?>" />
						<input type="hidden" name="paged" value="1" />
					</p>
				</form>
				<?php if ( !$WPV_view_archive_loop->check_archive_loops_exists() ) {?>
					<p id="js-wpv-no-archive" class="toolset-alert toolset-alert-info">
						<?php _e('All loops have a WordPress Archive assigned','wpv-views'); ?>
					</p>
				<?php } ?>
				
				<?php
				
				wpv_admin_wordpress_archives_listing_table_by_name($has_items);
			} else { ?>
				<?php if ( !$WPV_view_archive_loop->check_archive_loops_exists() ) {?>
					<p id="js-wpv-no-archive" class="toolset-alert toolset-alert-info">
						<?php _e('All loops have a WordPress Archive assigned','wpv-views'); ?>
					</p>
				<?php } ?>
				<div class="wpv-view-not-exist js-wpv-view-not-exist">
					<p><?php _e('WordPress Archives let you customize the output of standard Archive pages.');?></p>
					<p>
					<a class="button js-wpv-views-archive-create-new" data-target="<?php echo admin_url('admin-ajax.php');?>?action=wpv_create_wp_archive_button" href="<?php get_admin_url(); ?>admin.php?page=view-archives-new">
						<i class="icon-plus"></i>
						<?php _e('Create your first WordPress Archive');?>
					</a>
					</p>
				</div>
			<?php }
		}
           
		?>

            

        </div> <!-- .wpv-settings-container" -->


	</div>
<?php

}

function wpv_admin_wordpress_archives_listing_table_by_name($has_items) { ?>
	<div id="js-wpv-archive-tables-containter" class="wpv-archive-tables-containter">
		<?php wpv_admin_archive_listing_name($has_items); ?>
	</div>
<?php }

function wpv_admin_wordpress_archives_listing_table_by_usage() {?>
	<div id="js-wpv-archive-tables-containter" class="wpv-archive-tables-containter">
		<?php wpv_admin_archive_listing_usage(); ?>
	</div>
<?php }

function wpv_admin_menu_archive_listing_row($post_id) {
	global $WPV_view_archive_loop;

	ob_start();
	$post = get_post($post_id);
	$meta = get_post_meta($post_id, '_wpv_settings');
	$view_description = get_post_meta($post_id, '_wpv_description', true);
	?>
	<tr id="wpv_view_list_row_<?php echo $post->ID; ?>" class="js-wpv-view-list-row" >
		<td>
    		<h3 class="row-title">
                <?php $post->post_title; ?>
    			<a href="admin.php?page=view-archives-editor&amp;view_id=<?php echo $post->ID; ?>"><?php echo trim($post->post_title); ?></a>
    		</h3>
    		<?php if (isset($view_description) && '' != $view_description): ?>
                <p class="desc">
                    <?php echo nl2br($view_description)?>
                </p>
            <?php endif; ?>
        </td>
		<td>
			<ul class="js-list-views-loops">
			<?php
			global $WP_Views;
			$options = $WP_Views->get_options();

			$loops = $WPV_view_archive_loop->_get_post_type_loops();

			$opt2 = $WPV_view_archive_loop->_view_edit_options($post->ID, $options);

			$selected = '';
			foreach ($loops as $loop => $loop_name) {
				if (isset($options['view_' . $loop]) && $options['view_' . $loop] == $post->ID) {
					$selected .= sprintf(__('<li>%s</li>', 'wpv-views'), $loop_name);
				}
			}
			$taxonomies = get_taxonomies('', 'objects');
			$exclude_tax_slugs = array();
			$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );
			foreach ($taxonomies as $category_slug => $category) {

				if ( in_array( $category_slug, $exclude_tax_slugs ) ) {
					continue;
				}
				if ( !$category->show_ui ) {
					continue; // Only show taxonomies with show_ui set to TRUE
				}

				$name = $category->name;

				if (isset ($options['view_taxonomy_loop_' . $name ]) && $options['view_taxonomy_loop_' . $name ] == $post->ID) {
					$selected .= sprintf(__('<li>%s</li>', 'wpv-views'), $category->labels->name);
				}
			}
			if (!empty($selected)) {
				echo $selected;
			} else {
				echo __("This View isn't being used for any archive loops.",'wpv-views');
			}
			?>
			</ul>
		</td>
		<td>
			<select class="js-list-views-action" name="list_views_action_<?php echo $post->ID; ?>" id="list_views_action_<?php echo $post->ID; ?>" data-view-id="<?php echo $post->ID; ?>">
				<option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
                    <option value="change"><?php _e('Change archive usage','wpv-views') ?></option>
				<option value="delete"><?php _e('Delete','wpv-views') ?></option>
			</select>
		</td>
		<td>
			<?php echo get_the_time(get_option('date_format'), $post->ID); ?>
		</td>
		<!--<td>
			<button class="button js-change-button" data-id="<?php echo $post->ID; ?>"><i class="icon-exchange"></i>  <?php _e('Change','wp-views') ?></button>
		</td>-->
	</tr>

	<?php
	$row = ob_get_contents();
	ob_end_clean();

	return $row;
}