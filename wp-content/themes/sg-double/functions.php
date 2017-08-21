<?php
/**
 * Functions and definitions
 *
 * @package WordPress
 * @subpackage SG Double
 * @since SG Double 1.0
*/

/**
 * SG Double setup.
 *
 * @since SG Double 1.0
 */
 
define( 'SGWINDOWCHILD', 'SGDouble' );
  
function sgdouble_setup() {

	$defaults = sgwindow_get_defaults();

	load_child_theme_textdomain( 'sgdouble', get_stylesheet_directory() . '/languages' );
	
	$args = array(
		'default-image'          => get_stylesheet_directory_uri() . '/img/header.jpg',
		'header-text'            => true,
		'default-text-color'     => sgwindow_text_color( get_theme_mod('color_scheme'), $defaults ['color_scheme'] ),
		'width'                  => sgwindow_get_theme_mod( 'size_image' ),
		'height'                 => sgwindow_get_theme_mod( 'size_image_height' ),
		'flex-height'            => true,
		'flex-width'             => true,
	);
	add_theme_support( 'custom-header', $args );
	
	remove_action( 'sgwindow_empty_sidebar_before_footer-home', 'sgwindow_the_footer_sidebar_widgets', 20 );
	remove_action( 'sgwindow_empty_sidebar_top-home', 'sgwindow_the_top_sidebar_widgets', 20 );
	remove_action( 'sgwindow_empty_column_2-portfolio-page', 'sgwindow_right_sidebar_portfolio', 20 );
	remove_action( 'admin_menu', 'sgwindow_admin_page' );
}
add_action( 'after_setup_theme', 'sgdouble_setup' );

/**
 * SG Double Colors.
 *
 * @since SG Double 1.0
 */
   
function sgdouble_setup_colors() {
	
	/* colors */
	global $sgwindow_colors_class;
	
	$section_id = 'main_colors';
	$section_priority = 10;
	$p = 10;
	
	$i = 'link_color';
	
	$sgwindow_colors_class->add_color($i, $section_id, __('Link', 'sgdouble'), $p++, false);
	$sgwindow_colors_class->set_color($i, 0, '#840a2b');
	$sgwindow_colors_class->set_color($i, 1, '#1d5e1c');
	$sgwindow_colors_class->set_color($i, 2, '#1e73be');
	
	$i = 'heading_color';
	
	$sgwindow_colors_class->add_color($i, $section_id, __('H1-H6 heading', 'sgdouble'), $p++, false);
	$sgwindow_colors_class->set_color($i, 0, '#3f3f3f');
	$sgwindow_colors_class->set_color($i, 1, '#141414');
	$sgwindow_colors_class->set_color($i, 2, '#3f3f3f');
	
	$i = 'heading_link';
	
	$sgwindow_colors_class->add_color($i, $section_id, __('H1-H6 Link', 'sgdouble'), $p++, false);
	$sgwindow_colors_class->set_color($i, 0, '#840a2b');	
	$sgwindow_colors_class->set_color($i, 1, '#b7ba2a');	
	$sgwindow_colors_class->set_color($i, 2, '#1e73be');
	
	$i = 'description_color';
	
	$sgwindow_colors_class->add_color($i, $section_id, __('Description', 'sgdouble'), $p++, false);
	$sgwindow_colors_class->set_color($i, 0, '#ffffff');	
	$sgwindow_colors_class->set_color($i, 1, '#ffffff');
	$sgwindow_colors_class->set_color($i, 2, '#ffffff');			
	
	$i = 'hover_color';
	
	$sgwindow_colors_class->add_color($i, $section_id, __('Link Hover', 'sgdouble'), $p++, false, 'refresh');
	$sgwindow_colors_class->set_color($i, 0, '#1e73be');
	$sgwindow_colors_class->set_color($i, 1, '#1e73be');
	$sgwindow_colors_class->set_color($i, 2, '#1e73be');

}
add_action( 'after_setup_theme', 'sgdouble_setup_colors', 100 );

/**
 * Enqueue parent and child scripts
 *
 * @package WordPress
 * @subpackage SG Double
 * @since SG Double 1.0
*/

function sgdouble_styles() {
    wp_enqueue_style( 'sgwindow-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'sgdouble-style', get_stylesheet_uri(), array( 'sgwindow-style' ) );
	
	wp_enqueue_style( 'sgdouble-colors', get_stylesheet_directory_uri() . '/css/scheme-' . sgwindow_get_theme_mod( 'color_scheme' ) . '.css', array( 'sgdouble-style', 'sgwindow-colors' ) );
}
add_action( 'wp_enqueue_scripts', 'sgdouble_styles' );

/**
 * Set defaults
 *
 * @package WordPress
 * @subpackage SG Double
 * @since SG Double 1.0
*/

function sgdouble_defaults( $defaults ) {

	/* slider defaults */
	$defaults['slider_height'] = '80';
	$defaults['slider_margin'] = '33';
	$defaults['slider_play'] = '1';
	$defaults['slider_content_type'] = '0';
	$defaults['slider_speed'] = '500';
	$defaults['slider_delay'] = '4000';
	
	$defaults['is_thumbnail_empty_icon'] = '';
	
	$defaults['is_cat'] = '1';
	$defaults['is_author'] = '1';
	$defaults['is_date'] = '1';
	$defaults['is_views'] = '';
	$defaults['is_comments'] = '1';
	$defaults['blog_is_cat'] = '1';
	$defaults['blog_is_author'] = '1';
	$defaults['blog_is_date'] = '1';
	$defaults['blog_is_views'] = '';
	$defaults['blog_is_comments'] = '1';
	$defaults['blog_is_entry_meta'] = '';
	
	$defaults['is_sticky_first_menu'] = '';
	$defaults['is_sticky_second_menu'] = '';
	$defaults['site_style'] = 'full';
	$defaults['are_we_saved'] = '';
	
	$defaults['is_defaults_post_thumbnail_background'] = '1';
	$defaults['is_parallax_header'] = '';

	$defaults['is_show_top_menu'] = '';
	$defaults['is_show_secont_top_menu'] = '1';
	$defaults['is_show_footer_menu'] = '';
	$defaults['body_font'] = 'Open Sans';
	$defaults['heading_font'] = 'Open Sans';
	$defaults['header_font'] = 'Allerta Stencil';
	$defaults['column_background_url'] = get_stylesheet_directory_uri() . '/img/back.jpg';
	$defaults['logotype_url'] =  get_stylesheet_directory_uri() . '/img/logo.png';
	$defaults['post_thumbnail_size'] = '730';
	
	$defaults['width_top_widget_area'] = '1366';
	$defaults['width_content_no_sidebar'] = '1366';	
	$defaults['width_content'] = '1366';
	$defaults['width_main_wrapper'] = '1366';
	$defaults['is_home_footer'] = '1';
	$defaults['front_page_style'] = '1';
	
	$defaults['is_header_on_front_page_only'] = '';
	
	/* portfolio: excerpt/content */
	$defaults['portfolio_style'] = 'no_content';
	
	/* Header Image size */
	$defaults['size_image'] = '1680';
	$defaults['size_image_height'] = '200';
	/* Header Image and top sidebar wrapper */
	$defaults['width_image'] = '1680';
		
	$defaults['width_column_1_left_rate'] = '30';
	$defaults['width_column_1_right_rate'] = '30';
	$defaults['width_column_1_rate'] = '22';
	$defaults['width_column_2_rate'] = '30';
	
	$defaults['single_style'] = 'content';

	$defaults['defined_sidebars']['home'] = array(
											'use' => '1', 
											'callback' => 'is_front_page', 
											'param' => '', 
											'title' => __( 'Home', 'sgdouble' ),
											'sidebar-top' => '1',
											'sidebar-before-footer' => '1',
											'column-1' => '1',
											'column-2' => '1', 
											);

	$defaults['footer_text'] = '<a href="' . __( 'http://wordpress.org/', 'sgdouble' ) . '">' . __( 'Powered by WordPress', 'sgdouble' ). '</a> | ' . __( 'theme ', 'sgdouble' ) . '<a href="' .  __( 'http://wpblogs.ru/themes/blog/theme/sg-double/', 'sgdouble') . '">SG Double</a>';
	
	return $defaults;

}
add_filter( 'sgwindow_option_defaults', 'sgdouble_defaults' );

/** Set theme layout
 *
 * @since SG Double 1.0
 */
function sgdouble_layout( $layout ) {
	
	foreach( $layout as $id => $layouts ) {
		if ( 'layout_home' == $layouts['name'] || 'layout_blog' == $layouts['name'] || 'layout_archive' == $layouts['name'] ) {

			$layout[ $id ]['content_val'] = 'default';
			$layout[ $id ]['val'] = 'two-sidebars';
			
		}
	}
	return $layout;
}
add_filter( 'sgwindow_layout', 'sgdouble_layout' );

/**
 * Hook widgets into right sidebar at the front page
 *
 * @package WordPress
 * @subpackage SG Double
 * @since SG Double 1.0
*/

function sgdouble_home_right_column( $layouts ) {

	the_widget( 'WP_Widget_Search', 'title=' );
	the_widget( 'WP_Widget_Categories' );
	the_widget( 'WP_Widget_Tag_Cloud', 'title=' );
	the_widget( 'WP_Widget_Recent_Comments' );
	
}
add_action('sgwindow_empty_column_2-home', 'sgdouble_home_right_column', 20);


/**
 * Add widgets to the right sidebar on portfolio pages
 *
 * @since SG Double 1.0
 */
function sgdouble_right_sidebar_portfolio() {

	the_widget( 'sgwindow_items_portfolio', 'title='.__('Recent Projects', 'sgdouble').
								'&count=8'.
								'&jetpack-portfolio-type=0'.
								'&columns=column-2'.
								'&is_background=1'.
								'&is_margin_0='.
								'&is_link=1'.
								'&effect_id_0=effect-1');
}
add_action('sgwindow_empty_column_2-portfolio-page', 'sgdouble_right_sidebar_portfolio', 20);

/**
 * Add widgets to the top sidebar on the home page
 *
 * @since SG Double 1.0.0
 */
function sgdouble_the_top_sidebar_widgets() {

	the_widget( 'WP_Widget_Search', 'title=' );
	
}
add_action('sgwindow_empty_sidebar_top-home', 'sgdouble_the_top_sidebar_widgets', 20);

/**
 * Hook widgets into right sidebar at the front page
 *
 * @package WordPress
 * @subpackage SG Double
 * @since SG Double 1.0
*/

function sgdouble_home_left_column( $layouts ) {

	the_widget( 'sgwindow_items_category', 'title=' . __('Recent Posts', 'sgdouble').
								'&count=4'.
								'&category=0'.
								'&is_animate='.
								'&is_animate_once='.
								'&columns=column-1'.
								'&is_background=1'.
								'&is_margin_0='.
								'&is_link=1'.
								'&effect_id=effect-9');
}
add_action('sgwindow_empty_column_1-home', 'sgdouble_home_left_column', 20);

//admin page
require get_stylesheet_directory() . '/inc/admin-page.php';