<?php
get_header();
?>
<a name="something"></a>


<?php
$args = array(
	'sort_order' => 'ASC',
	'sort_column' => 'post_date', //post_title
	'hierarchical' => 1,
	'exclude' => '',
	'child_of' => 0,
	'parent' => -1,
	'exclude_tree' => '',
	'number' => '',
	'offset' => 0,
	'post_type' => 'page',
	'post_status' => 'publish'
);
$pages = get_pages($args);
//start loop
foreach ($pages as $page_data) {
    $content = apply_filters('the_content', $page_data->post_content);
    $title = $page_data->post_title;
    $slug = $page_data->post_name;
?>

<div class="row">
	<div id="primary" class="site-content small-12 medium-12 columns">
		
		
		<div class="section">
		
					<div id="content" role="main">
			
							<div class='<?php echo "$slug" ?>'><a id='<?php echo "$slug" ?>'></a>
							       
							        <h5 class="page-title"><?php echo "$title" ?></h5>
										<?php echo "$content" ?>
										
							</div>

					</div>
		</div>

<hr>

	</div>
</div>

<?php
}
get_footer();
?>