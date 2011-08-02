<?php

function charity_search_form() {
				$search_form_length = apply_filters('charity_search_form_length', '32');
				$search_form = "\n" . "\t";
				$search_form .= '<form id="searchform" method="get" action="' . get_bloginfo('url') .'/">';
				$search_form .= "\n" . "\t" . "\t";
				$search_form .= '<div>';
				$search_form .= "\n" . "\t" . "\t". "\t";
				if (is_search()) {
						$search_form .= '<input id="s" name="s" type="text" value="' . esc_html(stripslashes($_GET['s'])) .'" size="' . $search_form_length . '" tabindex="1" />';
				} else {
						$value = __('To search, type and hit enter', 'charity');
						$value = apply_filters('search_field_value',$value);
						$search_form .= '<input id="s" name="s" type="text" value="' . $value . '" onfocus="if (this.value == \'' . $value . '\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'' . $value . '\';}" size="'. $search_form_length .'" tabindex="1" />';
				}
				$search_form .= "\n" . "\t" . "\t". "\t";

				$search_submit = '<input id="searchsubmit" name="searchsubmit" type="submit" value="' . __('Search', 'charity') . '" tabindex="2" />';

				$search_form .= apply_filters('charity_search_submit', $search_submit);

				$search_form .= "\n" . "\t" . "\t";
				$search_form .= '</div>';

				$search_form .= "\n" . "\t";
				$search_form .= '</form>';

				echo apply_filters('charity_search_form', $search_form);

}

function charity_widgets_array()
{
	// Define array for the widgetized areas
	$charity_widgetized_areas = array(
        'Primary Aside' => array(
			'admin_menu_order' => 100,
			'args' => array (
				'name' => 'Primary Aside',
				'id' => 'primary-aside',
                                'description' => __('The primary widget area, most often used as a sidebar.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_primary_aside',
			'function'		=> 'charity_primary_aside',
			'priority'		=> 10,
			),
		'Secondary Aside' => array(
			'admin_menu_order' => 200,
			'args' => array (
				'name' => 'Secondary Aside',
				'id' => 'secondary-aside',
                'description' => __('The secondary widget area, most often used as a sidebar.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_secondary_aside',
			'function'		=> 'charity_secondary_aside',
			'priority'		=> 10,
			),
			
		'Index Top' => array(
			'admin_menu_order' => 600,
			'args' => array (
				'name' => 'Index Top',
				'id' => 'index-top',
                'description' => __('The top widget area displayed on the index page.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_index_top',
			'function'		=> 'charity_index_top',
			'priority'		=> 10,
			),
		'Index Bottom' => array(
			'admin_menu_order' => 800,
			'args' => array (
				'name' => 'Index Bottom',
				'id' => 'index-bottom',
                'description' => __('The bottom widget area displayed on the index page.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_index_bottom',
			'function'		=> 'charity_index_bottom',
			'priority'		=> 10,
			),
		'Single Top' => array(
			'admin_menu_order' => 900,
			'args' => array (
				'name' => 'Single Top',
				'id' => 'single-top',
                'description' => __('The top widget area displayed on a single post.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_single_top',
			'function'		=> 'charity_single_top',
			'priority'		=> 10,
			),
		'Single Bottom' => array(
			'admin_menu_order' => 1100,
			'args' => array (
				'name' => 'Single Bottom',
				'id' => 'single-bottom',
                'description' => __('The bottom widget area displayed on a single post.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_single_bottom',
			'function'		=> 'charity_single_bottom',
			'priority'		=> 10,
			),
		'Page Top' => array(
			'admin_menu_order' => 1200,
			'args' => array (
				'name' => 'Page Top',
				'id' => 'page-top',
                'description' => __('The top widget area displayed on a page.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_page_top',
			'function'		=> 'charity_page_top',
			'priority'		=> 10,
			),
		'Page Bottom' => array(
			'admin_menu_order' => 1300,
			'args' => array (
				'name' => 'Page Bottom',
				'id' => 'page-bottom',
                'description' => __('The bottom widget area displayed on a page.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_page_bottom',
			'function'		=> 'charity_page_bottom',
			'priority'		=> 10,
			),
		'Footer One' => array(
			'admin_menu_order' => 1301,
			'args' => array (
				'name' => 'Footer One',
				'id' => 'footer-one',
                'description' => __('The first widget area within the footer.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_footer_one',
			'function'		=> 'widget_footer_one',
			'priority'		=> 10,
			),
		'Footer Two' => array(
			'admin_menu_order' => 1302,
			'args' => array (
				'name' => 'Footer Two',
				'id' => 'footer-two',
                'description' => __('The second widget area within the footer.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_footer_two',
			'function'		=> 'widget_footer_three',
			'priority'		=> 10,
			),
		'Footer Three' => array(
			'admin_menu_order' => 1303,
			'args' => array (
				'name' => 'Footer Three',
				'id' => 'footer-three',
                'description' => __('The third widget area within the footer.', 'charity'),
				'before_widget' => charity_before_widget(),
				'after_widget' => charity_after_widget(),
				'before_title' => charity_before_title(),
				'after_title' => charity_after_title(),
				),
			'action_hook'	=> 'widget_area_footer_three',
			'function'		=> 'widget_footer_three',
			'priority'		=> 10,
			),
		);
	
	return apply_filters('charity_widgetized_areas', $charity_widgetized_areas);
	
}

function charity_widgets_init() {

	$charity_widgetized_areas = charity_widgets_array();
	
	if ( !function_exists('register_sidebars') )
			return;

	foreach ($charity_widgetized_areas as $key => $value) {
		register_sidebar($charity_widgetized_areas[$key]['args']);
	}
	  
    // we will check for a Charity widgets directory and and add and activate additional widgets
    // Thanks to Joern Kretzschmar
	  $widgets_dir = @ dir(ABSPATH . '/wp-content/themes/' . get_template() . '/widgets');
	  if ($widgets_dir)	{
		  while(($widgetFile = $widgets_dir->read()) !== false) {
			 if (!preg_match('|^\.+$|', $widgetFile) && preg_match('|\.php$|', $widgetFile))
				  include(ABSPATH . '/wp-content/themes/' . get_template() . '/widgets/' . $widgetFile);
		  }
	  }

	  // we will check for the child themes widgets directory and add and activate additional widgets
    // Thanks to Joern Kretzschmar 
	  $widgets_dir = @ dir(ABSPATH . '/wp-content/themes/' . get_stylesheet() . '/widgets');
	  if ((TEMPLATENAME != THEMENAME) && ($widgets_dir)) {
		  while(($widgetFile = $widgets_dir->read()) !== false) {
			 if (!preg_match('|^\.+$|', $widgetFile) && preg_match('|\.php$|', $widgetFile))
				  include(ABSPATH . '/wp-content/themes/' . get_stylesheet() . '/widgets/' . $widgetFile);
		  }
	  }   

	// Remove WP default Widgets
	// WP 2.8 function using $widget_class
	
    unregister_widget('WP_Widget_Meta');
    unregister_widget('WP_Widget_Search');

	// Finished intializing Widgets plugin, now let's load the charity default widgets
	
	register_widget('THM_Widget_Search');
	register_widget('THM_Widget_Meta');
	register_widget('THM_Widget_RSSlinks');

}

// Runs our code at the end to check that everything needed has loaded
add_action( 'widgets_init', 'charity_widgets_init' );

// Action hook for initializing the preset widgets
function charity_presetwidgets() {
	do_action( 'charity_presetwidgets' );
}

// Initialize the preset widgets
if (function_exists('childtheme_override_init_presetwidgets'))  {
    function charity_init_presetwidgets() {
    	childtheme_override_init_presetwidgets();
    }
} else {
	function charity_init_presetwidgets() {
		update_option( 'widget_search', array( 2 => array( 'title' => '' ), '_multiwidget' => 1 ) );
		update_option( 'widget_pages', array( 2 => array( 'title' => ''), '_multiwidget' => 1 ) );
		update_option( 'widget_categories', array( 2 => array( 'title' => '', 'count' => 0, 'hierarchical' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ) );
		update_option( 'widget_archives', array( 2 => array( 'title' => '', 'count' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ) );
		update_option( 'widget_links', array( 2 => array( 'title' => ''), '_multiwidget' => 1 ) );
		update_option( 'widget_rss-links', array( 2 => array( 'title' => ''), '_multiwidget' => 1 ) );
		update_option( 'widget_meta', array( 2 => array( 'title' => ''), '_multiwidget' => 1 ) );
	}
}
add_action( 'charity_presetwidgets', 'charity_init_presetwidgets' );

// We connect the relevant functions to the action hooks
function charity_connect_functions() {

	$charity_widgetized_areas = charity_widgets_array();

	foreach ($charity_widgetized_areas as $key => $value) {
		if (!has_action($charity_widgetized_areas[$key]['action_hook'], $charity_widgetized_areas[$key]['function'])) {
			add_action($charity_widgetized_areas[$key]['action_hook'], $charity_widgetized_areas[$key]['function'], $charity_widgetized_areas[$key]['priority']);	
		}
	}

}
add_action('template_redirect', 'charity_connect_functions');

// We sort our array of widgetized areas to get a nice list display under wp-admin
function charity_sort_widgetized_areas($content) {
	asort($content);
	return $content;
}
add_filter('charity_widgetized_areas', 'charity_sort_widgetized_areas', 100);

// We start our functions for the widgetized areas here

// Define the Primary Aside 
function charity_primary_aside() {
	if (is_active_sidebar('primary-aside')) {
		echo charity_before_widget_area('primary-aside');
		dynamic_sidebar('primary-aside');
		echo charity_after_widget_area('primary-aside');
	}
}

// Define the Secondary Aside
function charity_secondary_aside() {
	if (is_active_sidebar('secondary-aside')) {
		echo charity_before_widget_area('secondary-aside');
		dynamic_sidebar('secondary-aside');
		echo charity_after_widget_area('secondary-aside');
	}
}

// Define the First Footer Widget Zone
function widget_footer_one() {
	if (is_active_sidebar('footer-one')) {
		echo charity_before_widget_area('footer-one');
		dynamic_sidebar('footer-one');
		echo charity_after_widget_area('footer-one');
	}
}

// Define the Second Footer Widget Zone
function widget_footer_two() {
	if (is_active_sidebar('footer-two')) {
		echo charity_before_widget_area('footer-two');
		dynamic_sidebar('footer-two');
		echo charity_after_widget_area('footer-two');
	}
}

// Define the Third Footer Widget Zone
function widget_footer_three() {
	if (is_active_sidebar('footer-three')) {
		echo charity_before_widget_area('footer-three');
		dynamic_sidebar('footer-three');
		echo charity_after_widget_area('footer-three');
	}
}

// Define the Index Top
function charity_index_top() {
	if (is_active_sidebar('index-top')) {
		echo charity_before_widget_area('index-top');
		dynamic_sidebar('index-top');
		echo charity_after_widget_area('index-top');
	}
}

// Define the Index Bottom
function charity_index_bottom() {
	if (is_active_sidebar('index-bottom')) {
		echo charity_before_widget_area('index-bottom');
		dynamic_sidebar('index-bottom');
		echo charity_after_widget_area('index-bottom');
	}
}

// Define the Single Top
function charity_single_top() {
	if (is_active_sidebar('single-top')) {
		echo charity_before_widget_area('single-top');
		dynamic_sidebar('single-top');
		echo charity_after_widget_area('single-top');
	}
}

// Define the Single Bottom
function charity_single_bottom() {
	if (is_active_sidebar('single-bottom')) {
		echo charity_before_widget_area('single-bottom');
		dynamic_sidebar('single-bottom');
		echo charity_after_widget_area('single-bottom');
	}
}

// Define the Page Top
function charity_page_top() {
	if (is_active_sidebar('page-top')) {
		echo charity_before_widget_area('page-top');
		dynamic_sidebar('page-top');
		echo charity_after_widget_area('page-top');
	}
}

// Define the Page Bottom
function charity_page_bottom() {
	if (is_active_sidebar('page-bottom')) {
		echo charity_before_widget_area('page-bottom');
		dynamic_sidebar('page-bottom');
		echo charity_after_widget_area('page-bottom');
	}
}

// this function returns the opening CSS markup for the widget area 
function charity_before_widget_area($hook) {
	$content =  "\n";
	if ($hook == 'primary-aside') {
		$content .= '<div id="primary" class="aside main-aside">' . "\n";
	} elseif ($hook == 'secondary-aside') {
		$content .= '<div id="secondary" class="aside main-aside">' . "\n";
	} elseif ($hook == '1st-subsidiary-aside') {
		$content .= '<div id="first" class="aside sub-aside">' . "\n";
	} elseif ($hook == '2nd-subsidiary-aside') {
		$content .= '<div id="second" class="aside sub-aside">' . "\n";
	} elseif ($hook == '3rd-subsidiary-aside') {
		$content .= '<div id="third" class="aside sub-aside">' . "\n";
	} else {
		$content .= '<div id="' . $hook . '" class="aside">' ."\n";
	}
	$content .= "\t" . '<ul class="xoxo">' . "\n";
	return apply_filters('charity_before_widget_area', $content);
}

// this function returns the clossing CSS markup for the widget area
function charity_after_widget_area($hook) {
	$content = "\n" . "\t" . '</ul>' ."\n";
	if ($hook == 'primary-aside') {
		$content .= '</div><!-- #primary .aside -->' ."\n";
	} elseif ($hook == 'secondary-aside') {
		$content .= '</div><!-- #secondary .aside -->' ."\n";
	} elseif ($hook == '1st-subsidiary-aside') {
		$content .= '</div><!-- #first .aside -->' ."\n";
	} elseif ($hook == '2nd-subsidiary-aside') {
		$content .= '</div><!-- #second .aside -->' ."\n";
	} elseif ($hook == '3rd-subsidiary-aside') {
		$content .= '</div><!-- #third .aside -->' ."\n";
	} else {
		$content .= '</div><!-- #' . $hook . ' .aside -->' ."\n";
	} 
	return apply_filters('charity_after_widget_area', $content);
}

?>