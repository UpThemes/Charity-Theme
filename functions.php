<?php

// Getting Theme and Child Theme Data
// Credits: Joern Kretzschmar

$themeData = get_theme_data(TEMPLATEPATH . '/style.css');
$thm_version = trim($themeData['Version']);
if(!$thm_version)
    $thm_version = "unknown";

$ct=get_theme_data(STYLESHEETPATH . '/style.css');
$templateversion = trim($ct['Version']);
if(!$templateversion)
    $templateversion = "unknown";

// set theme constants
define('THEMENAME', $themeData['Title']);
define('THEMEAUTHOR', $themeData['Author']);
define('THEMEURI', $themeData['URI']);
define('CHARITYVERSION', $thm_version);

// set child theme constants
define('TEMPLATENAME', $ct['Title']);
define('TEMPLATEAUTHOR', $ct['Author']);
define('TEMPLATEURI', $ct['URI']);
define('TEMPLATEVERSION', $templateversion);


// set feed links handling
// If you set this to TRUE, charity_show_rss() and charity_show_commentsrss() are used instead of add_theme_support( 'automatic-feed-links' )
if (!defined('CHARITY_COMPATIBLE_FEEDLINKS')) {	
	if (function_exists('comment_form')) {
		define('CHARITY_COMPATIBLE_FEEDLINKS', false); // WordPress 3.0
	} else {
		define('CHARITY_COMPATIBLE_FEEDLINKS', true); // below WordPress 3.0
	}
}

// set comments handling for pages, archives and links
// If you set this to TRUE, comments only show up on pages with a key/value of "comments"
if (!defined('CHARITY_COMPATIBLE_COMMENT_HANDLING')) {
	define('CHARITY_COMPATIBLE_COMMENT_HANDLING', false);
}

// set body class handling to WP body_class()
// If you set this to TRUE, Charity will use charity_body_class instead
if (!defined('CHARITY_COMPATIBLE_BODY_CLASS')) {
	define('CHARITY_COMPATIBLE_BODY_CLASS', false);
}

// set post class handling to WP post_class()
// If you set this to TRUE, Charity will use charity_post_class instead
if (!defined('CHARITY_COMPATIBLE_POST_CLASS')) {
	define('CHARITY_COMPATIBLE_POST_CLASS', false);
}
// which comment form should be used
if (!defined('CHARITY_COMPATIBLE_COMMENT_FORM')) {
	if (function_exists('comment_form')) {
		define('CHARITY_COMPATIBLE_COMMENT_FORM', false); // WordPress 3.0
	} else {
		define('CHARITY_COMPATIBLE_COMMENT_FORM', true); // below WordPress 3.0
	}
}

// Check for WordPress mu or WordPress 3.0
define('CHARITY_MB', function_exists('get_blog_option'));

// Create the feedlinks
if (!(CHARITY_COMPATIBLE_FEEDLINKS)) {
	add_theme_support( 'automatic-feed-links' );
}

// Check for WordPress 2.9 add_theme_support()
if ( apply_filters( 'charity_post_thumbs', TRUE) ) {
	if ( function_exists( 'add_theme_support' ) )
	add_theme_support( 'post-thumbnails' );
}

// Load jQuery
wp_enqueue_script('jquery');

// Path constants
define('THEMELIB', TEMPLATEPATH . '/library');

// Bootstrap UpThemes Framework
require_once('admin/admin.php');

// Bootstrap Carrington Build
if ( file_exists( get_template_directory() . '/carrington-build/carrington-build.php' ) ) {
	require_once( 'carrington-build/carrington-build.php' );
}

// Load widgets
require_once(THEMELIB . '/extensions/widgets.php');

// Load custom header extensions
require_once(THEMELIB . '/extensions/header-extensions.php');

// Load custom content filters
require_once(THEMELIB . '/extensions/content-extensions.php');

// Load custom Comments filters
require_once(THEMELIB . '/extensions/comments-extensions.php');
 
// Load custom discussion filters
require_once(THEMELIB . '/extensions/discussion-extensions.php');

// Load custom Widgets
require_once(THEMELIB . '/extensions/widgets-extensions.php');

// Load the Comments Template functions and callbacks
require_once(THEMELIB . '/extensions/discussion.php');

// Load custom sidebar hooks
require_once(THEMELIB . '/extensions/sidebar-extensions.php');

// Load custom footer hooks
require_once(THEMELIB . '/extensions/footer-extensions.php');

// Add Dynamic Contextual Semantic Classes
require_once(THEMELIB . '/extensions/dynamic-classes.php');

// Need a little help from our helper functions
require_once(THEMELIB . '/extensions/helpers.php');

// Load shortcodes
require_once(THEMELIB . '/extensions/shortcodes.php');

// Load featured image slider
require_once(THEMELIB . '/featured-images/featured-images.php');

// Adds filters for the description/meta content in archives.php
add_filter( 'archive_meta', 'wptexturize' );
add_filter( 'archive_meta', 'convert_smilies' );
add_filter( 'archive_meta', 'convert_chars' );
add_filter( 'archive_meta', 'wpautop' );

// Remove the WordPress Generator - via http://blog.ftwr.co.uk/archives/2007/10/06/improving-the-wordpress-generator/
function charity_remove_generators() { return ''; }
if (apply_filters('charity_hide_generators', TRUE)) {  
    add_filter('the_generator','charity_remove_generators');
}

// Translate, if applicable
load_theme_textdomain('charity', THEMELIB . '/languages');

$locale = get_locale();
$locale_file = THEMELIB . "/languages/$locale.php";
if ( is_readable($locale_file) )
	require_once($locale_file);

wp_register_script( 'hoverIntent', get_bloginfo('template_url') . '/library/scripts/hoverIntent.js', array('jquery') );
wp_register_script( 'jquery.easing', get_bloginfo('template_url') . '/library/scripts/jquery.easing.1.3.js', array('jquery') );

add_action('init','theme_init');

function theme_init(){
	
	global $up_options;
	
	if(!is_admin()){
	
		wp_enqueue_script( 'superfish', get_bloginfo('template_url') . '/library/scripts/superfish.js', array('jquery','hoverIntent') );
		wp_enqueue_script( 'global', get_bloginfo('template_url') . '/library/scripts/global.js', array('jquery','hoverIntent','superfish') );
		wp_enqueue_style( 'superfish-styles', get_bloginfo('template_url') . '/library/styles/superfish.css' );


		if(isset($_REQUEST['style'])):
			$theme_color = $_REQUEST['style'];
			$_COOKIE['style'] = $theme_color;
		elseif($_COOKIE['style'] && ($_COOKIE['style']=='dark' || $_COOKIE['style']=='default')):
			$theme_color = $_COOKIE['style'];
		elseif($up_options->style):
			$theme_color = $up_options->style;
		endif;
		
		if( $theme_color == 'dark' )
			wp_enqueue_style( 'style-dark', get_bloginfo('template_url') . '/style-dark.css' );
	
	}
	
}

function head_styles(){
	
	global $up_options;
	
	$styles = '<style type="text/css">';
	
	if ( $up_options->linkcolor )
		$styles .= 'a,a:link,a:visited{ color: ' . $up_options->linkcolor . '; }';

	if ( $up_options->hovercolor )
		$styles .= 'a:hover{ color: ' . $up_options->hovercolor . '; }';
	
	if ( $up_options->activecolor )
		$styles .= 'a:active{ color: ' . $up_options->activecolor . '; }';
		
	if	( $up_options->header_image )
		$styles .= '#header{ background-image: url(' . $up_options->header_image . '); }';
	
	$styles .= '</style>';
	
	echo $styles;
	
}

add_action('wp_head','head_styles');

function above_header_ads(){
	global $up_options; ?>
    <div id="header_ads" class="advertisement">
    	<?php echo $up_options->top_ads; ?>
    </div>
    <?php
}

function below_header_ads(){
	global $up_options; ?>
    <div id="footer_ads" class="advertisement">
    	<?php echo $up_options->bottom_ads; ?>
    </div>
    <?php
}

function check_for_ads(){
	
	global $up_options;
	
	if( $up_options->top_ads )
		add_action('charity_belowheader','above_header_ads');

	if( $up_options->bottom_ads )
		add_action('charity_abovefooter','below_header_ads');

}

add_action('init','check_for_ads');

function charity_post_meta( $content ){
    global $post;
	
	if( get_post_type() == 'post' || is_single() ):
		
		$meta = '<div class="article">
					<div class="meta">
						<span class="time">'.get_the_date().'</span>
		';
		$tags = get_the_tags($post->ID);
		if(is_array($tags)):
			foreach ($tags as $tag):
				$tag_list .= '<a href="'.get_tag_link($tag->term_id).'">'.$tag->name.'</a> ';
			endforeach;
			$meta .= '
					<p class="tags">
						'.$tag_list.'
					</p>
					';      
		endif;
		$meta .= '</div></div>';
	
	else:
	
		$meta = '';
	
	endif;
	
	return $meta . $content;
	    
}
add_filter('the_content', 'charity_post_meta');

?>