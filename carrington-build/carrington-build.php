<?php
/*
Plugin Name: Carrington Build
Plugin URI: http://crowdfavorite.com
Description: Advanced custom page layouts.
Version: 1.0.1
Author: Crowd Favorite
Author URI: http://crowdfavorite.com/ 
*/

/**
 * @package carrington-build
 *
 * This file is part of Carrington Build for WordPress
 * http://crowdfavorite.com/wordpress/carrington-build/
 *
 * Copyright (c) 2009-2010 Crowd Favorite, Ltd. All rights reserved.
 * http://crowdfavorite.com
 *
 * **********************************************************************
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * **********************************************************************
 */

// Where am I?
	function cfct_where_am_i() {
		$_path = realpath(dirname(__FILE__));
		$loc = null;
		
		switch (true) {
			case strpos($_path, 'mu-plugins') !== false:
				$loc = 'mu-plugins';
				$url = WPMU_PLUGIN_URL;
				$path = WPMU_PLUGIN_DIR;
				break;
			case strpos($_path, 'plugins') !== false:
				$loc = 'plugins';
				$url = WP_PLUGIN_URL;
				$path = WP_PLUGIN_DIR;
				break;
			case strpos($_path, 'themes') !== false:
				$loc = 'theme';
				$url = get_stylesheet_directory_uri();
				$path = get_stylesheet_directory();
				break;
		}
		
		return apply_filters('cfct-build-loc', compact('loc','url','path'));
	}

	$cfct_loc = cfct_where_am_i();

	define('CFCT_BUILD_DIR', apply_filters('cfct-build-dir', trailingslashit($cfct_loc['path']).'carrington-build/'), $cfct_loc['loc']);
	define('CFCT_BUILD_URL', apply_filters('cfct-build-url', trailingslashit($cfct_loc['url']).'carrington-build/'), $cfct_loc['loc']);

// Constants
	define('CFCT_BUILD_VERSION', '1.0.1');
	define('CFCT_BUILD_POSTMETA', '_cfct_build_data');
	define('CFCT_BUILD_TEMPLATES', 'cfct_build_templates');
	define('CFCT_POST_DATA', 'cfct_build');

	define('CFCT_BUILD_DEBUG', false);
	define('CFCT_BUILD_DEBUG_ERROR_LOG', true);
	define('CFCT_BUILD_DEBUG_DISPLAY_ERRORS', false);

// template tag
	function cfct_build() {
		global $cfct_build;
		
		do_action('pre_cfct_build', $cfct_build);
		return $cfct_build->display();
	}

// Init
	function cfct_object_init() {
		global $cfct_build, $post, $post_ID, $pagenow;
		
		// Templates are experimental, enable at your own risk!
		define('CFCT_BUILD_ENABLE_TEMPLATES', apply_filters('cfct-build-enable-templates', false));
				
		if (!defined('CFCT_BUILD_DISABLE') || defined('CFCT_BUILD_DISABLE') && CFCT_BUILD_DISABLE != true) {
			// Includes
			include('lib/cf-json/cf-json.php');
			include('classes/message.class.php');
			include('classes/template.class.php');
			include('classes/common.class.php');
			include('classes/row.class.php');
			include('classes/default-rows.class.php');
			include('classes/module-utility.class.php');
			include('classes/module.class.php');
			include('classes/module-options.php');
			include('classes/default-modules.class.php');
			include('classes/admin.class.php');
			include('classes/build.class.php');
			include('classes/exception.class.php');
			if (CFCT_BUILD_DEBUG) {
				include('classes/debug.class.php');
				include('classes/tests.php');
			}
				
			if (is_admin()) {
				$cfct_build = new cfct_build_admin();
			}
			else {
				$cfct_build = new cfct_build();
				cfct_build_add_filters();
			}			
		}
	}
	add_action('init', 'cfct_object_init', 1);
	
// Readme

	function cfct_readme_menu() {
		if (!defined('CFCT_BUILD_DISABLE') || (defined('CFCT_BUILD_DISABLE') && CFCT_BUILD_DISABLE != true)) {
			global $user_level;
			add_submenu_page('cf-faq', __('Carrington Build FAQ', 'carrington-build') , __('Carrington Build', 'carrington-build'), 'edit_posts', 'cfct-faq', 'cfreadme_show');
			add_action('cfreadme_content', 'cfct_readme_content');
		}
	}
	add_action('admin_menu', 'cfct_readme_menu', 99);

	function cfct_readme_content($content) {
		if ($_GET['page'] == 'cfct-faq') {
			$content = file_get_contents(CFCT_BUILD_DIR.'README.txt');
			$content .= cfct_load_module_readmes();
		}
		return PHP_EOL.$content.PHP_EOL;
	}
	
	function cfct_load_module_readmes() {
		$readme = PHP_EOL.'			
## Included Modules
Carrington Build Ships with the base modules needed to create complex layouts.

---

			'.PHP_EOL;
		$path = trailingslashit(CFCT_BUILD_DIR).'modules';
		if (is_dir($path) && $handle = opendir($path)) {
			while (false !== ($file = readdir($handle))) {
				$path = trailingslashit($path);
				if ($file == '.' || $file == '..') { continue; }
				if (is_dir($path.$file) && is_file($path.$file.'/README.txt')) {
					$readme .= PHP_EOL.file_get_contents($path.$file.'/README.txt').PHP_EOL.'---'.PHP_EOL;
				}
			}
		}
		return $readme;
	}


// Content Output
	function cfct_build_the_post($post) {
		global $cfct_build;
		$cfct_build->_init($post->ID,true);
	}

	function cfct_build_the_content($the_content) {
		global $cfct_build, $post;

		if ( !post_password_required($post) ) {
			if ($html = cfct_build()) {
				$the_content = $html;
			}
		}
		
		return $the_content;
	}
	
	function cfct_build_post_class($class) {
		global $cfct_build;
		if ($cfct_build->can_do_build()) {
			$class[] = 'cfct-can-haz-build';
		}
		return $class;
	}
	
	/**
	 * We need to keep Build from running when WordPress fakes in an excerpt
	 *
	 * @param string $the_excerpt 
	 * @return string
	 */
	function cfct_build_disable($the_excerpt) {
		remove_filter('the_content', 'cfct_build_the_content');
		// remove_filter('get_the_excerpt', 'cfct_build_disable', 1);
		add_filter('get_the_excerpt', 'cfct_build_enable', 99999);
		return $the_excerpt;
	}
	
	/**
	 * Enable Carrington Build via a Post Content filter
	 *
	 * @param string $the_excerpt 
	 * @return void
	 */
	function cfct_build_enable($the_excerpt) {
		cfct_build_add_filters();
		return $the_excerpt;
	}
	
	function cfct_build_add_filters() {
		add_filter('get_the_excerpt', 'cfct_build_disable', 1);
		add_filter('post_class', 'cfct_build_post_class', 10);
		add_filter('the_post', 'cfct_build_the_post');
		// add_filter('the_content', 'cfct_build_the_content', 1);
		add_filter('the_content', 'cfct_build_the_content',10);
	}	
	
// Module Registration
	function cfct_build_register_module($id, $classname, $args = array()) {
		global $cfct_build;
		$cfct_build->template->register_type('module', $id, $classname, $args);
	}
	function cfct_build_deregister_module($id) {
		global $cfct_build;
		$cfct_build->template->deregister_type('module', $id);
	}

// Row Type Registration
	function cfct_build_register_row($id, $classname) {
		global $cfct_build;
		$cfct_build->template->register_type('row', $id, $classname);
	}
	
	function cfct_build_deregister_row($id) {
		global $cfct_build;
		$cfct_build->template->deregister_type('row', $id);
	}
	
// Custom Module Options Registration
	function cfct_module_register_extra($id, $classname) {
		$module_extras = cfct_module_options::get_instance();
		return $module_extras->register($id, $classname);
	}

	function cfct_module_deregister_extra($id, $classname) {
		$module_extras = cfct_module_options::get_instance();
		return $module_extras->deregister($id, $classname);
	}
	
// Common CSS Attribute classes

	/**
	 * Provide a base set of common class names for various uses
	 *
	 * @param string $type - group to return
	 * @return mixed array/bool
	 */
	function cfct_class_groups($type, $defaults=false) {
		static $types;
		$default_styles = array(
			'header' => array(
				'cfct-header-small' => 'Small',
				'cfct-header-medium' => 'Medium',
				'cfct-header-large' => 'Large'
			),
			'content' => array(
				'cfct-content-small' => 'Small',
				'cfct-content-medium' => 'Medium',
				'cfct-content-large' => 'Large'				
			), 
			'image' => array(
				'cfct-image-left' => 'Left',
				'cfct-image-center' => 'Center',
				'cfct-image-right' => 'Right'
			)
		);
		
		if ($defaults) {
			return (!empty($default_styles[$type]) ? $default_styles[$type] : false);			
		}
		else {
			if (is_null($types)) {
				$types = apply_filters('cfct-class-groups', $default_styles);
			}
			return (!empty($types[$type]) ? $types[$type] : false);
		}
	}
	
// Helpers

	/**
	 * Get a list of the Object based Widgets available
	 *
	 * @return array
	 */
	function cfct_get_modern_widgets() {
		if ($widgets = wp_cache_get('cfct_build_modern_widgets', 'cfct_build')) {
			return $widgets;
		}
		
		global $wp_registered_widgets;
		$widgets = array();
		foreach($wp_registered_widgets as $id => $widget) {
			if (!empty($widget['callback']) && $widget['callback'][0] instanceof WP_Widget) {
				$widgets[_get_widget_id_base($id)] = $widget;
			}
		}
		
		$widgets = apply_filters('cfct-modern-widgets', $widgets);
		wp_cache_set('cfct_build_modern_widgets', $widgets, 'cfct_build', 3600);
		
		return $widgets;
	}

	/**
	 * Generic guid creator
	 * @TODO - does 'cfct-' need to come off below?
	 */
	function cfct_build_guid($key, $type='') {
		return 'cfct-'.(!empty($type) ? $type.'-' : '').md5(strval(time()).$key);
	}
	
	/**
     * Sort an array by a key within the array_items
     * Items can be arrays or objects, but must all be the same type
     *
     * @example
     * $array = array(
     * 'mary' => array('age' => 21),
     * 'bob' => array('age' => 5),
     * 'justin' => array('age' => 15)
     * );
     * $array = cf_sort_by_key($array, 'age');
     * # array is now: bob,justin,mary
     *
     * @param $data - the array of items to work on
     * @param $sort_key - an array key or object member to use as the sort key
     * @param $ascending - wether to sort in reverse/descending order
     * @return array - sorted array
	 */
	function cfct_array_sort_by_key($data, $sort_key, $ascending=true) {
		$order = $ascending ? '$a, $b' : '$b, $a';
		if (is_object(current($data))) { $callback = create_function($order, 'return strnatcasecmp($a->'.$sort_key.', $b->'.$sort_key.');'); }
		else { $callback = create_function($order, 'return strnatcasecmp($a["'.$sort_key.'"], $b["'.$sort_key.'"]);'); }
		uasort($data, $callback);
		return $data;
	}

// Upgrade

	function cfct_upgrade_postmeta() {
		global $wpdb;
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->postmeta} WHERE meta_key='".CFCT_BUILD_POSTMETA."'";
		$result = mysql_query($query, $wpdb->dbh);
		
		if ($result != false) {
			$updated = 0;
			while ($row = mysql_fetch_assoc($result)) {
				$cfct_data = unserialize($row['meta_value']);
				// only upgrade if old postmeta 
				if (!isset($cfct_data['data']['blocks'])) {
					// convert
					$modules = array();
					$blocks = array();
					foreach ($cfct_data['data'] as $b_key => $block) {
						$blocks[$b_key] = array();
						foreach ($block as $m_key => $module) {
							unset($module['row_id']);
							$blocks[$b_key][] = $m_key;
							$modules[$m_key] = $module;
						}
					}
					$cfct_data['data'] = array(
						'blocks' => $blocks,
						'modules' => $modules
					);
					
					// update
					$query = 'UPDATE '.$wpdb->postmeta.' 
							SET meta_value="'.$wpdb->escape(serialize($cfct_data)).'" 
							WHERE post_id="'.$row['post_id'].'" 
							AND meta_key="'.CFCT_BUILD_POSTMETA.'"';
					if (mysql_query($query, $wpdb->dbh) == false) {
						echo mysql_error($wpdb->dbh);
						exit;
					}
					else {
						$updated++;
					}
				}
			}
		}
		
		$f = mysql_query("SELECT FOUND_ROWS() as rows", $wpdb->dbh);
		$found = mysql_fetch_assoc($f);
		echo 'updated '.$updated.' rows out of '.$found['rows'].' rows found';
		exit;
	}

	if (is_admin() && !empty($_GET['cfct-upgrade-postmeta'])) {
		add_action('init', 'cfct_upgrade_postmeta');
	}
	
// Debug

	/**
	 * log message to the debugger
	 *
	 * @param string $method - method logging the message
	 * @param string $message - log message
	 * @return bool
	 */
	function cfct_dbg($method, $message) {
		if (!CFCT_BUILD_DEBUG) { return false; }
		if (class_exists('cfct_build_debug')) {
			return cfct_build_debug::log($method, $message);
		}
	}
?>