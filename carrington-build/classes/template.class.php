<?php

/**
 * Template
 *
 * @package cfct_build
 */
class cfct_build_template implements Iterator {
		
	private $data;
	
	private $template;
	
	public $rows = array();
	public $registered_rows = array();
	public $modules = array();
	public $registered_modules = array();

	private $types = array();
	
	protected $html;
	protected $current_row = null;
	
	protected $admin;
			
	public function __construct() {
		$this->types = apply_filters('cfct_template_valid_types', array('row', 'module'));
		$this->set_is_admin(is_admin());
		add_action('init', array($this, 'init'), 999999);
	}

	public function init() {
		$this->init_types();
		do_action('cfct_template_init', $this);
	}

	public function set_template($template) {
		if (!$template || empty($template)) {
			// start a new template
			$template = apply_filters('cfct_default_template', $this->new_template());
		}
		elseif (is_int($template)) {
			// @TODO pull structure from database?
		}
		$this->template = apply_filters('cfct_build_template', $template);		
		return true;
	}
	
	public function get_template() {
		return $this->template;
	}
	
	public function get_row_data($row_id) {
		if (isset($this->template['rows'][$row_id]) && is_array($this->template['rows'][$row_id])) {
			return $this->template['rows'][$row_id];
		}
		return false;
	}
	
	/**
	 * display the template
	 *
	 * @param array $data 
	 * @return string html
	 */
	public function html(array $data) {
		$this->data = $data;		
		$this->html = '';
		foreach ($this->template['rows'] as $row_id => $row) {			
			$this->current_row = $row_id;
			$this->html .= $this->row($row);
		}
		
		return apply_filters('cfct_build_template_html', $this->html, $this);
	}
	
	public function text(array $data) {
		$this->return_format = 'text';
		return $this->html($data);
	}
	
	public function add_row(array $row) {
		if (!isset($this->rows[$row['type']])) {
			throw new cfct_row_exception('Class for row type <code>'.$row['type'].'</code> does not exist.');
		}
		
		$row = $this->rows[$row['type']]->process_new($row);
		$html = $this->row($row, true);		
		$this->template['rows'][$row['guid']] = $row;
		return array('html' => $html, 'args' => $row);
	}
	
	public function remove_row($row_id) {
		if (isset($this->template['rows'][$row_id])) {
			unset($this->template['rows'][$row_id]);
		}
		else {
			throw new cfct_row_exception('Cannot delete row. Row id <code>'.$row_id.'</code> does not exist.');
		}
		return true;
	}
	
	public function reorder_rows(array $new_order) {
		if (count($new_order) != count($this->template['rows'])) {
			throw new cfct_row_exception('Reorder row count does not match current row count.');
		}
		return $this->template['rows'] = array_merge(array_flip($new_order), $this->template['rows']);
	}
	
	public function have_rows() {
		return (is_array($this->template['rows']) && count($this->template['rows']) > 0);
	}
	
	public function row(array $row, $new = false) {
		if (!isset($this->rows[$row['type']])) {			
			return false;
		}
		if (isset($this->return_format) && $this->return_format == 'text') {
			$ret = PHP_EOL.$this->rows[$row['type']]->text($row,($new ? array() : $this->data), $this).PHP_EOL;
		}
		elseif ($this->get_is_admin()) {
			$ret = $this->rows[$row['type']]->admin($row,($new ? array() : $this->data), $this);
		}
		else {
			$ret = $this->rows[$row['type']]->html($row,($new ? array() : $this->data), $this);
		}
		return $ret;
	}
	
	private function row_class() {
		// TBD - pick row type here
		// ie: a, a-bc, ab-c, a-b-c
		$class = 'cfct-build-row';
		return apply_filters('cfct_row_class', $class);
	}
	
	private function block($block) {
		foreach ($block as $module) {
			if ($module = $this->get_type('module', $module['module_name'])) {
				$func = !$this->is_admin ? '_html' : '_admin';
				return $module->$func($this->data[$module['guid']]);
			}
			else {
				return false;
			}
		}
	}
	
	/**
	 * Start a blank template
	 * Trivial, but centralized
	 *
	 * @return array
	 */
	public function new_template() {
		$template = array(
			'from_template_id' => false,
			'rows' => array()
		);
		return $template;
	}
	
	/**
	 * Sanitize a template - 
	 *
	 * @param string $template 
	 * @return void
	 */
	public function sanitize_template($template) {
		// strip previous template_id association
		if (isset($template['from_template_id'])) {
			unset($template['from_template_id']);
		}
		
		// sanitize rows
		foreach ($template['rows'] as &$row) {
			if (isset($row['post_id'])) {
				unset($row['post_id']);
			}
		}
		
		return $template;
	}
	
	// REQUEST HANDLERS FOR JS/CSS
	public function css() {
		// iterate modules and grab any custom css
	}
	public function js() {
		// iterate modules and grab any custom js
	}

// Formatting object retrieval 
	
	public function get_module($id) {
		$module = $this->get_type('module', $id);
		if (!$module) {
			$module = new cfct_no_module_module($id);
		}
		return apply_filters('cfct-build-template-get-module', $module, $id);
	}
	
	public function get_row($id) {
		$row = $this->get_type('row', $id);
		return apply_filters('cfct-build-template-get-row', $row, $id);
	}
	
	/**
	 * Get a specific module or row
	 * Module & row classes are not data specific, so we can keep an array of 
	 * objects that we can re-use instantiated classes instead of using unique
	 * objects for each instance
	 *
	 * @param string $type - 'module' or 'row'
	 * @param string $id 
	 * @return object
	 */
	private function get_type($type, $id) {
		global $cfct_build;
		
		$registered_objects = 'registered_'.$type.'s';
		$objects = $type.'s';

		if (!isset($this->$registered_objects) || !isset($this->{$registered_objects}[$id])) {
			return false;
		}
		
		if (isset($this->{$objects}[$id]) && !($this->{$objects}[$id] instanceof $this->{$registered_objects}[$id]['classname'])) {
			$this->{$objects}[$id] = new $this->{$registered_objects}[$id];
		}
		return $this->{$objects}[$id];
	}

// Type Registration
	public function register_type($type, $id, $classname, $args = array()) {
		if (!class_exists($classname)) {
			return false;
		}
		
		$registered_objects = 'registered_'.$type.'s';
		$objects = $type.'s';
		
		if (!isset($this->$objects)) {
			return false;
		}
		// instrospect here
		
		// register
		$this->{$registered_objects}[$id] = array( 
			'classname' => $classname, 
			'args' => $args
		);
		return true;
	}
	
	public function deregister_type($type, $id) {
		$registered_objects = 'registered_'.$type.'s';
		$objects = $type.'s';

		if (!isset($this->$objects)) {
			return false;
		}
		
		if (isset($this->{$registered_objects}[$id])) {
			if (isset($this->{$objects}[$id])) {
				unset($this->{$objects}[$id]);
			}
			unset($this->{$registered_objects}[$id]);
		}
		
		return true;		
	}
	
	private function init_types() {
		foreach ($this->types as $type) {
			$registered_objects = 'registered_'.$type.'s';
			$objects = $type.'s';
			
			$this->$registered_objects = apply_filters('cfct-build-template-pre-'.$type.'-init', $this->$registered_objects);
			
			// widgets can throw an exception during instantiation to abort construction
			foreach ($this->$registered_objects as $id => $params) {
				try {
					$this->{$objects}[$id] = new $params['classname']($params['args']);
				}
				catch(Exception $e) {
					$this->dbg(__METHOD__, $e->getMessage);
				}
			}
			$this->$objects = cfct_array_sort_by_key($this->$objects, 'name');
		}
		
		return true;
	}

// Accessors & Helpers 
	
	public function row_type_exists($id) {
		return isset($this->registered_rows[$id]);
	}
	
	public function module_type_exists($id) {
		return isset($this->registered_modules[$id]);
	}
	
	/**
	 * Get admin setting
	 *
	 * @return void
	 */
	public function get_is_admin() {
		return $this->admin;
	}

	/**
	 * Override the admin setting
	 *
	 * @param string $bool 
	 * @return void
	 */
	public function set_is_admin($bool = null) {
		if (!is_null($bool) && is_bool($bool)) {
			$this->admin = $bool;
		}
		return $this->admin;
	}

// Iterator - Allows us to for/foreach the object

	public function next() {
		return (next($this->rows) !== FALSE);
	}

	public function rewind() {
		return reset($this->rows);
	}

	public function key() {
		return key($this->rows);
	}

	public function current() {
		return current($this->rows);
	}

	public function valid() {
		return !is_null(key($this->rows));
	}
	
	/**
	 * log message to the debugger
	 *
	 * @param string $method - method logging the message
	 * @param string $message - log message
	 * @return bool
	 */
	function dbg($method, $message) {
		if (!CFCT_BUILD_DEBUG) { return false; }
		if (class_exists('cfct_build_debug')) {
			return cfct_build_debug::log($method, $message);
		}
	}	
}

?>