<?php
/**
 * This file contains a default subset of Advanced Module Options
 */

// Module Options Singleton
class cfct_module_options {
	
	static $_instance;		
	private $module_extras = array();
			
	public function options_list() {
		$ret = '';
		if (count($this->module_extras)) {
			$ret .= '
				<div class="cfct-build-options cfct-build-module-options">
					<h2 class="cfct-build-options-header"><a href="#cfct-advanced-options-list">Advanced Options</a></h2>
					<ul id="cfct-advanced-options-list" class="cfct-build-options-list">';
			foreach ($this->module_extras as $extra) {
				$ret .= $extra->menu_item();
			}
			$ret .= '</ul>
				</div>
				';
		}
		return $ret;
	}
	
	public function options_html($data) {
		$ret = '';
		if (count($this->module_extras)) {
			$ret = '<div id="cfct-popup-advanced-actions" class="cfct-popup-advanced-actions" style="display: none;">';
			foreach ($this->module_extras as $extra) {
				$ret .= $extra->_form($data[$extra->id_base]);
			}
			$ret .= '</div>';
		}
		return $ret;
	}
	
	public function update($new_data, $old_data) {
		$ret = array();
		if (count($this->module_extras)) {
			foreach ($this->module_extras as $extra) {
				if (!empty($new_data[$extra->id_base])) {
					$old_data = (!empty($old_data[$extra->id_base]) ? $old_data[$extra->id_base] : null);
					$ret[$extra->id_base] = $extra->update($new_data[$extra->id_base], $old_data);
				}
			}
		}
		return $ret;
	}
	
	/**
	 * Return any custom module-extra JS for the front end 
	 *
	 * @return void
	 */
	public function js($admin = false) {
		$js = '';
		if (count($this->module_extras)) {
			foreach ($this->module_extras as $extra) {
				$method = ($admin ? 'admin_' : null).'js';
				$js .= PHP_EOL.PHP_EOL.$extra->$method();
			}
		}
		return $js;
	}
	
	/**
	 * Return any custom module-extra CSS for the front end
	 *
	 * @return string
	 */
	public function css($admin = false) {
		$css = '';
		if (count($this->module_extras)) {
			foreach ($this->module_extras as $extra) {
				$method = ($admin ? 'admin_' : null).'css';		
				$css .= PHP_EOL.PHP_EOL.$extra->$method();
			}
		}
		return $css;			
	}
	
	/**
	 * Register an extra
	 *
	 * @param $id
	 * @param $classname
	 * @return bool
	 */
	public function register($id, $classname) {
		if (!class_exists($classname)) {
			return false;
		}
		$this->module_extras[$id] = new $classname;
		return true;
	}
	
	/**
	 * De-register an extra
	 *
	 * @param $id
	 * @param $classname
	 * @return bool
	 */
	public function deregister($id, $classname) {
		if (isset($this->module_extras[$id]) && ($this->module_extras[$id] instanceof $classname)) {
			unset($this->module_extras[$id]);
			return true;
		}
		return false;
	}
	
	/**
	 * Singleton
	 *
	 * @return void
	 */
	public static function get_instance() {
		if (empty(self::$_instance) || !(self::$_instance instanceof cfct_module_options)) {
			self::$_instance = new cfct_module_options;
		}
		return self::$_instance;
	}
}

// Standard Module Options class
class cfct_module_option {
	public $name;
	public $id_base; 
	
	public function __construct($name, $id_base) {
		$this->name = $name;
		$this->id_base = $id_base;
	}
	
	public function menu_item() {
		return '<a href="#cfct-popup-'.$this->id_base.'">'.$this->name.'</a>';
	}
	
	public function _form($data) {
		$ret = '
			<div id="cfct-popup-'.$this->id_base.'">
				<a href="#" class="close">close</a>
				'.$this->form($data).'
			</div>';
		return $ret;
	}
	
	public function update($new_data, $old_data) {
		return $new_data;
	}
	
	public function form($data) {
		return null;
	}
	
	function get_field_name($field_name) {
		return 'cfct-module-options['.$this->id_base.']['.$field_name.']';
	}

	function get_field_id($field_name) {
		return $this->id_base.'-'.$field_name;
	}
			
	public function js() {
		return null;
	}
	
	public function css() {
		return null;
	}
	
	public function admin_js() {
		return null;
	}
	
	public function admin_css() {
		return null;
	}
}

?>