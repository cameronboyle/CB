<?php

require WPV_PATH_EMBEDDED . '/inc/wpv.class.php';
require_once( WPV_PATH . '/inc/filters/editor-addon-parametric.class.php');

class WP_Views_plugin extends WP_Views {

    function init() {
        add_filter( 'custom_menu_order', array($this, 'enable_custom_menu_order' ));
        add_filter( 'menu_order', array($this, 'custom_menu_order' ));

        global $wp_version;
        if (version_compare($wp_version, '3.3', '>=')) {
            add_action('admin_head-edit.php', array($this, 'admin_add_help')); // TODO review if this is needed here anymore, maybe for Content Templates
            add_action('admin_head-post.php', array($this, 'admin_add_help')); // TODO review if this is needed here anymore, maybe for Content Templates
            add_action('admin_head-post-new.php', array($this, 'admin_add_help')); // TODO review if this is needed here anymore, maybe for Content Templates
            add_action('admin_head', array($this, 'admin_add_help')); // TODO check in what page we are inside that method
        }

        add_action('admin_head-post.php', array($this, 'admin_add_errors')); // TODO review if this is needed here anymore, maybe for Content Templates
        add_action('admin_head-post-new.php', array($this, 'admin_add_errors')); // TODO review if this is needed here anymore, maybe for Content Templates

        parent::init();

        add_action('wp_ajax_wpv_get_types_field_name', array($this, 'wpv_ajax_wpv_get_types_field_name'));
        add_action('wp_ajax_wpv_get_taxonomy_name', array($this, 'wpv_ajax_wpv_get_taxonomy_name'));

        if(is_admin()){ // TODO unify all javascript and CSS register and enqueue
			add_action( 'admin_enqueue_scripts', array( $this,'wpv_admin_enqueue_scripts' ) );
			$this->view_parametric_create();
//            add_action('admin_print_scripts', array($this,'add_views_syntax_highlighting_js'));
  //          add_action('admin_print_scripts', array($this,'add_views_utils'));
		}

        /* Add hooks for Module Manager Integration */
        if (defined('MODMAN_PLUGIN_NAME'))
        {
            add_filter('wpmodules_register_sections', array($this,'register_modules_sections'),20,1);
            add_filter('wpmodules_register_items_'._VIEWS_MODULE_MANAGER_KEY_, array($this,'register_modules_views_items'), 30, 1);
            add_filter('wpmodules_export_items_'._VIEWS_MODULE_MANAGER_KEY_, array($this,'export_modules_views_items'), 10, 2);
            add_filter('wpmodules_import_items_'._VIEWS_MODULE_MANAGER_KEY_, array($this,'import_modules_views_items'), 10, 3);
            add_filter('wpmodules_items_check_'._VIEWS_MODULE_MANAGER_KEY_, array($this,'check_modules_views_items'), 10,1);
            add_filter('wpmodules_register_items_'._VIEW_TEMPLATES_MODULE_MANAGER_KEY_, array($this,'register_modules_view_templates_items'), 20, 1);
            add_filter('wpmodules_export_items_'._VIEW_TEMPLATES_MODULE_MANAGER_KEY_, array($this,'export_modules_view_templates_items'), 10, 2);
            add_filter('wpmodules_import_items_'._VIEW_TEMPLATES_MODULE_MANAGER_KEY_, array($this,'import_modules_view_templates_items'), 10, 3);
            add_filter('wpmodules_items_check_'._VIEW_TEMPLATES_MODULE_MANAGER_KEY_, array($this,'check_modules_view_templates_items'), 10,1);

            //Module manager: Hooks for adding plugin version

            /*Export*/
            add_filter('wpmodules_export_pluginversions_'._VIEWS_MODULE_MANAGER_KEY_,array($this,'modules_views_pluginversion_used'));
            add_filter('wpmodules_export_pluginversions_'._VIEW_TEMPLATES_MODULE_MANAGER_KEY_,array($this,'modules_views_pluginversion_used'));

            /*Import*/
            add_filter('wpmodules_import_pluginversions_'._VIEWS_MODULE_MANAGER_KEY_,array($this,'modules_views_pluginversion_used'));
            add_filter('wpmodules_import_pluginversions_'._VIEW_TEMPLATES_MODULE_MANAGER_KEY_,array($this,'modules_views_pluginversion_used'));

            add_action('view-editor-section-extra', array($this, 'add_view_module_manager_section'), 20, 2);
        }
    }

    function modules_views_pluginversion_used() {

    	if (defined( 'WPV_VERSION' )) {

    		return WPV_VERSION;

    	}

    }

    function register_modules_sections($sections)
    {
        $sections[_VIEW_TEMPLATES_MODULE_MANAGER_KEY_]=array(
           'title'=>__('Content Templates','wpv-views'),
           'icon'=>WPV_URL . '/res/img/icon12.png'
        );

        $sections[_VIEWS_MODULE_MANAGER_KEY_]=array(
           'title'=>__('Views','wpv-views'),
           'icon'=>WPV_URL . '/res/img/icon12.png'
        );
        return $sections;
    }

    function register_modules_views_items($items)
    {
        $views = $this->get_views();

        foreach ($views as $view)
        {		$summary = '';
			$view_settings = get_post_meta($view->ID, '_wpv_settings', true);

			if (!isset($view_settings['view-query-mode'])) { // Old views don't have this setting
				$view_settings['view-query-mode'] = 'normal';
			}

			switch ($view_settings['view-query-mode']) {
				case 'normal':
					$summary .= '<h5>' . __('Content to load', 'wpv-views') . '</h5><p>' . apply_filters('wpv-view-get-content-summary', $summary, $view->ID, $view_settings) .'</p>';
					$summary .= '<h5>' . __('Filter', 'wpv-views') . '</h5>';
					$summary .= wpv_create_summary_for_listing($view->ID);
					break;

				case 'archive':
					$summary .= '<h5>' . __('Content to load', 'wpv-views') . '</h5><p>'. __('This View displays results for an <strong>existing WordPress query</strong>', 'wpv-views') . '</p>';
					break;
			}
            $items[]=array(
                'id'=>_VIEWS_MODULE_MANAGER_KEY_.$view->ID,
                'title'=>$view->post_title,
                'details'=> '<div style="padding:0 5px 5px;">' . $summary . '</div>'
            );
        }
        return $items;
    }

    function export_modules_views_items($res, $items)
    {
	$newitems=array();
        // items is now, whole array, not just IDs
        foreach ($items as $ii=>$item)
        {
            $newitems[$ii]=str_replace(_VIEWS_MODULE_MANAGER_KEY_,'',$item['id']);
        }
        $export_data_pre = wpv_admin_export_selected_data( $newitems, 'view', 'module_manager' );
        $hashes = $export_data_pre['items_hash'];
        foreach ( $items as $jj =>$item ) {
		$id=str_replace(_VIEWS_MODULE_MANAGER_KEY_,'',$item['id']);
		$items[$jj]['hash'] = $hashes[$id];

        }
        return array(
		'xml' => $export_data_pre['xml'],
		'items' => $items
        );
    }

    function import_modules_views_items($result, $xmlstring, $items)
    {
        $result=wpv_admin_import_data_from_xmlstring($xmlstring, $items, 'views');
        if (false===$result || is_wp_error($result))
            return (false===$result)?__('Error during View import','wpv-views'):$result->get_error_message($result->get_error_code());

        return $result;
    }

    function check_modules_views_items( $items )
    {
		foreach ( $items as $key=>$item )
		{
			$view_exists = get_page_by_title( $item['title'], OBJECT, 'view');
			if ( $view_exists )
			{
				$items[$key]['exists'] = true;
				$new_item_export = wpv_admin_export_selected_data( array($view_exists->ID), 'view', 'module_manager');
				$new_item_hash = $new_item_export['items_hash'][$view_exists->ID];
				if ( $new_item_hash != $items[$key]['hash'] ) {
					$items[$key]['is_different'] = true;
					$items[$key]['new_hash'] = $new_item_hash;
					$items[$key]['old_hash'] = $items[$key]['hash'];
				} else {
					$items[$key]['is_different'] = false;
					$items[$key]['new_hash'] = $new_item_hash;
					$items[$key]['old_hash'] = $items[$key]['hash'];
				}
			}
			else
			{
				$items[$key]['exists'] = false;
			}
		}
		return $items;
    }

    function register_modules_view_templates_items($items)
    {
        $viewtemplates = $this->get_view_templates();
		$wpv_options = get_option('wpv_options');

        foreach ($viewtemplates as $view)
        {
			$summary = '';
			$used_as = wpv_get_view_template_defaults($wpv_options, $view->ID);
			if ($used_as != '<div class="view_template_default_box"></div>') {
				$summary .= '<h5>' . __('How this Content Template is used', 'wpv-views') . '</h5><p>' . $used_as . '</p>';
			}
			$fields_used = wpv_get_view_template_fields_list($view->ID);
			if ($fields_used != '<div class="view_template_fields_box"></div>') {
				$summary .= '<h5>' . __('Fields used', 'wpv-views') . '</h5><p>' . $fields_used . '</p>';
			}
			if ( '' == $summary ) $summary = '<p>' . __('Content template', 'wpv-views') . '</p>';
            $items[]=array(
                'id'=>_VIEW_TEMPLATES_MODULE_MANAGER_KEY_.$view->ID,
                'title'=>$view->post_title,
                'details'=>'<div style="padding:0 5px 5px;">' . $summary . '</div>'
            );
        }
        return $items;
    }

    function export_modules_view_templates_items($res, $items)
    {
	$newitems=array();
        // items is now, whole array, not just IDs
        foreach ($items as $ii=>$item)
        {
            $newitems[$ii]=str_replace(_VIEW_TEMPLATES_MODULE_MANAGER_KEY_,'',$item['id']);
        }
        $export_data_pre = wpv_admin_export_selected_data( $newitems, 'view-template', 'module_manager');
        $hashes = $export_data_pre['items_hash'];
        foreach ( $items as $jj =>$item ) {
		$id=str_replace(_VIEW_TEMPLATES_MODULE_MANAGER_KEY_,'',$item['id']);
		$items[$jj]['hash'] = $hashes[$id];
        }
        return array(
		'xml' => $export_data_pre['xml'],
		'items' => $items
        );
    }

    function import_modules_view_templates_items($result, $xmlstring, $items)
    {
        $result=wpv_admin_import_data_from_xmlstring($xmlstring, $items, 'view-templates');
        if (false===$result || is_wp_error($result))
            return (false===$result)?__('Error during Content Template import','wpv-views'):$result->get_error_message($result->get_error_code());

        return $result;
    }

    function check_modules_view_templates_items( $items )
    {
	foreach ( $items as $key=>$item )
	{
		$view_template_exists = get_page_by_title( $item['title'], OBJECT, 'view-template');
		if ( $view_template_exists )
		{
			$items[$key]['exists'] = true;
			$new_item_export = wpv_admin_export_selected_data( array($view_template_exists->ID), 'view-template', 'module_manager');
			$new_item_hash = $new_item_export['items_hash'][$view_template_exists->ID];
			if ( $new_item_hash != $items[$key]['hash'] ) {
				$items[$key]['is_different'] = true;
				$items[$key]['new_hash'] = $new_item_hash;
				$items[$key]['old_hash'] = $items[$key]['hash'];
			} else {
				$items[$key]['is_different'] = false;
			}
		}
		else
		{
			$items[$key]['exists'] = false;
		}
	}
	return $items;
    }

	/**
     * view_parametric_create function.
     *
     * @access public
     * @return void
     */
    function view_parametric_create()
    {

		$this->add_parametric = new Editor_addon_parametric (
            'parametric_filter_create',
			__('Filters', 'wpv-views'),
			WPV_URL . '/res/js/redisign/views_parametric.js',
			false,
            false,
            'icon-filter-mod'
        );

	    $this->edit_parametric = new Editor_addon_parametric (
            'parametric_filter_edit',
	    	__('Edit', 'wpv-views'),
	    	WPV_URL . '/res/js/redisign/views_parametric.js',
	    	false,
            false,
            'icon-edit'
        );

    }

    function enable_custom_menu_order($menu_ord) {
        return true;
    }

    function custom_menu_order( $menu_ord ) {
        $types_index = array_search('wpcf', $menu_ord);
        $views_index = array_search('edit.php?post_type=view', $menu_ord);

        if ($types_index !== false && $views_index !== false) {
            // put the types menu above the views menu.
            unset($menu_ord[$types_index]);
            $menu_ord = array_values($menu_ord);
            array_splice($menu_ord, $views_index, 0, 'wpcf');
        }

        return $menu_ord;
    }

    function is_embedded() {
        return false;
    }

    function wpv_register_type_view()
    {
      $labels = array(
        'name' => _x('Views', 'post type general name'),
        'singular_name' => _x('View', 'post type singular name'),
        'add_new' => _x('Add New View', 'book'),
        'add_new_item' => __('Add New View', 'wpv-views'),
        'edit_item' => __('Edit View', 'wpv-views'),
        'new_item' => __('New View', 'wpv-views'),
        'view_item' => __('View Views', 'wpv-views'),
        'search_items' => __('Search Views', 'wpv-views'),
        'not_found' =>  __('No views found', 'wpv-views'),
        'not_found_in_trash' => __('No views found in Trash', 'wpv-views'),
        'parent_item_colon' => '',
        'menu_name' => 'Views'

      );
      $args = array(
        'labels' => $labels,
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'query_var' => false,
        'rewrite' => false,
        'capability_type' => 'post',
        'can_export' => false,
        'has_archive' => false,
        'hierarchical' => false,
        //'menu_position' => 80,
        'menu_icon' => WPV_URL .'/res/img/views-18.png',
        'supports' => array('title','editor','author')
      );
      register_post_type('view',$args);
    }

    function admin_menu(){ // TODO remove not needed admin pages

		global $pagenow;
		$cap = 'manage_options';

//		add_utility_page(__('Views', 'wpv-views'), __('Views', 'wpv-views'), $cap, 'edit.php?post_type=view', '', WPV_URL .'/res/img/views-18.png');

        // remove the default menus and then add a Help menu
  //      remove_submenu_page('edit.php?post_type=view', 'edit.php?post_type=view');
  //      remove_submenu_page('edit.php?post_type=view', 'post-new.php?post_type=view');

                // Add the default menus after the Help menu
  //      add_submenu_page('edit.php?post_type=view', __('Views', 'wpv-views'), __('Views', 'wpv-views'), $cap, 'edit.php?post_type=view');
  //      add_submenu_page('edit.php?post_type=view', __('New View', 'wpv-views'), __('New View', 'wpv-views'), $cap, 'post-new.php?post_type=view');

        // Add the Content template menus.
  //      add_submenu_page('edit.php?post_type=view', __('Content Templates', 'wpv-views'), __('Content Templates', 'wpv-views'), $cap, 'edit.php?post_type=view-template');
//        add_submenu_page('edit.php?post_type=view', __('New Content Template', 'wpv-views'), __('New Content Template', 'wpv-views'), $cap, 'post-new.php?post_type=view-template');

        // add settings menu.
 //       add_submenu_page('edit.php?post_type=view', __('Settings', 'wpv-views'), __('Settings', 'wpv-views'), $cap, 'views-settings',
   //                 array($this, 'views_settings_admin'));

        // Add import export menu.
        if (function_exists('wpv_admin_menu_import_export')) {
//            add_submenu_page('edit.php?post_type=view', __('Import/Export', 'wpv-views'), __('Import/Export', 'wpv-views'), $cap, 'views-import-export', 'wpv_admin_menu_import_export');
        }

        //   add_submenu_page('edit.php?post_type=view', __('Help', 'wpv-views'), __('Help', 'wpv-views'), $cap , WPV_FOLDER . '/menu/help.php', null, WPV_URL . '/res/img/icon16.png');

     //create new top-level menu
    add_menu_page(__('Views', 'wpv-views'), __('Views', 'wpv-views'), $cap, 'views', 'wpv_admin_menu_views_listing_page',WPV_URL .'/res/img/views-18.png');
    // views_redesign_function() is a placeholder function located in wpv-admin-changes.php
    // create a new submenu for Views listing
    add_submenu_page( 'views', __('Views', 'wpv-views'), __('Views', 'wpv-views'), $cap, 'views', 'wpv_admin_menu_views_listing_page');
    if (isset( $_GET['page'] ) && 'views-editor' == $_GET['page']) {
	add_submenu_page( 'views', __( 'Edit View', 'wpv-views' ), __( 'Edit View', 'wpv-views' ), $cap, 'views-editor', 'views_redesign_html');
    }
    // create a new submenu for Content Templates listing
    add_submenu_page( 'views', __('Content Templates', 'wpv-views'), __('Content Templates', 'wpv-views'), $cap, 'view-templates', 'wpv_admin_menu_content_templates_listing_page');
    if (('post-new.php' == $pagenow) && (isset( $_GET['post_type'] ) && 'view-template' == $_GET['post_type'])) {
	add_submenu_page( 'views', __('New Content Template', 'wpv-views'), __('New Content Template', 'wpv-views'), $cap, 'post-new.php?post_type=view-template');
    }
    // create a new submenu for WordPress Archives listing
    add_submenu_page( 'views', __('WordPress Archives', 'wpv-views'), __('WordPress Archives', 'wpv-views'), $cap, 'view-archives', 'wpv_admin_archive_listing_page');
    if (isset( $_GET['page'] ) && 'view-archives-editor' == $_GET['page']) {
	add_submenu_page( 'views', __( 'Edit WordPress Archive', 'wpv-views' ), __( 'Edit WordPress Archive', 'wpv-views' ), $cap, 'view-archives-editor', 'views_archive_redesign_html');
    }
    // create a new submenu for remaining Settings - TODO adjust javascript and CSS enqueues
    add_submenu_page( 'views', __('Settings', 'wpv-views'), __('Settings', 'wpv-views'), $cap, 'views-settings-new', array($this, 'views_settings_admin'));
    // create a new submenu for Export/Import - TODO check that it works (it seems to work out of the box)
    if (function_exists('wpv_admin_menu_import_export')) {
	add_submenu_page( 'views', __('Import/Export', 'wpv-views'), __('Import/Export', 'wpv-views'), $cap, 'views-import-export', 'wpv_admin_menu_import_export');
    }
    // create a new submenu for Help - TODO check that it works (it seems to work out of the box)
    add_submenu_page( 'views', __('Help', 'wpv-views'), __('Help', 'wpv-views'), $cap, WPV_FOLDER . '/menu/help.php', null);
    }

    function settings_box_load(){ // DEPRECATED, check Module Manager, maybe needed in Content Templates
        add_meta_box('wpv_settings', '<img src="' . WPV_URL . '/res/img/icon16.png" />&nbsp;&nbsp;' . __('View Query - Choose what content to load', 'wpv-views'), array($this, 'settings_box'), 'view', 'normal', 'high');
        add_meta_box('wpv_layout', '<img src="' . WPV_URL . '/res/img/icon16.png" />&nbsp;&nbsp;' . __('View Layout - Edit the layout', 'wpv-views'), 'view_layout_box', 'view', 'normal', 'high');

        add_meta_box('wpv_views_help', '<img src="' . WPV_URL . '/res/img/icon16.png" />&nbsp;&nbsp;' . __('Views Help', 'wpv-views'), array($this, 'view_help_box'), 'view', 'side', 'high');

        global $pagenow;
        if (defined('MODMAN_PLUGIN_NAME') && 'post-new.php'!=$pagenow)
        {
            // module manager sidebar meta box
            add_meta_box('wpv_modulemanager_box',__('Module Manager','wpv-views'),array($this, 'modulemanager_views_box'),'view','side','high');  // this might be DEPRECATED
            add_meta_box('wpv_modulemanager_box',__('Module Manager','wpv-views'),array($this, 'modulemanager_view_templates_box'),'view-template','side','high');
        }
        //add_meta_box('wpv_css', '<img src="' . WPV_URL . '/res/img/icon16.png" />&nbsp;&nbsp;' . __('CSS for view', 'wpv-views'), array($this, 'css_box'), 'view', 'normal', 'high');

        if ($pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'view') {
            $this->include_admin_css();
        }
        if ($pagenow == 'options-general.php' && isset($_GET['page']) && $_GET['page'] == WPV_FOLDER . '/menu/main.php') {
            $this->include_admin_css();
        }
        if ($pagenow == 'options-general.php' && isset($_GET['page']) && $_GET['page'] == 'wpv-import-theme') {
            $this->include_admin_css();
        }


    }

    function hide_view_body_controls() { //DEPRECATED
        global $pagenow, $post;
        if (($pagenow == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'view') ||
                ($pagenow == 'post.php' && isset($_GET['action']) && $_GET['action'] == 'edit')) {

            $post_type = $post->post_type;

            if($pagenow == 'post.php' && $post_type != 'view') {
                return;
            }
            // hide the post body.
            ?>
                <div id="wpv-customize-link" style="display:none;margin-bottom:15px">
                    <a href="#" onclick="wpv_show_post_body()"><?php _e('Fully customize the View HTML output', 'wpv-views'); ?></a>
                    <?php $last_modified = get_post_meta($post->ID, '_wpv_last_modified', true); ?>
                    <input type="hidden" name="full_view" id="full_view" value="<?php echo ( '' != $last_modified ) ? $last_modified : 'no-data'; ?>"/>
                </div>

                <div id="wpv-learn-about-views-editing" style="display:none;margin-bottom:15px">
                    <?php printf(__('Learn about %sediting Views HTML%s', 'wpv-views'),
                                 '<a href="http://wp-types.com/documentation/user-guides/digging-into-view-outputs/" target="_blank">',
                                 ' &raquo;</a>'); ?>
                    <input class="button-secondary" type="button" value="<?php echo __('Hide this editor', 'wpv-views'); ?>" onclick="wpv_hide_post_body()" />
                </div>

                <script type="text/javascript">
                    jQuery('#postdivrich').hide();
                    jQuery('#wpv-learn-about-views-editing').insertAfter('#postdivrich');
                </script>


            <?php

            // hide the author as well.
            ?>
                <script type="text/javascript">
                    jQuery('#authordiv').hide();
                </script>
            <?php

            // add a note about saving changes
            ?>
                <div id="wpv-save-changes" class="wpv_form_notice" style="display:none;margin-top:10px;width:95%">
                    <?php _e('* This View has changed. You need to save for these changes to take effect.', 'wpv-views'); ?>
                </div>

            <?php


        }

    }

    /**
     * Output the view query metabox on the view edit page.
     *
     */

    function settings_box($post){ // DEPRECATED

        global $WPV_view_archive_loop;

        ?>
        <div id="wpv_view_query_controls" style="position: relative">
            <span id="wpv_view_query_controls_over" class="wpv_view_overlay" style="display:none">
                <p><strong><?php echo __('The view query settings will be copied from the original', 'wpv-views'); ?></strong></p>
            </span>
            <?php

            global $wp_version, $pagenow;
            if (version_compare($wp_version, '3.2', '<')) {
                echo '<p style="color:red;"><strong>';
                _e('* Requires WordPress 3.2 or greater for best results.', 'wpv-views');
                echo '</strong></p>';
            }

            $this->include_admin_css();

            wp_nonce_field( 'wpv_get_table_row_ui_nonce', 'wpv_get_table_row_ui_nonce');

            ?>
            <script type="text/javascript">

                var wpv_confirm_filter_change = '<?php _e("Are you sure you want to change the filter?\\n\\nIt appears that you made modifications to the filter.", 'wpv-views'); ?>';
                <?php if ($pagenow == 'post-new.php'): ?>
                    jQuery(document).ready(function($){
                       wpv_add_initial_filter_shortcode();
                    });
                <?php endif; ?>

                var wpv_save_button_text = '<?php _e("Save View", 'wpv-views'); ?>';
            </script>

            <?php

            global $WP_Views;
            $view_settings = $WP_Views->get_view_settings($post->ID);

            // check for creating a new view for an archive loop.

            if (isset($_GET['view_archive']) || isset($_GET['view_archive_taxonomy'])) {
                $view_settings['view-query-mode'] = 'archive';
                global $wpv_wp_pointer;
                $wpv_wp_pointer->add_pointer('View Layout',
                                             'This View displays results for an existing WordPress query.</p><p>Now choose the layout style and then add the fields you wish to display.',
                                             'select[name="_wpv_layout_settings[style]"]',
                                             'bottom',
                                             'wpv_layout');

            }

            ?>

            <p><span style="font-size:1.1em;font-weight:bold;">Does this View query it's own data or replace a standard WordPress archive?</span>&nbsp;&nbsp;&nbsp;<img src="<?php echo WPV_URL_EMBEDDED; ?>/common/res/images/question.png" style="position:relative;top:2px;" />&nbsp;<a href="http://wp-types.com/documentation/user-guides/normal-vs-archive-views/" target="_blank"><?php _e('Learn about Normal and Archive Views &raquo;',
'wpv-views'); ?></a></p>
            <ul style="margin-bottom:10px">
                <?php $checked = $view_settings['view-query-mode'] == 'normal' ? 'checked="checked"' : ''; ?>
                <li><label><input type="radio" name="_wpv_settings[view-query-mode]" value="normal" <?php echo $checked; ?> onclick="jQuery('#wpv-normal-view-mode').show();jQuery('#wpv-archive-view-mode').hide()" />&nbsp;<?php _e('<strong>Normal View:</strong> This View queries content from the database (good for inserting Views into content or widgets)', 'wpv-views'); ?></label></li>
                <?php $checked = $view_settings['view-query-mode'] == 'archive' ? 'checked="checked"' : ''; ?>
                <li><label><input type="radio" name="_wpv_settings[view-query-mode]" value="archive" <?php echo $checked; ?> onclick="jQuery('#wpv-normal-view-mode').hide();jQuery('#wpv-archive-view-mode').show()" />&nbsp;<?php _e('<strong>Archive View:</strong> This View displays results for an existing WordPress query (good for archive pages, taxonomy listing, search, etc.)', 'wpv-views'); ?></label></li>
            </ul>

            <div id="wpv-normal-view-mode"<?php if($view_settings['view-query-mode'] != 'normal') {echo ' style="display:none;"';} ?>>
                <table id="wpv_filter_table" class="widefat fixed">
                    <thead>
                        <tr>
                            <th width="20px"></th>
                            <th width="100%">
								<?php _e('Filter', 'wpv-views'); ?>
								&nbsp;&nbsp<a class="wpv-help-link" target="_blank" href="http://wp-types.com/documentation/user-guides/views/">
									<?php _e('Querying the database', 'wpv-views'); ?>
								</a>
							</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr id="wpv_filter_type">
                            <?php wpv_filter_types_admin($view_settings); ?>
                        </tr>

                        <?php
                            global $view_settings_table_row;
                            $view_settings_table_row = 0;
                            do_action('wpv_add_filter_table_row', $view_settings);
                        ?>

                    </tbody>
                </table>

                <?php
                $view_settings = wpv_types_defaults($view_settings);
                wpv_filter_add_filter_admin($view_settings, null, 'popup_add_filter', '', 'wpv_add_filters', $view_settings['query_type'][0] == 'posts');
                wpv_filter_add_filter_admin($view_settings, null, 'popup_add_filter_taxonomy', '', 'wpv_add_filters_taxonomy', $view_settings['query_type'][0] == 'taxonomy');

				wpv_filter_controls_admin($view_settings);

                ?>
                <p>
                    <span style="font-size:1.1em;font-weight:bold;"><?php _e('Pagination and Sliders settings',
            'wpv-views') ?></span>&nbsp;&nbsp;&nbsp;<img src="<?php echo WPV_URL_EMBEDDED; ?>/common/res/images/question.png" style="position:relative;top:2px;" />&nbsp;<a href="http://wp-types.com/documentation/user-guides/views-pagination/" target="_blank"><?php _e('Everything about Views pagination &raquo;',
            'wpv-views'); ?></a>
                </p>
			    <?php
                wpv_pagination_admin($view_settings);

                wpv_filter_meta_html_admin($view_settings);

                ?>
            </div>

            <?php $WPV_view_archive_loop->view_edit_admin($post->ID, $view_settings); ?>

        </div>
        <?php
    }

   function modulemanager_views_box($post)
   {
        $element=array('id'=>_VIEWS_MODULE_MANAGER_KEY_.$post->ID, 'title'=>$post->post_title, 'section'=>_VIEWS_MODULE_MANAGER_KEY_);
        do_action('wpmodules_inline_element_gui',$element);
   }

   function add_view_module_manager_section($view_settings, $view_id) {
		global $views_edit_help;
		?>
		<div class="wpv-setting-container js-wpv-settings-content">

			<div class="wpv-settings-header">
				<h3>
					<?php _e( 'Module Manager', 'wpv-views' ) ?>
					<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['module_manager']['title'] ?>" data-content="<?php echo $views_edit_help['module_manager']['content'] ?>" ></i>
				</h3>
			</div>

			<div class="wpv-setting">
				<?php
				$element=array('id'=>_VIEWS_MODULE_MANAGER_KEY_.$view_id, 'title'=>get_the_title($view_id), 'section'=>_VIEWS_MODULE_MANAGER_KEY_);
				do_action('wpmodules_inline_element_gui',$element);
				?>
			</div>

		</div>
	   <?php
	}

   function modulemanager_view_templates_box($post)
   {
        $element=array('id'=>_VIEW_TEMPLATES_MODULE_MANAGER_KEY_.$post->ID, 'title'=>$post->post_title, 'section'=>_VIEW_TEMPLATES_MODULE_MANAGER_KEY_);
        do_action('wpmodules_inline_element_gui',$element);
   }

    function view_help_box($post){ // DEPRECATED

        global $pagenow;
        ?>
            <div id="wpv-step-help-1" class="wpv-incomplete-step"><?php _e('1. Enter title', 'wpv-views'); ?></div>
            <div id="wpv-step-help-2" class="wpv-incomplete-step"><?php _e('2. Choose what content to load', 'wpv-views'); ?></div>
            <div id="wpv-step-help-3" class="wpv-incomplete-step"><?php _e('3. Edit the layout', 'wpv-views'); ?></div>

            <?php if($pagenow == 'post-new.php') :?>
                <div id="wpv-step-help-4" class="wpv-incomplete-step"><?php _e('4. Save this View', 'wpv-views'); ?></div>
            <?php else:?>
                <div id="wpv-step-help-4" class="wpv-complete-step"><?php _e('4. Save this View', 'wpv-views'); ?></div>
            <?php endif;?>
            <br />
            <?php printf(__('Learn how to create a View and how to display it in the complete %sViews Guide%s', 'wpv-views'),
                         '<a target=_"blank" href="http://wp-types.com/documentation/user-guides/views/">',
                         ' &raquo;</a>'); ?>

        <?php

    }


    /**
     * save the view settings.
     * Called from a post_save action
     *
     */

    function save_view_settings($post_id){ // DEPRECATED
        global $wpdb, $sitepress;

        list($post_type, $post_status) = $wpdb->get_row("SELECT post_type, post_status FROM {$wpdb->posts} WHERE ID = " . $post_id, ARRAY_N);

        if ($post_type == 'view') {

            if(isset($_POST['_wpv_settings'])){
                $_POST['_wpv_settings'] = apply_filters('wpv_view_settings_save', $_POST['_wpv_settings']);
                update_post_meta($post_id, '_wpv_settings', $_POST['_wpv_settings']);
            }
            save_view_layout_settings($post_id);


            if (isset($sitepress)) {
                if (isset($_POST['icl_trid'])) {
                    // save the post from the edit screen.
                    if (isset($_POST['wpv_duplicate_view'])) {
                        update_post_meta($post_id, '_wpv_view_sync', intval($_POST['wpv_duplicate_view']));
                    } else {
                        update_post_meta($post_id, '_wpv_view_sync', "0");
                    }

                    $icl_trid = $_POST['icl_trid'];
                } else {
                    // get trid from database.
                    $icl_trid = $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id={$post_id} AND element_type = 'post_$post_type'");
                }

                if (isset($_POST['wpv_duplicate_source_id'])) {
                    $source_id = $_POST['wpv_duplicate_source_id'];
                    $target_id = $post_id;
                } else {
                    // this is the source
                    $source_id = $post_id;
                    $target_id = null;
                }

                if ($icl_trid) {
                    $this->duplicate_view($source_id, $target_id, $icl_trid);
                }
            }
            if(isset($_POST['full_view'])) {
		$blogtime = current_time('timestamp');
		$last_saved = date('dmYHi',current_time('timestamp'));
		update_post_meta($post_id, '_wpv_last_modified', $last_saved);
	    }
        }
    }

    function duplicate_view($source_id, $target_id, $icl_trid) { // DEPRECATED,check how to translate Views

        global $wpdb;

        if ($target_id) {
            // we're saving a translation
            // see if we should copy from the original
            $duplicate = get_post_meta($target_id, '_wpv_view_sync', true);
            if ($duplicate === "") {
                // check the original state
                $duplicate = get_post_meta($source_id, '_wpv_view_sync', true);
            }
            if ($duplicate) {
                $view_settings = get_post_meta($source_id, '_wpv_settings', true);
                update_post_meta($target_id, '_wpv_settings', $view_settings);

                $view_layout_settings = get_post_meta($source_id, '_wpv_layout_settings', true);
                update_post_meta($target_id, '_wpv_layout_settings', $view_layout_settings);
            }
        } else {
            // We're saving the original
            // see if we should copy to translations.
            $translations = $wpdb->get_col("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid = {$icl_trid}");

            foreach ($translations as $translation_id) {
                if ($translation_id != $source_id) {
                    $this->duplicate_view($source_id, $translation_id, $icl_trid);
                }
            }
        }

    }

	/**
	 * If the post has a view
	 * add an view edit link to post.
	 */

	function edit_post_link($link, $post_id) {

		if ( !current_user_can( 'manage_options' ) )
			return $link;

        if ($this->current_view) {
			remove_filter('edit_post_link', array($this, 'edit_post_link'), 10, 2);
			ob_start();

			//edit_post_link(__('Edit view', 'wpv-views').' "'.get_the_title($this->current_view).'" ', '', '', $this->current_view);
            echo '<a href="'. admin_url() .'admin.php?page=views-editor&view_id='. $this->current_view .'" title="'.__('Edit view', 'wpv-views').'">'.__('Edit view', 'wpv-views').' "'.get_the_title($this->current_view).'</a>';
			$link = $link . ' ' . ob_get_clean();
			add_filter('edit_post_link', array($this, 'edit_post_link'), 10, 2);
		}
		return $link;
	}

    function admin_add_help() {
        global $pagenow;
        $screen = get_current_screen();

        $help = $this->admin_plugin_help('', $screen->id, $screen);

        if ($help) {
            $screen->add_help_tab(array(
                                    'id' => 'views-help',
                                    'title' => __('Views', 'wpv-views'),
                                    'content' => $help,
                                    ));
        }
    }
    /**
    * Adds help on admin pages.
    *
    * @param type $contextual_help
    * @param type $screen_id
    * @param type $screen
    * @return type
    */
    function admin_plugin_help($contextual_help, $screen_id, $screen) { // TODO review texts for contextual help
        $help = '';
        switch ($screen_id) {
            case 'views_page_view-templates':
                $help = '<p>'.__("Use <strong>Content Templates</strong> to design single pages in your site. ",'wpv-views').'</p>';
                $help .= '<p>'.__("This page lists the <strong>Content Templates</strong> that you have created and allows you to create new ones.",'wpv-views').'</p>';
                $help .= '<p>'.__("The ‘arrange by’ line, at the top of the page, lets you list the <strong>Content Templates</strong> by their name or by how they are used in the site.",'wpv-views').'</p>';
                $help .= '<p>'.__("Click on the name of a Content Template to edit it or create new <strong>Content Templates</strong> using the ‘Add’ buttons.",'wpv-views').'</p>';
                $help .= '<p><a href="http://wp-types.com/documentation/user-guides/view-templates/" target="_blank">'.__("Content Templates online help", 'wpv-views').'</a></p>';
                break;

            case 'view-template':
                $help = '<p>'.__("<strong>Content Templates</strong> let you design single pages in your site.", 'wpv-views').'</p>';
		        $help .= '<p>'.__("To Create a <strong>Content Template</strong>:", 'wpv-views').'</p>';
                $help .= '<ol><li>'.__("Set the title.", 'wpv-views').'</li>';
                $help .= '<li>'.__("Add fields to the body. Use the V icon to insert basic fields, custom fields, taxonomy and Views. If you are using CRED, use the C icon to insert CRED forms.", 'wpv-views').'</li>';
                $help .= '<li>'.__("Style the output by adding HTML around shortcodes.", 'wpv-views').'</li>';
                $help .= '</ol>';
                $help .= '<p><a href="http://wp-types.com/documentation/user-guides/view-templates/" target="_blank">'.__("Content Templates online help", 'wpv-views').'</a></p>';
                break;

            case 'toplevel_page_views':
                $help = '<p>'.__("Use <strong>Views</strong> to load content from the database and display it anyway you choose.",'wpv-views').'</p>';
                $help .= '<p>'.__("This page lists the <strong>Views</strong> in your site. Under ‘actions’, you will find ‘duplicate’ and ‘delete’ for Views.", 'wpv-views').'</p>';
                $help .= '<p>'.__("Click on a <strong>Views</strong> name to edit it or create new Views.", 'wpv-views').'</p>';
                $help .= '<p><a href="http://wp-types.com/documentation/user-guides/views/" target="_blank">'.__("Views online help", 'wpv-views') .'</a></p>';
                break;

            case 'views_page_view-archives':
                $help = '<p>'.__("Use <strong>WordPress Archives</strong> to style and design standard listing and archive pages.",'wpv-views').'</p>';
                $help .= '<p>'.__("This page lists the <strong>WordPress Archives</strong> in your site.", 'wpv-views').'</p>';
                $help .= '<p>'.__("The ‘arrange by’ line, at the top of the page, lets you list the <strong>WordPress Archives</strong> by their name or by how they are used in the site.", 'wpv-views').'</p>';
                $help .= '<p>'.__("Click on the name of a <strong>WordPress Archives</strong> to edit it or create new WordPress Archives using the ‘Add’ buttons.", 'wpv-views').'</p>';
                $help .= '<p><a href="http://wp-types.com/documentation/user-guides/normal-vs-archive-views/" target="_blank">'.__("WordPress Archives online help", 'wpv-views') .'</a></p>';
                break;

            case 'edit-view':
                $help = '<p>'.__("Use <strong>Views</strong> to filter and display lists in complex and interesting ways. Read more about Views in our user guide:",'wpv-views');
                $help .= '<br /><a href="http://wp-types.com/user-guides/views/" target="_blank">http://wp-types.com/user-guides/views/ &raquo;</a></p>';
                $help .= '<p>'.__("This page gives you an overview of the Views you have created.", 'wpv-views').'</p>';
                $help .= '<p>'.__("It has the following options:", 'wpv-views').'</p>';
                $help .= '<ul><li>'.__("<strong>Add New</strong>: Add a New View", 'wpv-views').'</li></ul>';
                $help .= '<p>'.__("If you hover over a View's name you also have these options:", 'wpv-views').'</p>';
                $help .= '<ul><li>'.__("<strong>Edit</strong>: Click to edit the View<br />\n", 'wpv-views').'</li>';
                $help .= '<li>'.__("<strong>Quick Edit</strong>: click to get quick editing options for the View, such as title, slug and date", 'wpv-views').'</li>';
                $help .= '<li>'.__("<strong>Trash</strong>: Move the View to Trash", 'wpv-views').'</li></ul>';
                $help .= '<p>'.sprintf(__("If you need additional help with Content Templates you can visit our <a href='%s' target='_blank'>support forum &raquo;</a>.", 'wpv-views'), WPV_SUPPORT_LINK).'</p>';
                break;

            case 'views_page_view-archives-editor':
                $help = '<p>'.__("<strong>WordPress Archives</strong> let you style and design standard listing and archive pages.",'wpv-views').'</p>';
                $help .= '<p>'.__("To create a <strong>WordPress Archive</strong>:", 'wpv-views').'</p>';
                $help .= '<ol><li>'.__("Set the title", 'wpv-views').'</li>';
                $help .= '<li>'.__("Select on which listing pages it displays", 'wpv-views').'</li>';
                $help .= '<li>'.__("Design the output for the View by inserting fields and styling with HTML", 'wpv-views').'</li></ol>';
                $help .= '<p><a href="http://wp-types.com/documentation/user-guides/normal-vs-archive-views/" target="_blank">'.__("WordPress Archives online help", 'wpv-views') .'</a></p>';
                break;

            case 'view':
            case 'views_page_views-editor':
                $help = '<p>'.__("<strong>Views</strong> load content from the database and display it anyway you choose.",'wpv-views').'</p>';
                $help = '<p>'.__("To make it easier to use Views, we’ve created different preset usage modes for <strong>Views</strong>. Each usage mode emphasizes the features that you need and hides the ones that are not needed.",'wpv-views').'</p>';
                $help = '<p>'.__("You can switch between the different <strong>Views</strong> usage mode by opening the ‘Screen options’ tab.",'wpv-views').'</p>';
                $help .= '<p>'.__("To create a <strong>View</strong>:", 'wpv-views').'</p>';
                $help .= '<ol><li>'.__("Set the title", 'wpv-views').'</li>';
                $help .= '<li>'.__("Select the content to load", 'wpv-views').'</li>';
                $help .= '<li>'.__("Optionally, apply a filter to the query", 'wpv-views').'</li>';
                $help .= '<li>'.__("If needed, enable pagination and front-end filters", 'wpv-views').'</li>';
                $help .= '<li>'.__("Design the output for the View by inserting fields and styling with HTML", 'wpv-views').'</li></ol>';
                $help .= '<p>'.__("When you are done, remember to add the <strong>View</strong> to your content. You can do that by inserting the <strong>View</strong> as a shortcode to content or displaying it as a widget.",'wpv-views').'</p>';
                $help .= '<p><a href="http://wp-types.com/documentation/user-guides/views/" target="_blank">'.__("Views online help", 'wpv-views') .'</a></p>';
                break;

        }

        if ($help != '') {
            return $help;
        } else {
            return $contextual_help;
        }
    }

    // Add important errors right after the View name

    function admin_add_errors() { // TODO check how to add errors now, if needed
    global $post;
    if (empty($post->ID)) {
	return;
    }
    $post_type = $post->post_type;
    if( 'view' != $post_type ) {
	return;
    }
    $last_saved = get_the_modified_time( 'dmYHi' );
    $last_modified = get_post_meta( $post->ID, '_wpv_last_modified', true );
    $view_not_complete = '<div class="wpv_form_errors" style="width:98.7%;">' . sprintf(  esc_js(__( 'This View was not saved correctly. You may need to increase the number of post variables allowed in PHP. <a href="%s">How to increase max_post_vars setting</a>.', 'wpv-views' )), 'http://wp-types.com/faq/why-do-i-get-a-500-server-error-when-editing-a-view/' ) . '</div>';
    ?>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		var last_saved = <?php echo $last_saved; ?>;
		var last_modified = <?php echo ('' != $last_modified) ? $last_modified : '""'; ?>;
		if (!jQuery('#full_view').length || (last_modified.length && last_saved != last_modified)) {
			jQuery('#titlediv').after('<?php echo $view_not_complete; ?>');
		}
	});
	</script>

    <?php
    }

	// Add WPML sync options.

	function language_options() { // DEPRECATED, check how to traslate Views

		global $sitepress, $post;

        if ($post->post_type == 'view') {
            list($translation, $source_id, $translated_id) = $sitepress->icl_get_metabox_states();

            echo '<br /><br /><strong>' . __('Views sync', 'wpv-views') . '</strong>';

            $checked = '';
            if ($translation) {
                if ($translated_id) {
                    $duplicate = get_post_meta($translated_id, '_wpv_view_sync', true);
                    if ($duplicate === "") {
                        // check the original state
                        $duplicate = get_post_meta($source_id, '_wpv_view_sync', true);
                    }
                } else {
                    // This is a new translation.
                    $duplicate = get_post_meta($source_id, '_wpv_view_sync', true);
                }

                if ($duplicate) {
                    $checked = ' checked="checked"';
                }
                echo '<br /><label><input class="wpv_duplicate_from_original" name="wpv_duplicate_view" type="checkbox" value="1" '.$checked . '/>' . __('Duplicate view from original', 'wpml-media') . '</label>';
                echo '<input name="wpv_duplicate_source_id" value="' . $source_id . '" type="hidden" />';
            } else {

                $duplicate = get_post_meta($source_id, '_wpv_view_sync', true);
                if ($duplicate) {
                    $checked = ' checked="checked"';
                }
                echo '<br /><label><input name="wpv_duplicate_view" type="checkbox" value="1" '.$checked . '/>' . __('Duplicate view to translations', 'wpv-views') . '</label>';
            }
        }
	}

	function admin_section_start($title, $help_link = null, $help_text = null) { // DEPRECATED


		if ($help_link) {
			$title .= '<a class="wpv-help-link" href="' . $help_link . '" target="_blank">' . $help_text . '</a>';
		}

		echo $title;

	}

	function admin_section_end() { // DEPRECATED
		?>

		<?php
	}


    function views_settings_admin() { // TODO move to its own file and create own js file too

        global $WPV_templates, $wpdb;

        $options = $this->get_options();

        $defaults = array('views_template_loop_blog' => '0');
        $options = wp_parse_args($options, $defaults);

        ?>

        <div class="wrap">

            <div id="icon-views" class="icon32"><br /></div>
            <h2><?php _e('Views Settings', 'wpv-views') ?></h2>

            <?php $WPV_templates->admin_settings($options); ?>

			<div id="wpv_show_hidden_custom_fields">
				<?php $this->show_hidden_custom_fields($options); ?>
			</div>

            <?php
                // change the preview url when the selector changes.
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    jQuery('.views_template_select').change(function() {

                        var taxonomy;
                        var link;
                        var loop = false;
                        if (jQuery(this).attr('name').substring(0, 20) == 'views_template_loop_') {
                            taxonomy = jQuery(this).attr('name').substring(20);
                            link = jQuery('#views_template_loop_preview_' + taxonomy).attr('href');
                            loop = true;
                        } else {
                            taxonomy = jQuery(this).attr('name').substring(19);
                            link = jQuery('#views_template_for_preview_' + taxonomy).attr('href');
                        }

                        var newAdditionalURL = "";
                        var tempArray = link.split("?");
                        var baseURL = tempArray[0];
                        var aditionalURL = '';
                        if (tempArray.length == 2) {
                            aditionalURL = tempArray[1];
                        }
                        var temp = "";
                        if(aditionalURL) {
                            var tempArray = aditionalURL.split("&");
                            for ( var i in tempArray ){
                                if(tempArray[i].indexOf("view-template") == -1){
                                    newAdditionalURL += temp+tempArray[i];
                                    temp = "&";
                                    }
                                }
                        }
                        var rows_txt = temp+"view-template="+jQuery("#" + jQuery(this).attr('id') + ' option:selected').text();
                        var finalURL = baseURL+"?"+newAdditionalURL+rows_txt;
                        if (loop) {
                            jQuery('#views_template_loop_preview_' + taxonomy).attr('href', finalURL);
                        } else {
                            jQuery('#views_template_for_preview_' + taxonomy).attr('href', finalURL);
                            jQuery('#wpv_diff_template_' + taxonomy).hide();
                        }
                    });
                });
            </script>

        </div>


        <?php
    }

	function wpv_save_theme_debug_settings() {

		global $WPV_templates;

        $options = $this->get_options();

        $defaults = array('views_template_loop_blog' => '0');
        $options = wp_parse_args($options, $defaults);

        if ( ! wp_verify_nonce($_POST['wpv_view_templates_theme_support'], 'wpv_view_templates_theme_support')) die("Security check");

        $options = $WPV_templates->submit($options);

        $this->save_options($options);

        echo 'ok';

	die();
	}

	function wpv_save_wpml_settings() {
		global $WPV_templates;

		$WPV_templates->wpv_save_wpml_settings();
	}

	// called from ajax
	function wpv_get_show_hidden_custom_fields() {

		if ( ! wp_verify_nonce( $_POST['wpv_show_hidden_custom_fields_nonce'], 'wpv_show_hidden_custom_fields_nonce' ) ) die("Security check");

		$options = $this->get_options();

		if (isset($_POST['wpv_show_hidden_fields'])) {
			// save as comma separated string.
			$options['wpv_show_hidden_fields'] = implode(',', $_POST['wpv_show_hidden_fields']);
		} else {
			$options['wpv_show_hidden_fields'] = '';
		}

		$this->save_options($options);

		$this->show_hidden_custom_fields($options);

		die();
	}

	function show_hidden_custom_fields($options) {

		if (isset($options['wpv_show_hidden_fields']) && $options['wpv_show_hidden_fields'] != '') {
			$defaults = explode(',', $options['wpv_show_hidden_fields']);
		} else {
			$defaults = array();
		}

		?>

        <div class="wpv-setting-container">

            <div class="wpv-settings-header">
                <h3><?php _e('Hidden custom fields', 'wpv-views'); ?></h3>
            </div>

            <div class="wpv-setting">

                <div class="js-cf-summary">
                    <?php
                        $cf_exists = false;
                        if ( sizeof($defaults) > 0 ) {
                            $cf_exists = true;
                        }
                    ?>
                    <p class="js-cf-exists-message <?php echo $cf_exists ? '' : 'hidden' ; ?>">
                        <?php _e('The following private custom fields are showing in the Views GUI:', 'wpv-views'); ?>
                    </p>
                    <p class="js-no-cf-message <?php echo $cf_exists ? 'hidden' : '' ; ?>">
                        <?php _e('No private custom fields are showing in the Views GUI.', 'wpv-views'); ?>
                    </p>
                    <ul class="js-selected-cf-list <?php echo $cf_exists ? '' : 'hidden' ; ?>">
                    <?php foreach ($defaults as $cf): ?>
                        <li><?php echo $cf ?></li>
                    <?php endforeach; ?>
                    </ul>

                    <p>
                        <button class="button-secondary js-show-cf-list" type="button"><?php _e('Edit', 'wpv-views'); ?></button>
                        <span class="toolset-alert toolset-alert-success hidden js-cf-update-message">
                            <?php _e( 'Settings saved', 'wpv-views' ); ?>
                        </span>
                    </p>

                </div>

                <div class="js-cf-toggle hidden">

    				<?php $meta_keys = $this->get_meta_keys(true); ?>
                    <ul class="js-all-cf-list cf-list">
    				<?php foreach($meta_keys as $field): ?>
    					<?php if (strpos($field, '_') === 0): ?>
                            <?php
        						$options[$field]['#default_value'] = in_array($field, $defaults);
        						$element = wpv_form_control(array('field' => array(
        							'#type' => 'checkbox',
        							'#name' => 'wpv_show_hidden_fields[]',
        							'#attributes' => array('style' => ''),
        							'#inline' => true,
        							'#title' => $field,
        							'#value' => $field,
        							'#before' => '',
        							'#after' => '',
        							'#default_value' => in_array($field, $defaults)
                                )));
                            ?>
                            <li><?php echo $element ?></li>
                        <?php endif; ?>
    				<?php endforeach; ?>
				<?php wp_nonce_field( 'wpv_show_hidden_custom_fields_nonce', 'wpv_show_hidden_custom_fields_nonce' ); ?>
                    </ul>

    				<p class="update-button-wrap">
                        <span class="js-cf-spinner spinner hidden"></span>
                        <button class="button-secondary js-hide-cf-list"><?php _e('Cancel', 'wpv-views'); ?></button>
                        <button class="button-primary js-save-cf-list"><?php _e('Save', 'wpv-views'); ?></button>
                    </p>

                </div>
            </div>
        </div>

		<?php
	}
	/**
	 * Get the available View in a select box
	 *
	 */

	function get_view_select_box($row, $page_selected, $archives_only = false) {
		global $wpdb, $sitepress;

		static $views_available = null;

		if (!$views_available) {
			$views_available = $wpdb->get_results("SELECT ID, post_title, post_name FROM {$wpdb->posts} WHERE post_type='view' AND post_status='publish'");

            if ($archives_only) {
                foreach ($views_available as $index => $view) {
                    $view_settings = $this->get_view_settings($view->ID);
                    if ($view_settings['view-query-mode'] != 'archive') {
                        unset($views_available[$index]);
                    }
                }
            }

			// Add a "None" type to the list.
			$none = new stdClass();
			$none->ID = '0';
			$none->post_title = __('None', 'wpv-views');
			$none->post_content = '';
			array_unshift($views_available, $none);
		}

        $view_box = '';
		if ($row === '') {
			$view_box .= '<select class="view_select" name="view" id="view">';
		} else {
			$view_box .= '<select class="view_select" name="view_' . $row . '" id="view_' . $row . '">';
		}

        if (function_exists('icl_object_id')) {
            $page_selected = icl_object_id($page_selected, 'view', true);
        }

        foreach($views_available as $view) {

			if (isset($sitepress)) { // TODO maybe DEPRECATED check how to translate Views
				// See if we should only display the one for the correct lanuage.
				$lang_details = $sitepress->get_element_language_details($view->ID, 'post_view');
				if ($lang_details) {
					$translations = $sitepress->get_element_translations($lang_details->trid, 'post_view');
					if (count($translations) > 1) {
						$lang = $sitepress->get_current_language();
						if (isset($translations[$lang])) {
							// Only display the one in this language.
							if ($view->ID != $translations[$lang]->element_id) {
								continue;
							}
						}
					}
				}
			}

            if ($page_selected == $view->ID)
                $selected = ' selected="selected"';
            else
                $selected = '';

			if ($view->post_title) {
				$post_name = $view->post_title;
			} else {
				$post_name = $view->post_name;
			}

			$view_box .= '<option value="' . $view->ID . '"' . $selected . '>' . $post_name . '</option>';

        }
        $view_box .= '</select>';

        return $view_box;
	}

	function wpv_ajax_wpv_get_types_field_name() { // TODO check where this is used, maybe create a function to handle it past wpnonce verification
		if (wp_verify_nonce($_POST['wpv_nonce'], 'wpv_get_types_field_name_nonce')) {
			if (!defined('WPCF_VERSION')) {
				echo json_encode(array('found' => false,
									   'name' => $_POST['field']));
			} else {
			    if (defined('WPCF_INC_ABSPATH')) {
					require_once WPCF_INC_ABSPATH . '/fields.php';
				}

				if (function_exists('wpcf_admin_fields_get_fields')) {
					$fields = wpcf_admin_fields_get_fields();
				} else {
					$fields = array();
				}

				$found = false;
				foreach ($fields as $field) {
					if ($_POST['field'] == wpcf_types_get_meta_prefix($field) . $field['slug']) {
						echo json_encode(array('found' => true,
											   'name' => $field['name']));
						$found = true;
						break;
					}
				}

				if (!$found) {
					echo json_encode(array('found' => false,
										   'name' => $_POST['field']));
				}

			}
		}
		die();
	}

	function wpv_ajax_wpv_get_taxonomy_name() { // TODO check where this is used, maybe create a function to handle it past wpnonce verification
		if (wp_verify_nonce($_POST['wpv_nonce'], 'wpv_get_types_field_name_nonce')) {

			$taxonomies = get_taxonomies('', 'objects');
			if (isset($taxonomies[$_POST['taxonomy']])) {
				echo json_encode(array('found' => false,
								   'name' => $taxonomies[$_POST['taxonomy']]->labels->name));
			} else {
				echo json_encode(array('found' => false,
								   'name' => $_POST['taxonomy']));
			}
		}
		die();
	}

	function wpv_admin_enqueue_scripts($hook) {// echo $hook; TODO this function needs a lot of love
		/**
		* Registering all scripts
		*/

		// Shared ToolSet JS
        //views-colorbox-script
		// External libraries JS
        /*if ( !wp_script_is('toolset-colorbox', 'registered') ){
            wp_register_script( 'toolset-colorbox' , WPV_URL . '/res/js/redesign/lib/jquery.colorbox-min.js', array('jquery'), WPV_VERSION);
        }*/
        wp_deregister_script( 'toolset-colorbox' );
        wp_register_script( 'toolset-colorbox' , WPV_URL . '/res/js/redesign/lib/jquery.colorbox-min.js', array('jquery'), WPV_VERSION);
		wp_register_script( 'views-select2-script' , WPV_URL . '/res/js/redesign/lib/select2/select2.min.js', array('jquery'), WPV_VERSION);
		wp_register_script( 'views-utils-script' , WPV_URL . '/res/js/redesign/utils.js', array('jquery','toolset-colorbox'), WPV_VERSION);

		// CodeMirror

		wp_register_script( 'views-codemirror-script' , WPV_URL . '/res/js/codemirror311/lib/codemirror.js', array(), null, false);
		wp_register_script( 'views-codemirror-overlay-script' , WPV_URL . '/res/js/codemirror311/addon/mode/overlay.js', array('views-codemirror-script'), null, false);
		wp_register_script( 'views-codemirror-xml-script' , WPV_URL . '/res/js/codemirror311/mode/xml/xml.js', array('views-codemirror-overlay-script'), null, false);
		wp_register_script( 'views-codemirror-css-script' , WPV_URL . '/res/js/codemirror311/mode/css/css.js', array('views-codemirror-overlay-script'), null, false);
		wp_register_script( 'views-codemirror-js-script' , WPV_URL . '/res/js/codemirror311/mode/javascript/javascript.js', array('views-codemirror-overlay-script'), null, false);
		wp_register_script( 'views-codemirror-conf-script' , WPV_URL . '/res/js/views_codemirror_conf.js', array('jquery','views-codemirror-script'), null, false);

		// Views & WPA JS

		wp_register_script( 'views-redesign-js', ( WPV_URL . "/res/js/redesign/views_editor.js" ), array( 'jquery','wp-pointer', 'jquery-ui-sortable', 'jquery-ui-draggable', 'views-codemirror-script'), null, true );
		wp_register_script( 'views-redesign-filters-js', ( WPV_URL . "/res/js/redesign/views_section_filters.js" ), array( 'views-redesign-js'), null, true );
		wp_register_script( 'views-redesign-status-js', ( WPV_URL . "/res/js/redesign/views_filter_status.js" ), array( 'views-redesign-filters-js'), null, true );
		wp_register_script( 'views-redesign-author-js', ( WPV_URL . "/res/js/redesign/views_filter_author.js" ), array( 'suggest', 'views-redesign-filters-js'), null, true );
		wp_register_script( 'views-redesign-parent-js', ( WPV_URL . "/res/js/redesign/views_filter_parent.js" ), array( 'views-redesign-filters-js'), null, true );
		wp_register_script( 'views-redesign-search-js', ( WPV_URL . "/res/js/redesign/views_filter_search.js" ), array( 'views-redesign-filters-js'), null, true );
		wp_register_script( 'views-redesign-id-js', ( WPV_URL . "/res/js/redesign/views_filter_id.js" ), array( 'suggest', 'views-redesign-filters-js'), null, true );
		wp_register_script( 'views-redesign-post-relationship-js', ( WPV_URL . "/res/js/redesign/views_filter_post_relationship.js" ), array( 'suggest', 'views-redesign-filters-js'), null, true );
		wp_register_script( 'views-redesign-category-js', ( WPV_URL . "/res/js/redesign/views_filter_category.js" ), array( 'views-redesign-filters-js'), null, true );
		wp_register_script( 'views-redesign-custom-field-js', ( WPV_URL . "/res/js/redesign/views_filter_custom_field.js" ), array( 'views-redesign-filters-js'), null, true );
		wp_register_script( 'views-redesign-taxonomy-term-js', ( WPV_URL . "/res/js/redesign/views_filter_taxonomy_term.js" ), array( 'views-redesign-filters-js'), null, true );
		wp_register_script( 'views-redesign-pagination-js', ( WPV_URL . "/res/js/redesign/views_section_pagination.js" ), array( 'views-redesign-js'), null, true );
		wp_register_script( 'views-redesign-update-js', ( WPV_URL . "/res/js/redesign/views_sections_update.js" ), array( 'views-redesign-js'), null, true );

		wp_register_script( 'views-archive-redesign-js', ( WPV_URL . "/res/js/redesign/views_archive_editor.js" ), array( 'jquery','wp-pointer', 'jquery-ui-sortable', 'jquery-ui-draggable', 'views-codemirror-script'), null, true );
		wp_register_script( 'views-redesign-update-archive-js', ( WPV_URL . "/res/js/redesign/views_archives_sections_update.js" ), array( 'views-archive-redesign-js'), null, true );

		wp_register_script( 'views-redesign-layout-template-js', ( WPV_URL . "/res/js/redesign/views_section_layout_template.js" ), array( 'jquery'), null, true );
		wp_register_script( 'views-redesign-media-manager-js', ( WPV_URL . "/res/js/redesign/views_media_manager.js" ), array( 'jquery'), null, true );
		wp_register_script( 'views-layout-wizard-script' , WPV_URL . '/res/js/redesign/views_layout_edit_wizard.js', array('jquery'), null, true);

        wp_register_script( 'views-template-redesign-js' , WPV_URL . '/res/js/redesign/views_content_template.js', array( 'jquery','wp-pointer', 'jquery-ui-sortable', 'jquery-ui-draggable', 'views-codemirror-script'), null, true);

		// Settings JS

		wp_register_script( 'views-settings-script' , WPV_URL . '/res/js/views_settings.js', array('jquery'), null, true);

		// Listing JS

		wp_register_script( 'views-listing-script' , WPV_URL . '/res/js/redesign/views_listing_page.js', array('jquery'), null, true);
		wp_register_script( 'views-archive-listing-script' , WPV_URL . '/res/js/redesign/views_wordpress_archive_listing_page.js', array('jquery'), null, true);
		wp_register_script( 'views-content-templates-listing-script' , WPV_URL . '/res/js/redesign/wpv_content_template_listing.js', array('jquery'), null, true);

		// NOTE knockout and parametric.js files need to be enqueued at admin_header time for some reason, registered and enqueued in editor-addon-parametric-class.php in common

		/**
		* Registering all styles
		*/

		// Shared ToolSet CSS

		wp_deregister_style( 'toolset-font-awesome' );
		wp_register_style( 'toolset-font-awesome', WPV_URL . '/res/css/font-awesome/css/font-awesome.min.css', array(), WPV_VERSION );

        //TODO, when we will move font-awesome to common
        //if ( !wp_style_is('toolset-font-awesome', 'registered') ){
         //  wp_register_style( 'toolset-font-awesome', WPV_URL . '/res/css/font-awesome/css/font-awesome.min.css', array(), WPV_VERSION );
        //}
        wp_deregister_style( 'toolset-colorbox' );
        wp_register_style( 'toolset-colorbox', WPV_URL . '/res/css/colorbox.css', array(), WPV_VERSION );


		// CodeMirror

		wp_register_style( 'views-codemirror-css' , WPV_URL . '/res/js/codemirror311/lib/codemirror.css', array(), WPV_VERSION);

		// Views CSS
        //if ( !wp_style_is('toolset-colorbox', 'registered') ){
            //wp_register_style( 'toolset-colorbox', WPV_URL . '/res/css/colorbox.css', array(), WPV_VERSION );
        //}
        wp_register_style( 'views-notifications-css', WPV_URL . '/res/css/notifications.css', array(), WPV_VERSION );
        wp_register_style( 'views-dialogs-css', WPV_URL . '/res/css/dialogs.css', array(), WPV_VERSION );
        wp_register_style( 'views-select2-css', WPV_URL . '/res/js/redesign/lib/select2/select2.css', array(), WPV_VERSION );

		wp_register_style( 'views-redesign-css', WPV_URL . '/res/css/views-redesign.css',
		  array('toolset-font-awesome','toolset-colorbox','views-notifications-css','views-dialogs-css','views-select2-css'), WPV_VERSION );



		/**
		* Enqueue actions
		*/

		// Basic WordPress scripts & styles

		wp_enqueue_script('wp-pointer'); // TODO maybe DEPRECATED
		wp_enqueue_style('wp-pointer'); // TODO maybe DEPRECATED
		wp_enqueue_script('thickbox'); // TODO maybe DEPRECATED
		wp_enqueue_style('thickbox'); // TODO maybe DEPRECATED

		// External libraries scripts
		if (isset($_GET['page']) &&
			(
				$_GET['page'] == 'views' || $_GET['page'] == 'view-archives' || $_GET['page'] == 'view-templates' || $_GET['page'] == 'views-editor' || $_GET['page']=='view-archives-editor' || strpos( $_SERVER['QUERY_STRING'], 'help.php') !== false
			)
		) {

                if ($_GET['page'] == 'views-editor')
                    delete_transient('wpv_layout_wizard_save_settings');

		wp_enqueue_script( 'views-select2-script');
		wp_enqueue_script( 'toolset-colorbox');
		wp_enqueue_script( 'views-utils-script');
		$help_box_translations = array(
		'wpv_dont_show_it_again' => __("Got it! Don't show this message again", 'wpv-views'),
		'wpv_close' => __("Close", 'wpv-views')
		);
		wp_localize_script( 'views-utils-script', 'wpv_help_box_texts', $help_box_translations );

		// CodeMirror NOTE add to Views and Content Templates edit screen only

		wp_enqueue_script('views-codemirror-script');
		wp_enqueue_script('views-codemirror-overlay-script');
		wp_enqueue_script('views-codemirror-xml-script');
		wp_enqueue_script('views-codemirror-css-script');
		wp_enqueue_script('views-codemirror-js-script');
		wp_enqueue_script('views-codemirror-conf-script');
		wp_enqueue_style('views-codemirror-css');

		// Shared ToolSet CSS

		wp_enqueue_style( 'toolset-font-awesome' );

		// General Views CSS

		wp_enqueue_style( 'views-redesign-css' );
		// TODO register and enqueue CSS files that are added using @import here

		}

		// Views settings script

		if (isset($_GET['page']) && ($_GET['page'] == 'views-settings' || $_GET['page'] == 'views-settings-new') ) { // TODO rename settings page so -new is not needed anymore
			wp_enqueue_script( 'views-settings-script' );
			wp_enqueue_style( 'views-redesign-css' );
		}

		// Views screens - import/export and help

		if ( $hook == 'views_page_views-import-export' ||
					$hook == 'wp-views/menu/help.php' ||
					$hook == 'views_page_views-settings-new') { // TODO rename settings page so -new is not needed anymore
			wp_enqueue_style( 'views-redesign-css' );
		}

		// Views listing page

		if (isset($_GET['page']) && $_GET['page'] == 'views') {
			wp_enqueue_script( 'views-listing-script' );
		}

		if (isset($_GET['page']) && $_GET['page'] == 'view-archives') {
			wp_enqueue_script( 'views-archive-listing-script' );
		}

		if (isset($_GET['page']) && $_GET['page'] == 'view-templates') {
			wp_enqueue_script( 'views-content-templates-listing-script' );
		}


		// Views and WPA editors

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'views-editor' ) {
			wp_enqueue_script('suggest'); // for author filter
			wp_enqueue_script( 'views-redesign-js' ); // general js file
			wp_enqueue_script( 'views-redesign-filters-js' ); // general js file for filters
			wp_enqueue_script( 'views-redesign-status-js' );
			wp_enqueue_script( 'views-redesign-author-js' );
			wp_enqueue_script( 'views-redesign-parent-js' );
			wp_enqueue_script( 'views-redesign-search-js' );
			wp_enqueue_script( 'views-redesign-id-js' );
			wp_enqueue_script( 'views-redesign-post-relationship-js' );
			wp_enqueue_script( 'views-redesign-category-js' );
			wp_enqueue_script( 'views-redesign-custom-field-js' );
			wp_enqueue_script( 'views-redesign-taxonomy-term-js' );
			wp_enqueue_script( 'views-redesign-pagination-js' );
			$pagination_translation = array(
				'wpv_insert_wrong_cursor_position'		=> __('You need to place your cursor between the [wpv-filter-start] and the [wpv-filter-end] shortcodes', 'wpv-views'),
				'wpv_page_pagination_shortcode_definition'	=> __('This is an optional placeholder to wrap the pagination shortcodes. The content of this shortcode will only be displayed if there is more than one page of results.', 'wpv-views'),
				'wpv_page_num_shortcode_definition'		=> __('Displays the current page number', 'wpv-views'),
				'wpv_page_total_shortcode_definition'		=> __('Displays the maximum number of pages found by the Views Query.', 'wpv-views'),
				'wpv_page_selector_shortcode_definition'	=> __('Displays a pager with the current page selected. Depending on the value of the <em>style</em> parameter it displays a list of links to the other pages or a drop-down list to select another page.', 'wpv-views'),
				'wpv_page_pre_shortcode_definition'		=> __('Display a <em>Previous</em> link to move to the previous page.', 'wpv-views'),
				'wpv_page_next_shortcode_definition'		=> __('Display a <em>Next</em> link to move to the next page.', 'wpv-views')
			);
			wp_localize_script( 'views-redesign-pagination-js', 'wpv_pagination_texts', $pagination_translation );
			wp_enqueue_script( 'views-redesign-update-js' );
			wp_enqueue_script( 'views-redesign-layout-template-js' );
			wp_enqueue_script( 'views-layout-wizard-script' );
			if( function_exists( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
				wp_enqueue_script( 'views-redesign-media-manager-js' );
			}
		}

		if ( isset($_GET['page'] ) && $_GET['page']=='view-archives-editor' ) {
			wp_enqueue_script( 'views-archive-redesign-js' ); // general js file
			wp_enqueue_script( 'views-redesign-update-archive-js' );
			wp_enqueue_script( 'views-redesign-layout-template-js' );
			wp_enqueue_script( 'views-layout-wizard-script' );
			if( function_exists( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
				wp_enqueue_script( 'views-redesign-media-manager-js' );
			}
		}

	}

	function add_views_syntax_highlighting_js() { // DEPRECATED not needed anymore
	//	global $post;
	//	if (isset($post->post_type)) {
	//		if ($post->post_type == 'view' || $post->post_type == 'view-template') {
	//			wp_enqueue_script( 'views-layout-meta-html-codemirror-script' , WPV_URL . '/res/js/codemirror311/lib/codemirror.js', array(), WPV_VERSION);
	//			wp_enqueue_script( 'views-layout-meta-html-codemirror-overlay-script' , WPV_URL . '/res/js/codemirror311/addon/mode/overlay.js', array('views-layout-meta-html-codemirror-script'), WPV_VERSION);
	//			wp_enqueue_script( 'views-layout-meta-html-codemirror-xml-script' , WPV_URL . '/res/js/codemirror311/mode/xml/xml.js', array('views-layout-meta-html-codemirror-overlay-script'), WPV_VERSION);
	//			wp_enqueue_script( 'views-layout-meta-html-codemirror-css-script' , WPV_URL . '/res/js/codemirror311/mode/css/css.js', array('views-layout-meta-html-codemirror-overlay-script'), WPV_VERSION);
	//			wp_enqueue_script( 'views-layout-meta-html-codemirror-js-script' , WPV_URL . '/res/js/codemirror311/mode/javascript/javascript.js', array('views-layout-meta-html-codemirror-overlay-script'), WPV_VERSION);
          //                      wp_enqueue_script( 'views-codemirror-script' , WPV_URL . '/res/js/views_codemirror_conf.js', array('jquery','views-layout-meta-html-codemirror-script'), WPV_VERSION);
		//		wp_enqueue_style( 'views-layout-meta-html-codemirror-css' , WPV_URL . '/res/js/codemirror311/lib/codemirror.css', array(), WPV_VERSION);
		//	}
	//	}
	}

	function add_views_utils() { // DEPRECATED not needed anymore
	//	wp_register_script( 'views-colorbox-script' , WPV_URL . '/res/js/redesign/lib/jquery.colorbox-min.js', array('jquery'), WPV_VERSION);
	//	wp_register_script( 'views-utils-script' , WPV_URL . '/res/js/redesign/utils.js', array('jquery','views-colorbox-script'), WPV_VERSION);
	//	wp_enqueue_script( 'views-colorbox-script');
	//	wp_enqueue_script( 'views-utils-script');

        //TODO: I don't know is it a right place for translations... probably not. NOTE Move it to the general register + enqueue flow
        //$help_box_translations = array(
        //    'wpv_dont_show_it_again' => __("Got it! Don't show this message again", 'wpv-views'),
        //    'wpv_close' => __("Close", 'wpv-views')
        //);
        //wp_localize_script( 'views-utils-script', 'wpv_help_box_texts', $help_box_translations );
	}

}

