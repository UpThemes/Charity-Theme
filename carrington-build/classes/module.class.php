<?php

/**
 * Module
 * Borrows heavily from WP_Widget
 *
 * @package cfct_build
 */
class cfct_build_module extends cfct_build_module_utility {
	
	public $id_base;
	public $name;
	public $opts;
	public $module_options;
	
	protected $focus_target = null; // CSS3 selector of field to set focus to, if not set focus is set to the first visible field
		
	protected $view = 'view.php';
	protected $available = true;
	protected $editable = true;
	protected $_truncate = true;
	protected $admin_form_fullscreen = false;
	
	protected $errors = array();
	
	protected $suppress_save = false;
	
	/**
	 * Construct
	 */
	function __construct($id_base = false, $name, $opts = array()) {
		$this->basename = $this->get_basename();

		// store widget type if available
		if (isset($opts['widget_type'])) {
			$this->get_widget($opts['widget_type']);
		}
		$this->id_base = $id_base;
		$this->name = $name;
		$this->opts = $opts;
		$this->admin_text_length = 25;
		
		if (isset($opts['is_content']) && !$opts['is_content']) {
			$this->is_content = false;
		}
		
		if (!empty($opts['url'])) {
			$this->url = $opts['url'];
		}
		
		if (!empty($opts['view'])) {
			$this->view = $opts['view'];
		}
			
		parent::__construct();
		$this->module_options = cfct_module_options::get_instance();
	}
	
	public function list_admin() {
		return $this->available;
	}
	
	/**
	 * Public facing output
	 * return html and not echo
	 * Proxy for child class ::display() method
	 * @return html
	 */
	public function html($data) {
		global $cfct_build;
		
		if (empty($data)) { 
			// no funny stuff if we're not passed any display data
			//throw new cfct_module_exception('No data passed to '.$this->name.' module for display'); 
			return '';
		}

		$module_class = apply_filters('cfct-build-module-class', 'cfct-module '.$this->id_base, $data);
		$ret = '
			<div class="'.$module_class.'">
				'.apply_filters('cfct_module_'.$this->id_base.'_display', $this->display($data), $data).'
			</div>
			';
		
		return apply_filters('cfct_module_'.$this->id_base.'_html', $ret, $data);
	}
	
	/**
	 * Load the view 
	 * 
	 * $params is an associative array that will be extracted for the view
	 * All keys in the array will become available variables in the view in
	 * addition to the $data variable
	 *
	 * @param string $view 
	 * @param string $params - additional params to be made available to the template 
	 * @return void
	 */
	public function load_view($data, $params = array()) {
		$view = apply_filters('cfct-module-'.$this->id_base.'-view', $this->view);
		// find file
		$view_path = '';
		if (is_file($view)) {
			// full path to view given
			$view_path = $path;
		}
		else {
			// look for view in module folder
			global $cfct_build;
			$path = dirname($cfct_build->get_module_path($this->basename));
			if (is_file(trailingslashit($path).$view)) {
				$view_path = trailingslashit($path).$view;
			}
		}
		
		// render
		if (!empty($view_path)) {		
			extract($params);
			ob_start();
		
			include($view_path);
		
			$buffer = ob_get_clean();
			return $buffer;
		}
		else {
			return null;
		}
	}
	
	/**
	 * function to output to admin page so that we can wrap the form in lightbox actions
	 * proxy for child class ::admin_form() and ::admin_preview() methods
	 *
	 * @param array $data 
	 * @return string html
	 */
	public function _admin($mode = 'details', $data = array()) {
		global $cfct_build;
		
		// reset admin_success each time
		$this->suppress_save = false;
		$module_admin_content = apply_filters('cfct_module_'.$this->id_base.'_admin_form', $this->admin_form($data), $data);

		if ($mode == 'edit') {
			$html = '
				<div class="'.$this->id_base.'-edit cfct-popup">
					<div class="cfct-popup-header cfct-popup-header-has-icon">
						<img class="cfct-popup-icon" src="'.$this->get_icon().'" alt="'.$this->get_name().'" />
						<h2 class="cfct-popup-title">'.$this->name.'</h2>';
			
			if (isset($this->opts['description'])) {
				$html .= '<p class="cfct-popup-subtitle">' . $this->opts['description'] . '</p>';
			}
			
			if ($this->do_custom_attributes()) {
				$html .= $this->module_options->options_list();		
			}
			
			$html .= '
					</div>';

			$guid = isset($data['module_id']) && !empty($data['module_id']) ? $data['module_id'] : cfct_build_guid($this->id_base, 'module');

			$style = '';
			if (isset($data['max-height'])) {
				$style = ' style="max-height: '.floor($data['max-height']).'px; overflow: auto;"';
			}

			// yank custom attributes from data
			if (isset($data['custom_attributes']) && is_array($data['custom_attributes'])) {
				$custom_attributes = $data['custom_attributes'];
				unset($data['custom_attributes']);
			}
			
			$html .= '
					<div class="'.apply_filters('cfct-module-form-class', 'cfct-module-form').'">
						<form id="'.$this->id_base.'-edit-form" name="'.$this->id_base.'"'.($this->suppress_save ? ' onsubmit="return false;"' : '').'>
				';

			if ($this->do_custom_attributes()) {
				$module_options = array();
				if (isset($data['cfct-module-options'])) {
					$module_options = $data['cfct-module-options'];
					unset($data['cfct-module-options']);
				}
				$html .= $this->module_options->options_html($module_options);
			}

			$html .= '
							<div class="cfct-popup-content'.(!empty($this->admin_form_fullscreen) && $this->admin_form_fullscreen == true ? ' cfct-popup-content-fullscreen' : '').'"'.$style.'>
								<fieldset>
									'.$module_admin_content.'
								</fieldset>
				';
			$html .= '
							</div>
							<div class="cfct-popup-actions">
					';
			if (!$this->suppress_save) {
				$html .= '
								'.($cfct_build instanceof cfct_build_admin ? $cfct_build->popup_activity_div(__('Saving Module&hellip;', 'carrington-build')) : '').'
				
								<input type="submit" name="module-'.$this->id_base.'-submit" id="module-'.$this->id_base.'-submit" class="cfct-button cfct-button-dark" value="'.__('Save', 'carrington-build').'"/>
								<span class="cfct-or"> or </span>';
			}
			$html .= '
								<a href="#" id="cfct-edit-module-cancel" class="cancel">'.__('cancel', 'carrington-build').'</a>
								<input type="hidden" name="module_id" value="'.$guid.'" />
							</div>
						</form>
					</div>
				';
			$html .= '
				</div>
				';
		}
		else {
			$text = $this->admin_text($data);
			if (!empty($text) && $this->_truncate) {
				$hellip = strlen($text) > $this->admin_text_length ? '&hellip;' : '';
				$text = substr(strip_tags($text), 0, $this->admin_text_length).$hellip;
			}
			else {
				$text = $this->name;
			}
			
			$html = '
				<div id="'.$data['module_id'].'" class="cfct-module cfct-module-'.$this->id_base.'">			
					<dl class="cfct-module-content">
						<dt class="cfct-module-content-title">
							<img class="cfct-module-content-icon" src="'.$this->get_icon().'" alt="'.$this->get_name().'" />
							<small class="cfct-module-content-type">'.$this->name.'</small>
							'.esc_html($text).'
						</dt>
						<dd class="cfct-module-edit-clear">';
			if ($this->editable) {
				$html .= '<a href="#'.$data['module_id'].'" class="cfct-module-edit">'.__('Edit', 'carrington-build').'</a> ';
			}
			$html .= '<a href="#'.$data['module_id'].'" class="cfct-module-clear">'.__('Delete', 'carrington-build').'</a>
						</dd>
					</dl>
				</div>
			';
		}
		return apply_filters('cfct_module_'.$this->id_base.'_admin', $html, $mode);
	}
	
	public function _text($data) {
		return apply_filters('cfct_module_'.$this->id_base.'_text', esc_html($this->text($data)), $data);
	}
	
	public function admin_form($data) {
		trigger_error('::admin_form() should be overriden in child class. Do not call this parent method directly.', E_USER_ERROR);
	}
	public function display($data) {
		trigger_error('::display() should be overriden in child class. Do not call this parent method directly.', E_USER_ERROR);
	}
	public function text($data) {
		trigger_error('::text() should be overridden in child class to return the main module content. Do not call this parent method directly', E_USER_ERROR);
	}
	public function admin_text($data) {
		return $this->text($data);
	}
	public function icon() {
		return isset($this->opts['icon']) ? $this->opts['icon'] : false;
	}
	
	/**
	 * Get the module icon.
	 * Icon can be defined in $opts['icon'].
	 * Alternately the icon() method can be overridden to return a path if special operations are needed
	 *
	 * @return string - icon url
	 */
	public function get_icon() {
		if ($path = $this->icon()) {
			$icon = $path;			
			if (!preg_match('/^(http)/', $icon)) {
				#$icon = CFCT_BUILD_URL.'modules/'.preg_replace('/^(\\/)/', '', $icon);				
				$icon = trailingslashit(dirname($this->get_url())).preg_replace('/^(\\/)/', '', $icon);
			}
		}
		else {
			// provide generic icon
			$icon = CFCT_BUILD_URL.'img/default-icon.png';
		}
		return apply_filters('cfct-'.$this->id_base.'module-icon', $icon);
	}

	/**
	 * @deprecated
	 *//*
	protected function icon_file() {
		$file = trailingslashit(dirname(__FILE__)).'icon.png';
		if (file_exists($file)) {
			return str_replace(CFCT_BUILD_DIR, CFCT_BUILD_URL, $file);
		}
		return false;
	} */

	public function get_description() {
		return $this->opts['description'];
	}
	
	public function get_name() {
		return esc_html($this->name);
	}
	
	public function get_id() {
		return $this->id_base;
	}

	/**
	 * Update data, standard is to just return the new data
	 *
	 * @param array $new_data 
	 * @param array $old_data 
	 * @return array
	 */	
	function update($new_data, $old_data) {
		return $new_data;
	}
	
	/**
	 * Process the data for update
	 * Protect our custom-attributes from alteration by child module
	 *
	 * @param array $new_data 
	 * @param array $old_data 
	 * @return array
	 */
	function _update($new_data, $old_data) {
		// preprocess the extra attributes and keep them away from the individual module's update function
		if ($this->do_custom_attributes()) {
			$module_options_new = $module_options_old = array();
			
			if (!empty($new_data['cfct-module-options'])) {
				$module_options_new = $new_data['cfct-module-options'];
				unset($new_data['cfct-module-options']);
			}
			
			if (!empty($old_data['cfct-module-options'])) {
				$module_options_old = $old_data['cfct-module-options'];
				unset($old_data['cfct-module-options']);
			}
		}
		
		$processed = apply_filters('cfct_module_'.$this->id_base.'_update', $this->update($new_data, $old_data), $new_data, $old_data);
				
		if ($this->do_custom_attributes()) {
			$processed['cfct-module-options'] = $this->module_options->update($module_options_new, $module_options_old);
		}

		// wp_filter_post_kses 
		if (current_user_can('unfiltered_html') == false) {
			$processed = $this->apply_wp_kses($processed);
		}

		return $processed;
	}
	
	/**
	 * filter data from users who cannot post unfiltered html
	 * Recurses down in to nested arrays & objects
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	private function apply_wp_kses($data) {
		if (is_array($data)) {
			foreach ($data as &$item) {
				if (is_array($item) || is_object($item)) {
					$item = $this->apply_wp_kses($item);
				}
				else {
					$item = wp_filter_post_kses($item);
				}
			}
		}
		elseif (is_object($data)) {
			foreach (get_object_vars($data) as $var) {
				if (is_array($data->$var) || is_object($data->$var)) {
					$data->$var = $this->apply_wp_kses($data->$var);
				}
				else {
					$data->$var = wp_filter_post_kses($data->$var);
				}
			}
		}
		else {
			$data = wp_filter_post_kses($data);
		}
		return $data;
	}
	
	function error($field, $message) {
		// add ability to log errors for return to user
	}
	
	/**
	 * JS & CSS functions
	 * should return, not echo, for inclusion in a conglomerated file built on a Request Handler
	 */
	function js() {
		// client side js
		return null;
	}
	function css() {
		// client side css
		return null;
	}
	function _admin_js() {
		// admin js
		$js = null;
		if (method_exists($this, 'admin_js')) {
			$js = $this->admin_js();
		}
		if (!empty($this->focus_target)) {
			$js .= '
// set focus to declared target field
cfct_builder.addModuleLoadCallback("'.$this->id_base.'", function(form) {
	$("'.$this->focus_target.'").focus();
});
			';
		}
		else {
			$js .= '
// set focus to first visible field
cfct_builder.addModuleLoadCallback("'.$this->id_base.'", function(form) {
	$("#cfct-edit-module form:visible:first:has(:input:visible) :input[type!=checkbox][type!=radio][type!=file]:not(:submit):not(:button):visible:first").focus();
});
			';
		}
		return $js;
	}
	function _admin_css() {
		// admin css
		$css = null;
		if (method_exists($this, 'admin_css')) {
			$css = $this->admin_css();
		}
		return $css;
	}
	
	/**
	 * Auto id & field name builders, copied concept from WP_Widget
	 *
	 * @param string $field_name 
	 * @return string
	 */
	function get_field_name($field_name) {
		return $this->id_base.'-'.$field_name;
	}

	function get_field_id($field_name) {
		return $this->id_base.'-'.$field_name;
	}
	
	/**
	 * Error Handling Helpers
	 */
	function set_error($field, $message) {
		return $this->errors[$field] = $message;
	}
	function get_error($field) {
		return isset($this->errors[$field]) ? $this->errors[$field] : false;
	}
	function get_errors() {
		return is_array($this->errors) && count($this->errors) ? $this->errors : false;
	}
	
	protected function do_custom_attributes() {
		global $cfct_build;
		return $cfct_build->enable_custom_attributes && ($this->module_options instanceof cfct_module_options);
	}
	
	public function admin_success() {
		return $this->admin_success;
	}
	
	/**
	 * Filepath & URL helpers
	 * Only call from child classes
	 */
	public function get_url() {
		if (empty($this->url)) {
			global $cfct_build;
			$url = $cfct_build->get_module_url($this->basename);
			$this->url = apply_filters('cfct_module_'.$this->id_base.'_url', $url, $this->basename);
		}
		return $this->url;
	}
	
	public function get_path() {
		global $cfct_build;
		$path = $cfct_build->get_module_path($this->basename);
		return apply_filters('cfct_module_'.$this->id_base.'_path', dirname($path), $this->basename);
	}
	
	private function get_basename() {
		if (empty($this->basename)) {
			$dbt = debug_backtrace();
			$this->basename = basename($dbt[1]['file'], '.php');
		}
		return $this->basename;
	}
	
	/**
	 * Widget Helpers
	 */
	private $widget;
	private $widget_type;
	
	function get_widget($widget_type) {
		if (!class_exists($widget_type)) {
			return false;
		}
		if (!($this->widget instanceof $widget_type)) {
			return false;
		}
		$this->widget = new $widget_type($this->id_base = false, $this->name, $this->opts = array());
		return true;
	}
	
	public function is_widget() {
		return !empty($this->_widget_id);
	}
}

?>