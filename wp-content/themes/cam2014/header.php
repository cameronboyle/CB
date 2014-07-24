<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>
<meta charset="utf-8" />

<!-- Set the viewport width to device width for mobile -->
<meta name="viewport" content="initial-scale=1.0" />

<title><?php wp_title( '|', true, 'right' ); ?></title>

	<script type="text/javascript" src="//use.typekit.net/yra3ptd.js"></script>
  	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
  	<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico" />

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<a name="index"></a>
<div class="fixed">
<div class="contain-to-grid sticky">

<nav class="top-bar" data-topbar>
  <ul class="title-area">
    <li class="name">
    	<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/icon.png"></a>  
    </li>
    <!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
    <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
  </ul>

  <section class="top-bar-section">
    <?php
      // Left Nav Section
      if ( has_nav_menu( 'header-menu-left' ) ) {
          wp_nav_menu( array(
              'theme_location' => 'header-menu-left',
              'container' => false,
              'depth' => 0,
              'items_wrap' => '<ul class="left">%3$s</ul>',
              'fallback_cb' => false,
              'walker' => new cornerstone_walker( array(
                  'in_top_bar' => true,
                  'item_type' => 'li'
              ) ),
          ) );
        }
      ?>

    <?php
      //Right Nav Section
      if ( has_nav_menu( 'header-menu-right' ) ) {
          wp_nav_menu( array(
              'theme_location' => 'header-menu-right',
              'container' => false,
              'depth' => 0,
              'items_wrap' => '<ul class="right">%3$s</ul>',
              'fallback_cb' => false,
              'walker' => new cornerstone_walker( array(
                  'in_top_bar' => true,
                  'item_type' => 'li'
              ) ),
          ) );
        }
      ?>
  </section>
</nav>
</div>
</div>

<div class="row">
	<div id="primary" class="site-content small-12 medium-12 columns">
		<div id="content" role="main">
					<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
					<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
					<img data-interchange="[<?php bloginfo('stylesheet_directory'); ?>/images/line-pine-small.jpg, (only screen and (min-width: 1px))], [<?php bloginfo('stylesheet_directory'); ?>/images/line-pine.jpg, (only screen and (min-width: 1000px))]">

		</div>
	</div>
</div>

