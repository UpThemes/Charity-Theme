<?php

/**
 * Utility class that will be extended by cfct_build_module
 * Provides helper functions for common actions, inputs, fields, etc.
 */
class cfct_build_module_utility {
	public function __construct() {
		add_action('wp_ajax_cfct_module_ajax', array($this, '_handle_requests'));		
	}
	
	/**
	 * Global ajax request handler for utility class provided methods
	 */
	public function _handle_requests() {
		if (!empty($_POST['cf_id_base']) && $_POST['cf_id_base'] == $this->id_base && !empty($_POST['cf_action'])) {
			switch($_POST['cf_action']) {
				case 'cf-global-image-search':
					$this->_global_image_search();
					break;
			}
		}
	}

// Module Admin Tabs

	/**
	 * Standard module tabs
	 * 
	 * $tabs = array( 'tab_id' => 'title' );
	 * 	- tab_id: id of target div to toggle
	 * 	- title: user friendly tab display name
	 *
	 * The selected visible tab will have a `class="active"` attribute
	 * Your tab markup should be:
	 *		<div class="cfct-module-tab-contents">
	 *			<div id="tab-one" class="active">...</tab>
	 *			<div id="tab-two">...</tab>
	 *		</div>
	 *
	 * @param string $tabs_id
	 * @param array $tabs
	 * @param $active_tab
	 * @return string HTML
	 */
	protected function cfct_module_tabs($tabs_id, $tabs = array(), $active_tab = null) {
		$html = '';
		if (count($tabs)) {
			$html = '
				<div id="'.$tabs_id.'" class="cfct-module-tabs">
					<ul>';
			$i = 0;
			foreach ($tabs as $tab_id => $title) {
				$active = ((!empty($active_tab) && $active_tab == $tab_id) || empty($active_tab) && ++$i == 1 ? ' class="active"' : '');
				$html .= '
						<li'.$active.'><a href="#'.$tab_id.'">'.$title.'</a></li>';
			}
			$html .= '
					</ul>
				</div>
			';
		}
		return $html;
	}	

	/**
	 *	Add this JS to your module's addModuleSaveCallback JavaScript
	 */
	protected function cfct_module_tabs_js() {
		return '
			$(".cfct-module-tabs a").click(function(){
				var _this = $(this);
				if (!_this.parent("li").hasClass("active")) {
					_this.parent("li").addClass("active").siblings().removeClass("active");
					// thank IE for this next line
					var hash = _this.attr("href").slice(_this.attr("href").indexOf("#"));
					$(hash).addClass("active").siblings().removeClass("active");
				}
				return false;
			});
		';
	}

// Custom Layout
	
	/**
	 * generic layout controls for header-size, content-size and image-alignment
	 *
	 * $controls =  = array('header', 'content', 'image'); or any variation on that combo
	 *
	 * @param array $controls - array of control items to output
	 * @param array $data - module data
	 * @return string HTML
	 */
	function post_layout_controls($controls, $data) {
		$html = '
			<div class="'.$this->id_base.'-col-b cfct-post-layout-controls">';
		if (in_array('header', $controls)) {
			$html .= '
				<p class="cfct-style-title-chooser">'.$this->custom_css_dropdown('style-title', __('Header Size', 'carrington-build'), 'header', $data).'</p>';
		}
		if (in_array('image', $controls)) {
			$html .= '
				<p class="cfct-style-image-chooser">'.$this->custom_css_dropdown('image-alignment', __('Image Alignment', 'carrington-build'), 'image', $data).'</p>';
		}
		if (in_array('content', $controls)) {
			$html .= '
				<p class="cfct-style-content-chooser">'.$this->custom_css_dropdown('style-content', __('Content Size', 'carrington-build'), 'content', $data).'</p>';
		}
		$html .= '
			</div><!--/post-layout-controls-->
		';
		
		return $html;
	}
	
	function post_layout_controls_js() {
		return preg_replace('/^(\t){3}/m', '', '
			cfct_builder.addModuleLoadCallback("'.$this->id_base.'",function(form) {
				$(".cfct-post-layout-controls select.cfct-style-chooser").change(function(){
					var _this = $(this);
					var styles = '.json_encode(array_flip(array_map('strtolower', cfct_class_groups('image', true)))).';
					var tgt = $("#'.$this->get_field_id('post-preview-content').' .'.$this->id_base.'-post-content");

					for (i in styles) {
						tgt.removeClass(styles[i]);
					}
					tgt.addClass(_this.val());				
				});
				
			});
		');
	}
	
	function custom_css_dropdown($name, $title, $type, $data) {
		$options = cfct_class_groups($type);
		$current_setting = ($data[$this->get_field_name($name)] ? $data[$this->get_field_name($name)] : false);
		
		$ret = '<label for="'.$this->get_field_id($name).'">'.$title.'</label>
			<select class="cfct-style-chooser" name="'.$this->get_field_name($name).'" id="'.$this->get_field_id($name).'"><option value="">'.__('-none-', 'carrington-build').'</option>';
		foreach ($options as $value => $name) {
			//$ret .= '<option '.($current_setting == $value ? 'selected="selected" ' : '').'value="'.strtolower($value).'">'.$name.'</option>';
			$ret .= '<option '.selected($value, $current_setting, false).' value="'.strtolower($value).'">'.$name.'</option>';
		}
		$ret .='</select>';
		
		return $ret;
	}

// Image Selector
	protected $image_selectors = array();
	
	/**
	 * Image selector HTML markup
	 * $args are:
	 * 	- post_id: id of post to pull images from (for the post_image_selector)
	 * 	- field_name: name of form feild to be submitted on module save
	 * 	- selected_image: id of the currently selected image
	 *  - selected_size: id of the currently selected image size
	 *	- parent_class: additional classes to be applied to the parent wrapper
	 *	- image_class: additional classes to be applied to the image wrappers
	 *	- selected_image_class: additional classes to be applied to the
	 *  - direction: control the orientation of the image list, 'horizontal' or 'vertical' 
	 *
	 * @param string $type - 'post' or 'global'
	 * @param array $args - array('post_id', 'field_name', 'selected_image', 'parent_class', 'image_class', 'selected_image_class')
	 * @return string html
	 */
	public function image_selector($type = 'post', $args = array()) {
		$args = array_merge(array(
			'post_id' => null,
			'field_name' => null,
			'selected_image' => null,
			'selected_size' => null,
			'allow_multiple' => null,
			'image_size' => 'thumbnail',		
			'parent_class' => null,
			'image_class' => null,			
			'selected_image_class' => null  
		), $args);
		if ($type == 'post') {
			return $this->_post_image_selector($args);
		}
		else {
			return $this->_global_image_selector($args);
		}
	}
	
	/**
	 * Method to output a "global" image selector for searching the entire media gallery
	 * Image selector is loaded via ajax based on a search term entered by user
	 *
	 * @see image_selector() for $args descriptions
	 * @param array $args
	 * @return string HTML
	 */
	public function _global_image_selector($args) {
		$value = null;
		
		if (!empty($args['selected_image'])) {
			$image = get_post($args['selected_image']);
			$selected_image = '<div class="cfct-image-select-items-list-item active">'.$this->_image_list_item($image, $args['image_size']).'</div>';
		}
		else {
			$selected_image = '<div class="cfct-image-select-items-list-item cfct-image-select-items-no-image"><div><div class="cfct-image-list-item-title">'.__('No image selected', 'carrington-build').'</div></div></div>';
		}
		$html = '
			<div id="'.$this->id_base.'-'.$args['field_name'].'-global-image-search" class="cfct-global-image-select cfct-image-select-b">
				<div class="'.$this->id_base.'-global-image-select-search">
					<input type="text" name="'.$this->id_base.'-'.$args['field_name'].'-image-search" title="'.__('Search', 'carrington-build').'&hellip;" value="" id="'.$this->id_base.'-'.$args['field_name'].'-image-search" class="cfct-global-image-search-field" data-image-size="'.$args['image_size'].'" />
					<input type="hidden" id="'.$this->get_field_id($args['field_name']).'" class="cfct-global-image-select-value" name="'.$this->get_field_name($args['field_name']).'" value="'.$args['selected_image'].'" />
					
					<div class="cfct-image-scroller-group">
						<div class="cfct-global-image-search-current-image cfct-image-select-current-image cfct-image-select-items-list-item">
							'.$selected_image.'
							<p>'.__('Current Selection', 'carrington-build').'</p>
						</div><div class="cfct-global-image-search-results cfct-image-select-items-list '.$this->_image_list_dir_class($args).' cfct-image-select-items-list-b" id="'.$this->id_base.'-'.$args['field_name'].'-live-search-results"></div>
					</div>
				</div>
				'.$this->_image_selector_size_select($args).'
			</div>
			';
		return apply_filters($this->id_base.'-global-image-select-html', $html, $args);
	}
	
	/**
	 * JS for controlling global image selector
	 * Due to markup differences this method targets a different parent wrapper for adding the image id to the hidden field
	 *
	 * @param string $field_name 
	 * @return string HTML
	 */
	public function global_image_selector_js($field_name) {
		$js_base = $this->id_base;
		return '
			cfct_builder.addModuleLoadCallback("'.$this->id_base.'", function(form) {
				// assign search actions
				searches = [];
				$(".cfct-global-image-select").each(function(){
					var _this = $(this);
					var search = new cfctModuleLiveImageSearch("'.$this->id_base.'", _this);
					searches.push(search);
				});
				// assign search result click actions
				$(".cfct-global-image-select .cfct-image-select-items-list-item").live("click", function() {
					_this = $(this);
					_this.addClass("active").siblings().removeClass("active");
					_wrapper = _this.parents(".cfct-global-image-select");
					$("input:hidden", _wrapper).val(_this.attr("data-image-id"));
					$(".cfct-global-image-search-current-image .cfct-image-select-items-list-item > div", _wrapper)
						.css( "backgroundImage", _this.children(":first").css("backgroundImage") )
						.children(":first").text(_this.children(":first").children(":first").text());
					return false;
				});
			});
		';
	}
	
	/**
	 * Method to output a simple "post" image selector
	 * Image selector shows images attached to a particular post
	 *
	 * @see image_selector() for $args descriptions
	 * @param array $args
	 * @return string HTML
	 */
	public function _post_image_selector($args) {
		if (empty($args['post_id'])) {
			return false;
		}
		
		$attachment_args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'post_status' => 'inherit',
			'post_parent' => $args['post_id'],
			'order' => 'ASC'
		); 

		$attachments = get_posts($attachment_args); 

		if (count($attachments)) {
			$id = $this->id_base.'-'.$args['field_name'].'-image-select-items-list';
			
			$class = 'cfct-post-image-select cfct-image-select-items-list '.$this->_image_list_dir_class($args);
			if (!empty($args['allow_multiple']) && $args['allow_multiple'] == true) {
				$class .= ' cfct-post-image-select-multiple';
				$note = __('Select one or more Images', 'carrington-build');
			}
			else {
				$class .= ' cfct-post-image-select-single';
				$note = __('Select an Image', 'carrington-build');
			}
			
			$html = '
				<p class="cfct-image-select-note">'.$note.'</p>
				<div id="'.$id.'" class="'.$class.'">
					<div>
						'.$this->_image_list($attachments, $args).'
						<input type="hidden" name="'.$this->get_field_name($args['field_name']).'" id="'.$this->get_field_id($args['field_name']).'" value="'.$args['selected_image'].'" />
					</div>
				</div>
				'.$this->_image_selector_size_select($args);
		}
		else {
			$html = '<div class="cfct-image-select-no-images">'.__('No images found for the selected post.', 'carrington-build').'</div>';
		}
		return apply_filters($this->id_base.'-image-select-html', $html, $args);
	}
	
	/**
	 * JS for controlling global image selector
	 * Due to markup differences this method targets a different parent wrapper for adding the image id to the hidden field
	 *
	 * @param string $field_name 
	 * @return string HTML
	 */	 
	public function post_image_selector_js($field_name) {
		return preg_replace('/^(\t){3}/m', '', '
			cfct_builder.addModuleLoadCallback("'.$this->id_base.'", function(form) {
				// account for single select boxes
				$(".cfct-post-image-select.cfct-post-image-select-single .cfct-image-select-items-list-item").live("click", function() {
					_this = $(this);
					_this.addClass("active").siblings().removeClass("active");
					_this.parents(".cfct-image-select-items-list").find("input:hidden").val(_this.attr("data-image-id"));
					return false;
				});
				
				// account for multi-select boxes
				$(".cfct-post-image-select.cfct-post-image-select-multiple .cfct-image-select-items-list-item").live("click", function() {
					_this = $(this);
					
					var val = _this.parents(".cfct-image-select-items-list").find("input:hidden").val();
					if (val == 0) {
						var selected_images = new Array();
					}
					else {
						var selected_images = val.split(",");
					}
					var key = jQuery.inArray(_this.attr("data-image-id"), selected_images);
					
					if (_this.hasClass("active")) {
						_this.removeClass("active");
						if (key != -1) {
							selected_images.splice(key, 1);
						}
					}
					else {
						_this.addClass("active");
						if (key == -1) {
							selected_images.push(parseInt(_this.attr("data-image-id")));
						}
					}
					_this.parents(".cfct-image-select-items-list").find("input:hidden").val(selected_images);
					
					return false;
				});
			});
		');
	}
	
	/**
	 * Show a select list of available image sizes as defined in WordPress
	 *
	 * @param array $args - see this::image_selector() for args definition
	 * @return string HTML 
	 */
	protected function _image_selector_size_select($args) {
		$_sizes = get_intermediate_image_sizes();
		$image_sizes = array();
		foreach ($_sizes as $size) {
			$image_sizes[$size] = $size;
		}
		$image_sizes = apply_filters('cfct-build-image-size-select-sizes', $image_sizes, $this->id_base);
		
		$html = '
			<div class="cfct-image-select-size">
				<label for="'.$this->id_base.'-'.$args['field_name'].'-image-select-size">'.__('Image Size', 'carrington-build').'</label>
				<select name="'.$this->get_field_name($args['field_name']).'-size" id="'.$this->id_base.'-'.$args['field_name'].'-image-select-size">';
		foreach ($image_sizes as $size => $name) {
			$html .= '
					<option value="'.$size.'"'.selected($size, $args['selected_size'], false).'>'.($size == $name ? $this->humanize($name, true, array('-')) : esc_html($name)).'</option>';
		}			
		$html .= '
				</select>
			</div>
			<div class="clear"></div>';
		return $html;
	}
	
	/**
	 * Helper function to determine the direction of the image list based on the passed in args
	 *
	 * @param array $args - the same args array that was passed to the selection output method
	 * @return string classname
	 */
	protected function _image_list_dir_class($args) {
		if (!empty($args['direction']) && in_array($args['direction'], array('horizontal', 'vertical'))) {
			$class = 'cfct-image-select-items-list-'.$args['direction'];
		}
		else {
			$class = 'cfct-image-select-items-list-horizontal';
		}
		return $class;
	}
	
	/**
	 * Common method for building image lists
	 *
	 * @param array $attachments - list of objects describing wp_posts table attachment items
	 * @param bool/int $selected_image - ID of the selected image, 0 for no selection, false to hide the "no image selected" item
	 * @param string $size - size of image to be inserted, must be a registered image size to function correctly
	 * @return string HTML
	 */
	public function _image_list($attachments, $args) {
		// push the selected image to the front of the list of defined
		if ($args['selected_image'] != false) {
			$selected_images = (!empty($args['selected_image']) ? explode(',', $args['selected_image']) : 0);
			$_attachments = $attachments;
			foreach($_attachments as $key => $attachment) {
				if (in_array($attachment->ID, $selected_images)) {
					unset($attachments[$key]);
					array_unshift($attachments, $attachment);
					if (empty($args['allow_multiple'])) {
						break;
					}
				}
			}
			unset($_attachments);
		}
		else {
			$selected_images = false;
		}
		
		$html  = '			
			<ul class="cfct-image-select-items">';
		
		if ($selected_images !== false && empty($args['allow_multiple'])) {
			$html .= '
				<li class="cfct-image-select-items-list-item cfct-image-select-items-no-image'.(empty($selected_images) ? ' active' : '').'" data-image-id="0"><div><div class="cfct-image-list-item-title">'.__('No Image', 'carrington-build').'</div></div></li>';
		}
		foreach ($attachments as $attachment) {
			$active = (is_array($selected_images) && in_array($attachment->ID, $selected_images) ? ' active' : null);
			$html .= '<li class="cfct-image-select-items-list-item'.$active.'" data-image-id="'.$attachment->ID.'">'.$this->_image_list_item($attachment, $args['image_size']).'</li>';
		}
		
		$html .= '
			</ul>';
		return $html;
	}
	
	protected function _image_list_item($image, $size = 'thumbnail') {
		if (!empty($image)) {
			$img_src = wp_get_attachment_image_src($image->ID, $size);
			$url = $img_src[0];
			$title = $image->post_title;
		}
		return '<div style="background: url('.$url.') 0 50% no-repeat;"><div class="cfct-image-list-item-title">'.$title.'</div></div>';
	}

	protected function _global_image_search() {
		$term = trim(stripslashes($_POST['term']));
		
		$images = query_posts(array(
			's' => $term,
			'posts_per_page' => 20,
			'post_type' => 'attachment', 
			'post_mime_type' => 'image',
			'post_status' => 'inherit',
			'order' => 'ASC'
		));

		$args = array(
			'image_size' => (!empty($_POST['image_size']) ? esc_attr($_POST['image_size']) : 'thumbnail')
		);

		$html = '<div>';
		if (count($images)) {
			$html .= $this->_image_list($images, $args);
		}
		else {
			$html .= '
				<ul class="'.$this->id_base.'-image-select-items">
					<li class="cfct-image-select-items-list-item cfct-image-select-items-no-image" data-image-id="0">
						'.sprintf(__('No images found<br />for term "%s"', 'carrington-build'), esc_html($_POST['term'])).'
					</li>
				</ul>';
		}
		$html .= '</div>';
		
		$ret = array(
			'success' => (count($images) ? true : false),
			'term' => esc_html($_POST['term']),
			'html' => $html
		);
		
		header('content-type: text/javascript charset=utf8');
		echo cf_json_encode($ret);
		exit;
	}

// Authors
	
	/**
	 * Returns a label and dropdown for a list of authors
	 *
	 * @param array $data data set in the module
	 * @param string $post_type the permissions of post type the users must have
	 * @return void
	 */
	protected function get_author_dropdown($data = array(), $post_type = 'post') {
		global $current_user;
		$authors = get_editable_user_ids( $current_user->id, true, $post_type ); // TODO: ROLE SYSTEM
		if (is_array($authors)) {
			$dropdown_args = array(
				'include' => $authors, 
				'name' => $this->get_field_name('author'), 
				'selected' => $data[$this->get_field_name('author')],
				'echo' => 0,
				'class' => null,
				'show_option_all' => __('Any', 'carrington-build'),
			);
			$html = '
				<label for="'.$this->get_field_id('author').'">'.__('Author').': </label>
				'.wp_dropdown_users($dropdown_args).'
			';
			return $html;
		}
	}

// Text

	/**
	 * Text Input boilerplate
	 */
	protected function input_text($field_name, $label_text, $value, $args = array()) {
		$defaults = array(
			'prefix' => null,
			'class' => 'widefat',
			'wrapper_class' => '',
		);
		$args = array_merge($defaults, $args);
		extract($args);
	
		$id = (is_null($prefix)) ? $this->get_field_id($field_name) : $prefix.$field_name;
		$name = (is_null($prefix)) ? $this->get_field_name($field_name) : $prefix.$field_name;
		$wrapper_class = (!empty($wrapper_class)) ? ' class="'.$wrapper_class.'"' : '';
		$class = (!empty($class)) ? ' class="'.$class.'"' : '';
		$html = '
			<label for="'.$id.'">'.esc_html($label_text).'</label>
			<input'.$class.' id="'.$id.'" name="'.$name.'" type="text" value="'.esc_attr($value).'" />
		';
		return $html;
	}

// Utility
	public function humanize($str, $titlecase = true, $replace_extras = array()) {
		$find = array('_');
		if (is_array($replace_extras) && !empty($replace_extras)) {
			$find = array_merge($find, $replace_extras);
		}
		$str = str_replace($find, ' ', $str);
		if ($titlecase) {
			$str = ucwords($str);
		}
		return $str;
	}

	/**
	 * Does basic WP text formatting (texturize, autop, etc.)
	 *
	 * @param string
	 * @return string
	 */
	public function wp_formatting($str) {
		$str = wptexturize($str);
		$str = convert_smilies($str);
		$str = convert_chars($str);
		$str = wpautop($str);
		return $str;
	}

}

/**
 * for the time being we'll filter in the JS needed to do live searches
 */
function cfct_module_utility_add_live_image_search_js($js) {
	$file = CFCT_BUILD_DIR.'js/cfct-live-search.js';
	if (is_file($file)) {
		$js .= PHP_EOL.file_get_contents($file).PHP_EOL;
	}
	return $js;
}
add_filter('cfct-get-extras-modules-js-admin', 'cfct_module_utility_add_live_image_search_js');

?>