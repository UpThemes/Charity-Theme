<?php

// client side class only
class cfct_build extends cfct_build_common {
	
	protected $postmeta_key;
	protected $post_id;
	
	protected $the_content_filters;
	
	/**
	 * Storage var for Carrington Build postmeta retrieved for post_id 
	 *
	 * @var array
	 */
	public $template;
	protected $data;
	
	/**
	 * Construct
	 * prep and validate config
	 *
	 * @param int $post_id 
	 */
	public function __construct() {
		parent::__construct();
		add_action('init', array($this, 'request_handler'), 11);
		
		global $wp_filter;
		//add_action('cfct_build_post_build', array($this, 'remove_the_content_actions'));		
		wp_enqueue_style('cfct-build-css',site_url('/?cfct_action=cfct_css'), array(), CFCT_BUILD_VERSION, 'screen');
		wp_enqueue_script('cfct-build-js',site_url('/?cfct_action=cfct_js'), array('jquery'), CFCT_BUILD_VERSION);
	}
	
	public function request_handler() {
		if (isset($_GET['cfct_action'])) {
			switch ($_GET['cfct_action']) {
				case 'cfct_js':
					$this->js();
					break;
				case 'cfct_css':
					$this->css();
					break;
			}
		}
	}
	
	/**
	 * When doing build make build the only action on the_content.
	 * If needed restore the standard filters on the_content when doing regular post.
	 * To append/prepend to the build content use:
	 *   - the 'cfct_build_content' filter that gets passed the built HTML
	 *   - the 'cfct_build_post_build' action that gets passed this object and modify the $this->ret property.
	 *
	 * @return void
	 */
	// public function remove_the_content_actions() {
	// 	global $wp_filter, $post;
	// 	if (empty($this->the_content_filters)) {
	// 		$this->the_content_filters = $wp_filter['the_content'];
	// 	}
	// 	
	// 	if ($this->can_do_build()) {		
	// 		$wp_filter['the_content'] = array();
	// 	}
	// 	elseif (count($wp_filter['the_content']) == 1) {
	// 		$wp_filter['the_content'] = $this->the_content_filters;
	// 	}
	// }
	
	/**
	 * Display
	 *
	 * @param bool $echo 
	 * @param int $post_id
	 * @param bool $html
	 * @return mixed - bool/string HTML
	 */
	public function display($echo = false, $post_id = null, $html = true) {
		$this->_init($post_id);
		do_action('cfct_build_pre_build', $this);
		
		if ($this->can_do_build()) {
			if ($html) {
				$this->ret = '
					<div id="'.apply_filters('cfct-build-display-id', 'cfct-build-'.$this->post_id).'" class="'.apply_filters('cfct-build-display-class', 'cfct-build').'">
						'.$this->template->html($this->data).'
					</div>
					';	
			}
			else {
				$this->ret = $this->template->text($this->data);
			}
		}
		else {
			$this->ret = false;
		}
		
		do_action('cfct_build_post_build', $this);
		$ret = apply_filters('cfct_build_content', $this->ret);
		
		if ($echo) {
			echo $ret;
		}
		else {
			return $ret;
		}
	}
	
	/**
	 * Display Plain Text Version
	 *
	 * @param bool $echo 
	 * @param int $post_id
	 * @return mixed - bool/string HTML
	 */
	public function text($echo = false, $post_id = null) {
		return $this->display($echo, $post_id, false);
	}
	
	public function js() {
		header('Content-type: text/javascript');
		$js = '';
		// safety wrap the included JS so we can safely use $()
		$js .= '
;(function($) {

			';
		$js .= $this->get_module_extras('js');
		$js .= '

})(jQuery);		
			';		
		echo apply_filters('cfct_build_js', $js);
		exit;
	}
	
	/**
	 * Output Front End CSS
	 *
	 * @param string $css 
	 * @return void
	 */
	public function css() {
		header('Content-type: text/css');

		$css = '';

		$css .= file_get_contents(CFCT_BUILD_DIR.'css/cfct-build-common.css');
		$css .= file_get_contents(CFCT_BUILD_DIR.'css/cfct-build-client.css');
		$css .= $this->get_module_extras('css');
		$css .= $this->get_row_extras('css');

		echo apply_filters('cfct_build_css', $css);
		exit;
	}
}

?>