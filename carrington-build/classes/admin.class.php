<?php

// admin side class only
class cfct_build_admin extends cfct_build_common {
	
	protected $html;
	protected $in_ajax = false;
	
	function __construct() {
		parent::__construct();
		add_action('init', array($this, 'request_handler'), 11);
		add_action('wp_ajax_cfbuild_fetch', array($this, 'ajax_builder'));
		add_action('admin_init', array($this, 'attach_admin_actions'));
	}
	
	function attach_admin_actions() {
		if (false !== $this->do_build_edit_screen()) {
			add_action('edit_form_advanced', array($this, 'display'), 1);
			add_action('edit_page_form', array($this, 'display'), 1);
			add_action('admin_head', array($this, '_init'));
			add_action('admin_head', array($this, 'admin_head'));
			add_action('save_post', array($this, 'save_post'), 9999, 2);
		
			add_action('admin_head_media_upload_type_form',array($this,'admin_head_media_iframe_css'), 10);
			add_action('admin_head_media_upload_gallery_form',array($this,'admin_head_media_iframe_css'), 10);
		
			wp_enqueue_script('jquery-ui-sortable');
			
			// Allow override of admin scripts (specifically required for VIP hosting. See: README-DEVELOPERS.txt for details)
			if (function_exists('cfct_build_admin_scripts')) {
				// add actions for admin_head, so we can manually echo the <script src="[url]"> tags
				add_action('admin_head', 'cfct_build_admin_scripts', 99999);
			}
			else {
				wp_enqueue_script('cfct-admin-js',admin_url('?cfct_action=cfct_admin_js'), array('jquery'), CFCT_BUILD_VERSION);
				wp_enqueue_style('cfct-admin-css',admin_url('?cfct_action=cfct_admin_css'), array(), CFCT_BUILD_VERSION, 'screen');			
			}
		}
	}
	
	private function do_build_edit_screen() {
		$build_pages = apply_filters('cfct_build_enabled_post_types', array('page'));

		$ret = true;
		if (!empty($build_pages)) {
			$post_type = $this->get_post_type();
			$ret = in_array($post_type, $build_pages);
		}
		return $ret;
	}
	
	/**
	 * Toggle templates
	 * Use filter `cfct-build-enable-templates` to toggle templates on and off
	 *
	 * @return bool
	 */
	protected function templates_enabled() {
		return (defined('CFCT_BUILD_ENABLE_TEMPLATES') ? CFCT_BUILD_ENABLE_TEMPLATES : false);		
	}
	
	/**
	 * determine which type we're working on
	 */
	private function get_post_type() {
		global $pagenow;
		
		if (in_array($pagenow, array('post-new.php'))) {
			if (!empty($_GET['post_type'])) {
				// custom post type or wordpress 3.0 pages
				$type = esc_attr($_GET['post_type']);
			}
			else {
				$type = 'post';
			}
		}
		elseif (in_array( $pagenow, array('page-new.php'))) {
			// pre 3.0 new pages
			$type = 'page';
		}
		else {
			// post/page-edit
			if (isset($_GET['post']))
				$post_id = (int) $_GET['post'];
			elseif (isset($_POST['post_ID'])) {
				$post_id = (int) $_POST['post_ID'];
			}
			else {
				$post_id = 0;
			}

			$type = false;
			if ($post_id > 0) {
				$post = get_post_to_edit($post_id);
			
				if ($post && !empty($post->post_type) && !in_array($post->post_type, array('attachment', 'revision'))) {
					$type = $post->post_type;
				}
			}
		}
		return $type;
	}
	
	public function request_handler() {
		if (isset($_GET['cfct_action'])) {
			switch ($_GET['cfct_action']) {
				case 'cfct_admin_js':
					$this->js();
					break;
				case 'cfct_admin_css':
					$this->css();
					break;
				case 'cfct_admin_css_ie':
					$this->css_ie();
					break;
			}
		}
	}

	public function set_edit_mode() {
		switch (true) {
			case $this->post_id == 0 && !$this->template->have_rows():
				$mode = 'new';
				break;
			case !$this->template->have_rows():
				$mode = 'wordpress';
				break;
			case $this->template->have_rows():
				$mode = 'build';
				break;
		}
		$this->edit_mode = $mode;
	}

	public function display() {
		$this->template->init();
		do_action('cfct_admin_pre_build', $this);
		$this->set_edit_mode();
		
		$this->html .= '
			<div id="cfct-build" '.($this->edit_mode == 'new' ? ' class="new"' : null).'>
				<input type="hidden" name="cfct-autosave-title" id="cfct-autosave-title" value="'.apply_filters('cfct-autosave-title', __('Untitled Build', 'carrington-build')).'" />
				<div id="cfct-build-header" class="cfct-clearfix">
					<div class="cfct-build-header-group">
						<ul id="cfct-build-tabs">
							<li'.($this->edit_mode == 'wordpress' || $this->edit_mode == 'new' ? ' class="active"' : null).'>
								<a title="'.__('Standard WordPress editing mode', 'carrington-build').'" href="'.(user_can_richedit() ? '#postdivrich' : '#postdiv').'"><img class="cfct-icon-wp" src="'.CFCT_BUILD_URL.'img/x.gif" alt="" />'.__('WordPress', 'carrington-build').'</a>
							</li>
							<li'.($this->edit_mode == 'build' ? ' class="active"' : null).'>
								<a title="'.__('Carrington Build editing mode', 'carrington-build').'" href="#cfct-build-data">'.__('Build', 'carrington-build').'</a>
							</li>
						</ul>
					</div>
					<div class="cfct-build-header-group-secondary">
						'.$this->build_page_options().'
					</div>
					<div class="cfct-build-header-group-secondary">
						<div id="cfct-build-messages"></div>
					</div>
				</div><!-- /cfct-build-header -->
				<div id="cfct-build-data">

					<!--[if IE]><![if gte IE 7]><![endif]-->
				
					'.$this->sortables_add().'
					'.$this->row_chooser().'
					<div id="build-editor-toolbar"></div>
					<!--[if IE]><![endif]><![endif]-->
					
					<!--[if lte IE 6]>
						<div class="cfct-error">
							<p>'.__('<strong>Warning:</strong> Carrington Build is not compatible with your browser', 'carrington-build').'</p>
							<p>'.sprintf(__('Please <a href="%s">upgrade your browser</a>, or install the <a href="%s">Google Chrome Frame browser plugin</a> to start Building.', 'carrington-build'), 'http://getfirefox.com', 'http://www.google.com/chromeframe').'</p>
						</div>
					<![endif]-->

				</div><!-- /cfct-build-data -->
				'.$this->dialogs().'
			</div><!-- /cfct-build -->
			';
		do_action('cfct_admin_post_build', $this);
		echo $this->html;
	}
	
	public function welcome_box() {
		// templates are experimental, and just plain mental
		$templates = get_option(CFCT_BUILD_TEMPLATES, array());		
		
		if ($this->templates_enabled()) {
			$start = __('Start from a blank slate.', 'carrington-build');
		}
		else {
			$start = __("It's layout time!", 'carrington-build');
		}
		
		$html = '
			<div id="cfct-welcome-chooser" style="'.($this->edit_mode == 'new' ? 'display: block' : 'display: none').'">
				<div id="cfct-welcome-splash">
					<div class="cfct-popup">
						<div class="cfct-popup-header">
							<h2 class="cfct-popup-title">'.__('Welcome to Carrington Build', 'carrington-build').'</h2>
						</div>
						<div class="cfct-popup-content">
							<ul id="cfct-welcome-options" class="cfct-clearfix'.($this->templates_enabled() ? ' cfct-welcome-templates' : ' cfct-welcome-no-templates').'">
								<li>
									<p><input type="button" class="cfct-button cfct-button-dark" id="cfct-start-build" value="'.__('Start Building', 'carrington-build').'" /></p>
									<p>'.$start.'</p>
								</li>';
		if ($this->templates_enabled()) {
			$html .= '
								<li>
									<p><input type="button" class="cfct-button cfct-button-dark" id="cfct-start-template-chooser" value="'.__('Choose a Template', 'carrington-build').'" />
									<p>'.__('Start from an existing template.', 'carrington-build').'</p>
								</li>';
		}
		$html .= '
							</ul>
						</div>
					</div><!--/cfct-popup-->
				</div><!--/cfct-welcome-splash-->
			';
		
		// templates
		if ($this->templates_enabled()) {
			$html .= '
					<div id="cfct-welcome-templates" class="cfct-hidden">
						<div class="cfct-popup">
							<div class="cfct-popup-header">
								<h2 class="cfct-popup-title">'.__('Choose a Template', 'carrington-build').'</h2>
							</div>
							<div class="cfct-popup-content">';	
				if (count($templates) > 0) {
					$html .= '
								<ul id="cfct-welcome-available-templates" class="cfct-clearfix">';
					foreach ($templates as $template) {
						$html .= '
									<li><a class="cfct-template-name" href="#'.$template['guid'].'">'.
									$template['name'].'</a> <span class="cfct-template-description">'.
									$template['description'].'</span></li>';
					}
					$html .= '
								</ul>';
				}
				else {
					$html .= '
								<div id="cfct-no-templates-available">
									<p>'.__('Sorry, there are no templates available.', 'carrington-build').'</p>
									<p>'.__('Templates are Build configurations that are saved for re-use. Create a custom Build, save it as a template and it will appear here.', 'carrington-build').'</p>
								</div>';
				}						
				$html .= '
								<p><a href="#" id="cfct-choose-template-cancel">'.__('Cancel', 'carrington-build').'</a></p>
							</div><!--/cfct-popup-content-->
						</div><!--/cfct-popup-->
					</div><!--/cfct-welcome-templates-->
				';
		}
		
		$html .= '	
				<div class="cfct-faux-build" role="presentation">
					<div class="cfct-faux-row">
						<div class="cfct-block cfct-block-d">
							<div class="cfct-faux-block-dashes"></div>
						</div>
						<div class="cfct-block cfct-block-e">
							<div class="cfct-faux-block-dashes"></div>
						</div>
					</div><!--/cfct-faux-row-->
					<div id="cfct-welcome-faux-bottom-rows">
						<div class="cfct-faux-row">
							<div class="cfct-block cfct-block-abc">
								<div class="cfct-faux-block-dashes"></div>
							</div>
						</div><!--/cfct-faux-row-->
						<div class="cfct-faux-row">
							<div class="cfct-block cfct-block-ab">
								<div class="cfct-faux-block-dashes"></div>
							</div>
							<div class="cfct-block cfct-block-c">
								<div class="cfct-faux-block-dashes"></div>
							</div>
						</div><!--/cfct-faux-row-->
					</div><!--/cfct-welcome-faux-bottom-rows-->
				</div><!--/cfct-faux-build-->
			</div>
			';
		return $html;
	}
	
	public function row_chooser() {
		$row_types = '';
		foreach ($this->template->rows as $id => $row) {
			$row_types .= '
				<li class="cfct-il-item" id="cfct-row-type-'.$id.'">
					<a class="cfct-il-a" href="#cfct-row-type-'.$id.'" rel="'.$id.'" title="'.esc_attr($row->get_desc()).'">
						<img class="cfct-il-icon" alt="'.$row->get_desc().'" src="'.$row->get_icon().'" />
						<span class="cfct-il-body">
							<strong class="cfct-il-title">'.$row->get_name().'</strong>
						</span>
					</a>
				</li>
				';
		}

		$html = '
			<div id="cfct-sortables-add-container">
				<div id="cfct-loading-row" style="display: none;"></div>
				<div id="cfct-select-new-row">
					<div class="cfct-popup-anchored cfct-popup-anchored-bottom">
						<div class="cfct-popup">
							<div class="cfct-popup-header">
								<h2 class="cfct-popup-title">'.__('Choose a Type of Row to Add', 'carrington-build').'</h2>
							</div><!-- /cfct-popup-header -->
							<div class="cfct-popup-content">
								<ul class="cfct-il cfct-il-mini il-hover-titles cfct-clearfix cfct-rc-row-type-list">
									'.$row_types.'
								</ul>
							</div><!-- /cfct-popup-content -->
						</div><!--/cfct-popup-->
					</div><!-- /cfct-popup-anchored -->
				</div><!--/cfct-select-new-row-->

				<div class="cfct-rows-bottom-bar">
					<a href="#cfct-select-new-row" id="cfct-sortables-add" class="cfct-button cfct-button-dark">'.__('Add New Row', 'carrington-build').'</a>
				</div><!-- /cfct-row-bottom-cap -->
			</div><!-- /cfct-sortables-add-container -->
		';
		return $html;
	}
	
	public function build_page_options() {
		$style = $this->template->have_rows() ? '' : ' style="display: none"';
		$html = '
			<div class="cfct-build-options"'.$style.'>
				<h2 class="cfct-build-options-header"><a href="#cfct-build-options-list">Carrington Build Options</a></h2>
				<ul id="cfct-build-options-list" class="cfct-build-options-list">
			';
		if ($this->templates_enabled()) {
			$html .= '
					<li><a id="cfct-save-as-template" href="#cfct-save-as-template">Save Layout as Template</a><li>';
		}
		$html .= '
					<li><a id="cfct-reset-build-data" href="#cfct-reset-build">Reset Layout</a></li>
					'.apply_filters('cfct-build-page-options', '').'
				</ul>
			</div>
			';
		return $html;
	}
	
	public function sortables_add($welcome = true) {
		$html = '
			<div id="cfct-sortables" class="meta-box-sortables">
				'.$this->welcome_box().'
				'.$this->template->html($this->data).'
			</div>
		';
		return $html;
	}
	
	public function dialogs() {
		// placeholder, do not edit
		$html = '
			<div id="cfct-dialogs" class="cfct-hidden">
			';
		
		// Main dialog wrapper
		$html .= $this->main_dialog_wrapper();
			
		// delete row dialog
		$html .= '
				<div id="cfct-delete-row" class="cfct-popup">
					<div class="cfct-popup-header">
						<h2 class="cfct-popup-title">'.__('Are you sure you want to delete this row?', 'carrington-build').'</h2>
					</div>
					<div class="cfct-popup-content">
						<p>'.__('All information and settings for the row <em>will be permanently lost</em>.', 'carrington-build').'</p>
					</div>
					<div id="cfct-delete-buttons" class="cfct-popup-actions">
						'.$this->popup_activity_div(__('Removing Row','carrington-build').'&hellip;').'
					
						<input type="hidden" id="cfct-delete-row-id" name="cfct-delete-row-id" value="" />
						<button id="cfct-delete-row-confirm" class="cfct-button cfct-button-dark">'.__('Delete Row', 'carrington-build').'</button>
						<span>or</span>
						<a id="cfct-delete-row-cancel" href="#">'.__('Cancel', 'carrington-build').'</a>
					</div>
				</div>
			';

		// delete module dialog
		$html .= '
				<div id="cfct-delete-module" class="cfct-popup">
					<div id="cfct-delete-module-message" class="cfct-popup-header">
						<h2 class="cfct-popup-title">'.__('Are you sure you want to delete this module?', 'carrington-build').'</h2>
					</div>
					<div class="cfct-popup-content">
						<p>'.__('All information and settings for the module <em>will be permanently lost</em>.', 'carrington-build').'</p>
					</div>
					<div id="cfct-delete-buttons" class="cfct-popup-actions">
						'.$this->popup_activity_div(__('Removing Module','carrington-build').'&hellip;').'
					
						<button id="cfct-delete-module-confirm" class="cfct-button cfct-button-dark">'.__('Delete Module', 'carrington-build').'</button>
						<span>or</span>
						<a id="cfct-delete-module-cancel" href="#">'.__('Cancel', 'carrington-build').'</a>
					</div>
				</div>
			';
		
		// reset build dialog
		$html .= '
				<div id="cfct-reset-build" class="cfct-popup">
					<div id="cfct-reset-build-message" class="cfct-popup-header">
						<h2 class="cfct-popup-title">'.__('Are you sure you want to delete all Build Data?', 'carrington-build').'</h2>
					</div>
					<div class="cfct-popup-content">
						<p>'.__('All Build information and settings for this post <em>will be permanently lost</em>.', 'carrington-build').'</p>
					</div>
					<div id="cfct-delete-buttons" class="cfct-popup-actions">
						'.$this->popup_activity_div(__('Resetting Build Data','carrington-build').'&hellip;').'

						<button id="cfct-reset-build-confirm" class="cfct-button cfct-button-dark">'.__('Reset Build', 'carrington-build').'</button>
						<span>or</span>
						<a id="cfct-reset-build-cancel" class="Cancel" href="#">'.__('cancel', 'carrington-build').'</a>
					</div>
				</div>
			';
			
		// save as template
		$html .= $this->save_as_template_wrapper();
			
		// add module dialog
		$html .= $this->add_module_wrapper();
			
		// edit module dialog
		$html .= $this->edit_module_wrapper();
		
		// error dialog
		$html .= $this->error_dialog_wrapper();
		
		// close placeholder
		$html .= '
			</div>
			';
		return $html;
	}
	
	public function popup_activity_div($text = 'Loading&hellip;') {
		return '<span class="cfct-dialog-activity cfct-hidden">'.$text.'</span>';
	}
	
	public function save_as_template_wrapper() {
		$html = '
				<div id="cfct-save-template" class="cfct-popup">
					<div class="cfct-popup-header">
						<h2 class="cfct-popup-title">'.__('Save Layout as Template', 'carrington-build').'</h2>
						<p class="cfct-popup-subtitle">'.__('Save this layout so it can be used as a starting point for other Build posts/pages', 'carrington-build').'</p>
					</div>
					<div class="cfct-module-form">
						<div class="cfct-popup-content">
							<fieldset>
								<p>
									<label for="cfct-new-template-name">Template Name</label><br />
									<input class="cfct-input-short" type="text" name="cfct-new-template-name" id="cfct-new-template-name" value="" />
								</p>
								<p>
									<label for="cfct-new-template-description">Template Description</label>
									<input type="text" name="cfct-new-template-description" id="cfct-new-template-description" value="" />
								</p>
							</fieldset>
						</div>
						<div class="cfct-popup-actions">
							'.$this->popup_activity_div('Saving Template&hellip;').' 
						
							<input id="cfct-template-save-submit" class="cfct-button cfct-button-dark submit" type="submit" value="Save" name="cfct-template-save-submit" />
							<span class="cfct-or"> or </span>
							<a href="#" id="cfct-save-template-cancel" class="cancel">'.__('Cancel', 'carrington-build').'</a>
						</div>
					</div>
				</div>
			';
		return $html;
	}
	
	public function error_dialog_wrapper($content='') {
		$html = '
			<div id="cfct-error-notice" class="cfct-popup">
				<div class="cfct-popup-header">
					<h2 class="cfct-popup-title">'.__('Oops! An Error Has Occurred', 'carrington-build').'</h2>
				</div>
				<div class="cfct-module-form">
					<div class="cfct-popup-content">
						<div id="cfct-error-notice-message">'.$content.'</div>
					</div>
				</div>
				<div class="cfct-popup-actions">
					<a href="#" id="cfct-error-notice-close" class="close cancel">'.__('Close', 'carrington-build').'</a>
				</div>
			</div>
		';
		return $html;		
	}
	
	public function main_dialog_wrapper($content='') {
		$html = '
				<div id="cfct-popup-placeholder"><!-- this div essentially gets discarded -->
					<div id="cfct-popup" class="cfct-dom-window">
						<div id="cfct-popup-inner" class="cfct-dom-window-inner">'.$content.'</div>
					</div>
				</div>
			';
		return $html;
	}
	
	public function add_module_wrapper() {
		$user = wp_get_current_user();
		$view_state = get_usermeta($user->ID, 'cfct_content_chooser_state');

		$modules = $widgets = array();
		foreach($this->template->modules as $id => $module) {
			if ($module->is_widget()) {
				$widgets[$id] = $module;
			}
			else {
				$modules[$id] = $module;
			}
		}

		$html = '
			<div id="cfct-add-module" class="cfct-popup">
				<div class="cfct-popup-header">
					<h2 class="cfct-popup-title">'.__('Choose a Type of Content', 'carrington-build').'</h2>
					<p class="cfct-popup-subtitle">'.__('Select a module or widget to add to your Build', 'carrington-build').'</p>
				</div>
				<div class="cfct-popup-content">
					<ul id="cfct-module-list" class="cfct-module-list cfct-il cfct-clearfix'.($view_state == 'icon' ? ' cfct-il-mini' : '').'">';
		foreach ($modules as $id => $module) {
			if ($module->list_admin()) {
				$html .= $this->module_select_list_wrapper($id, $module);
			}
		}
		$html .= '
					</ul>';
		
		// output widgets in their own list
		if (count($widgets)) {
			$html .= '
					<h3 class="cfct-modules-list-head">Widgets</h3>
					<ul id="cfct-widgets-list" class="cfct-module-list cfct-il cfct-clearfix'.($view_state == 'icon' ? ' cfct-il-mini' : '').'">';
			foreach ($widgets as $id => $module) {
				if ($module->list_admin()) {
					$html .= $this->module_select_list_wrapper($id, $module);
				}
			}
			$html .= '
					</ul>';
		}
		
		$html .= '
				</div>
				<div class="cfct-popup-actions">
					<span id="cfct-module-list-toggles">
						<a id="cfct-module-list-toggle-detail" class="cfct-module-list-toggle '.($view_state !== 'icon' ? ' active' : '').'" href="#cfct-module-list,#cfct-widgets-list" title="Toggle Detail view">Toggle Detail View</a>
						<a id="cfct-module-list-toggle-compact" class="cfct-module-list-toggle'.($view_state == 'icon' ? ' active' : '').'" href="#cfct-module-list,#cfct-widgets-list" title="Toggle Compact View">Toggle Compact View</a>
					</span>
					'.$this->popup_activity_div(__('Loading Module Options','carrington-build').'&hellip;').' 

					<a href="#" id="cfct-delete-module-cancel" class="cancel">'.__('Cancel', 'carrington-build').'</a>
				</div>
			</div>';
		return $html;
	}
	
	protected function module_select_list_wrapper($id, $module) {
		return '
				<li class="cfct-il-item">
					<a class="cfct-add-module-'.$module->get_id().' cfct-il-a" href="#'.$id.'">
						<img class="cfct-il-icon" src="'.$module->get_icon().'" alt="'.esc_attr($module->get_name()).'" />
						<span class="cfct-il-body">
							<strong class="cfct-il-title">'.$module->get_name().'</strong>
							<span class="cfct-il-description">'.$module->get_description().'</span>
						</span>
					</a>
				</li>';		
	}
	
	public function edit_module_wrapper($content = '') {
		$html = '
			<div id="cfct-edit-module">
				<div id="cfct-edit-module-form">'.$content.'</div>
			</div>
			';
		return $html;
	}
	
	/**
	 * Set the post_content to be the Carrington Build data.
	 * This completely overwrites any previous post_content.
	 *
	 * @param int $post_id 
	 * @return void
	 */
	public function set_post_content($post_id) {
		$build = new cfct_build();
		// force admin to false so that this functions correctly on the back end
		$this->template->set_is_admin(false);
		$build->set_template($this->template);
		do_action('pre_cfct_build', $build);
		$post_update = array(
			'ID' => $post_id, 
			'post_content' => $build->text(false, $post_id)
		);
		remove_action('save_post', array($this, 'save_post'), 9999);
		wp_update_post($post_update);
		unset($build);
		// reset admin to proper value
		$this->template->set_is_admin(is_admin());
	}
	
	/**
	 * Populate post data on post save for build compatible posts
	 *
	 * @param int $post_id 
	 * @param object $post 
	 * @return void
	 */
	public function save_post($post_id, $post) {
		if ($post->post_type == 'revision') { return; }
		$this->_init($post_id);
		if ($this->can_do_build()) {
			$this->set_post_content($post_id);
			return true;
		}
		return false;
	}

	/**
	 * Process template & data for save
	 */
	public function process($data, $old_data = array()) {
		$pdata = array();
		
		foreach ($_POST['cfct_build_data'] as &$row_data) {
			foreach ($row as &$block) {
				foreach ($block as &$module) {
					$m = $this->template->get_module($module['type']);
					$m_old_data = isset($old_data[$module['guid']]) ? $old_data[$module['guid']] : array();
					$pdata[$module['guid']] = $m->update($module_data['type'], $m_old_data);
				} // end module processing
			} // end block processing
		} // end row processing
		return $pdata;
	}

	/**
	 * If requested, save template data as a public template
	 * do any prep work necessary, the call the template's save() method
	 */
	private function save_template() {
		$this->template->save();
	}
	
	/**
	 * Handle build requests via the admin_ajax handler
	 *
	 * @return html
	 */
	public function ajax_builder() {
		$this->in_ajax = true;
		// sleep(2);
		if (method_exists($this, 'ajax_'.strval($_POST['func']))) {
			$method = 'ajax_'.strval($_POST['func']);

			$args = $this->ajax_decode_json($_POST['args'], true);

			try {
				$result = $this->$method($args);
			}
			catch(cfct_exception $e) {
				$result = new cfct_message(array(
					'success' => false,
					'html' => $e->getHTML(),
					'message' => $e->getMessage()
				));				
			}
			catch(Exception $e) {
				$result = new cfct_message(array(
					'success' => false,
					'html' => '<div class="cfct-error"><p>'.__('An unknown Error has occurred.', 'carrington-build').'</p></div>',
					'message' => $e->getMessage()
				));				
			}
		}
		else {
			$result = new cfct_message(array(
				'success' => false,
				'message' => __('invalid function call: function does not exist','carrington-build')
			));
		}
		$result->send();
	}
	
	/**
	 * Helper function to allow others to determine if this
	 * object is responding to an Ajax call
	 *
	 * @return bool
	 */
	public function in_ajax() {
		return $this->in_ajax;
	}
	
	/**
	 * Decode the incoming JSON data
	 *
	 * @param string $json 
	 * @param bool $array 
	 * @return array
	 */
	public function ajax_decode_json($json, $array = false) {
		$json = stripslashes($json);
		return cf_json_decode($json, $array);
	}
	
	/**
	 * Start off a post from a template.
	 *
	 * @param array $args 
	 * @return object
	 */
	public function ajax_insert_template($args) {
		$templates = get_option(CFCT_BUILD_TEMPLATES);
		if (!is_array($templates) || count($templates) < 1) {
			throw new cfct_template_exception(__('Invalid action. There are no templates to choose from.','carrington-build'));
		}
		if (!array_key_exists($args['template_id'], $templates)) {
			throw new cfct_template_exception(__('Template id not found','carrington-build').' (template_id: '.$args['template_id'].')');
		}
		$this->template->init();
		do_action('cfct_admin_pre_build', $this);
		$template = $templates[$args['template_id']]['template'];
		$template['from_template_id'] = $args['template_id'];
		if ($this->set_postmeta($args['post_id'], array('template' => $template))) {
			$this->template->set_template($template);
			$html = $this->template->html(array());
		
			$ret = new cfct_message(array(
				'success' => true,
				'html' => $html,
				'message' => __('Template successfully applied to post:', 'carrington-build').' '.$args['post_id']
			));
		}
		else {
			$ret = new cfct_template_exception(__('Could not save template for post.','carrington-build').' (post_id: '.$args['post_id'].')');
		}
		return $ret;
	}
	
	/**
	 * Add a new row from an ajax request
	 *
	 * @throws cfct_row_exception, cfctTemplateException
	 * @param array $args 
	 * @return object
	 */
	public function ajax_new_row($args) {
		$post_data = $this->get_postmeta($args['post_id']);
		if ($this->template->set_template($post_data['template'])) {
			$data = $args;
			unset($data['post_id']);
			$result = $this->template->add_row($data);
			$post_data['template'] = $this->template->get_template();
			if (!$this->set_postmeta($args['post_id'], $post_data)) {
				throw new cfct_row_exception(__('Could not save postmeta for post on row add','carrington-build').' (post_id: '.$args['post_id'].')');
			}
			$ret = new cfct_message(array(
				'success' => true,
				'html' => $result['html'],
				'message' => __((isset($result['message']) ? $result['message'] : 'operation successful'), 'carrington-build')
			));
		}
		else {
			throw new cfct_template_exception(__('Could not set up Template to add row','carrington-build'));
		}
		return $ret;
	}
	
	public function ajax_save_as_template($args) {
		$templates = get_option(CFCT_BUILD_TEMPLATES);
		if (!$templates) {
			$templates = array();
			$new_value= true;
		}
		
		$postmeta = get_post_meta($args['post_id'], CFCT_BUILD_POSTMETA, true);
		$guid = cfct_build_guid(md5(serialize($args)), 'template');
		
		parse_str($args['data'], $data);
		
		$name = esc_attr(strip_tags($data['cfct-new-template-name']));
		$description = esc_attr(($data['cfct-new-template-description']));
		$template = $this->template->sanitize_template($postmeta['template']);
		
		$templates[$guid] = array(
			'guid' => $guid,
			'name' => $name,
			'description' => $description,
			'template' => $template
		);
		
		if ($new) {
			$res = insert_option(CFCT_BUILD_TEMPLATES, $templates, 'no');
		}
		else {
			$res = update_option(CFCT_BUILD_TEMPLATES, $templates);
		}
		
		if ($res !== false) {
			$ret = new cfct_message(array(
				'success' => true,
				'html' => '<p>'.__('Template saved.','carrington-build').'</p>',
				'message' => 'template saved: '.$guid
			));	
		}
		else {
			throw new cfct_template_exception(__('Could not save template.','carrington-build').' (post_id: '.$args['post_id'].')');
		}
		return $ret;
	}
	
	/**
	 * Delete a row from an ajax request
	 * 
	 * @throws cfct_row_exception
	 * @param array $args 
	 * @return array
	 */
	public function ajax_delete_row($args) {
		$post_data = $this->get_postmeta($args['post_id']);
		if ($this->template->set_template($post_data['template'])) {
			$row = $this->template->get_row_data($args['row_id']);
			if (is_array($row)) {
				foreach ($row['blocks'] as $block) {
					if (!empty($post_data['data']['blocks'][$block['guid']]) && is_array($post_data['data']['blocks'][$block['guid']])) {
						foreach($post_data['data']['blocks'][$block['guid']] as $module_id) {
							if (isset($post_data['data']['modules'][$module_id])) {
								unset($post_data['data']['modules'][$module_id]);
							}
						}
						unset($post_data['data']['blocks'][$block['guid']]);
					}					
				}
			}
			
			// remove row from template
			if ($this->template->remove_row($args['row_id'])) {
				$post_data['template'] = $this->template->get_template();
				if (!$this->set_postmeta($args['post_id'], $post_data)) {
					throw new cfct_row_exception(__('Could not save postmeta for post on row delete','carrington-build').' (post_id: '.$args['post_id'].')');
				}				
				$ret = new cfct_message(array(
					'success' => true,
					'html' => '<div class="cfct-message">'.__('Row deleted.', 'carrington-build').'</div>',
					'message' => 'row id '.$args['row_id'].' '.__('delete successful', 'carrington-build')
				));
			}
			else {
				throw new cfct_row_exception(__('Could not delete row. An unknown error occured.','carrington-build'));
			}
		}
		else {
			throw new cfct_template_exception(__('Could not set up Template to delete row','carrington-build'));
		}
		
		return $ret;
	}
	
	/**
	 * Reorder the rows via ajax
	 *
	 * @param array $args 
	 * @return object
	 */
	public function ajax_reorder_rows($args) {
		$post_data = $this->get_postmeta($args['post_id']);
		if ($this->template->set_template($post_data['template'])) {
			$this->template->reorder_rows(explode(',', $args['order']));
			$post_data['template'] = $this->template->get_template();
			if (!$this->set_postmeta($args['post_id'], $post_data)) {
				throw new cfct_row_exception(__('Could not save postmeta for post on row reorder.','carrington-build').' (post_id: '.$args['post_id'].')');
			}				
			$ret = new cfct_message(array(
				'success' => true,
				'html' => '<div class="cfct-message">'.__('Rows Reordered.', 'carrington-build').'</div>',
				'message' => __('row reorder successful', 'carrington-build')
			));	
		}
		else {
			throw new cfct_template_exception(__('Could not set up Template to reorder rows','carrington-build').' (post_id: '.$args['post_id'].')');
		}
		
		return $ret;
	}
	
	/**
	 * Reorder a row's modules via ajax
	 *
	 * @param string $args 
	 * @return object
	 */
	function ajax_reorder_modules($args) {
		$post_data = $this->get_postmeta($args['post_id']);
		$new_order = (!empty($args['order']) ? $args['order'] : array());

		$post_data['data']['blocks'] = $new_order;
		if (!$this->set_postmeta($args['post_id'], $post_data)) {
			throw new cfct_row_exception(__('Could not save postmeta for post on module reorder.','carrington-build').' (post_id: '.$args['post_id'].')');			
		}
		else {
			$this->set_post_content($args['post_id']);
			$ret = new cfct_message(array(
				'success' => true,
				'html' => '<div class="cfct-message">'.__('Modules Reordered', 'carrington-build').'</div>',
				'message' => __('module reorder successful', 'carrington-build')
			));
		}
		return $ret;
	}
	
	public function ajax_edit_module($args) {
		$post_data = $this->get_postmeta($args['post_id']);
		
		// Get the module type to instantiate
		if (isset($args['module_type'])) {
			// new module data
			$module_type = $args['module_type'];
		}
		else {
			// existing module data
			$module_type = $post_data['data']['modules'][$args['module_id']]['module_type'];
		}

		if ($module = $this->template->get_module($module_type)) {
			$data = (!empty($args['module_id']) && isset($post_data['data']['modules'][$args['module_id']])) ? $post_data['data']['modules'][$args['module_id']] : array();
			
			// handle incoming widget type arg
			if (isset($args['widget_id'])) {
				$data['widget_id'] = $args['widget_id'];
			}
			if (isset($args['max-height'])) {
				$data['max-height'] = $args['max-height'];
			}
			// make sure we always have a module-type name to work with
			if (empty($data['module_type'])) {
				$data['module_type'] = $module_type;
			}
			$html = $module->_admin('edit', $data);
		
			$ret = new cfct_message(array(
				'success' => true,
				'html' => $this->edit_module_wrapper($html),
				'message' => __('returning module admin form', 'carrington-build')
			));
		}
		else {
			throw new cfct_template_exception(__('Could not find module ','carrington-build').' (module_id: '.$args['module'].')');
		}
		return $ret;
	}

	public function ajax_save_module($args) {
		kses_init();
		$post_data = $this->get_postmeta($args['post_id']);

		if (isset($args['module_type'])) {
			$module_type = $args['module_type'];
			$old_data = array();
			$new = true;
		}
		else {
			$old_data = $post_data['data']['modules'][$args['module_id']];
			$module_type = $old_data['module_type'];
			$new = false;
		}

		if ($module = $this->template->get_module($module_type)) {
			parse_str($args['data'], $data);
			
			// @TODO: use http://us.php.net/manual/en/function.array-diff-uassoc.php to supply _update with only the
			// values it needs to update and not give it access to any of the _id args
	
			$save = $module->_update($data, $old_data);

			if ($save !== false) {

				$save['module_type'] = $module_type;
				$save['module_id'] = $save['module_id'];
				$save['block_id'] = $args['block_id'];
				unset($save['guid']);
				
				$post_data['data']['modules'][$save['module_id']] = $save;
				if ($new) {
					$post_data['data']['blocks'][$save['block_id']][] = $save['module_id'];
				}
				
				$this->set_postmeta($args['post_id'], $post_data);
				$this->set_post_content($args['post_id']);

				$ret = new cfct_message(array(
					'success' => true,
					'html' => $module->_admin('details', $save),
					'message' => __('edit successful for', 'carrington-build').' row_id: '.$args['row_id'].', block_id: '.$args['block_id'].''
				));				
			}
			else {
				// @TODO: return modified form with error messages in it
			}
		}
		else {
			throw new cfct_template_exception(__('Could not find module ','carrington-build').' (module: '.$module.')');
		}
		return $ret;
	}

	public function ajax_delete_module($args) {
		$post_data = $this->get_postmeta($args['post_id']);
		$cleared = false;
		
		if (isset($post_data['data']['blocks'][$args['block_id']]) && in_array($args['module_id'], $post_data['data']['blocks'][$args['block_id']])) {
			$k = array_search($args['module_id'], $post_data['data']['blocks'][$args['block_id']]);
			unset($post_data['data']['blocks'][$args['block_id']][$k], $post_data['data']['modules'][$args['module_id']]);
			$cleared = true;
		}
		
		if (!$cleared) {
			throw new cfct_template_exception(__('Data for block not found. No remove performed','carrington-build').' (block_id: '.$args['block_id'].')');
		}
		
		if (!$this->set_postmeta($args['post_id'], $post_data)) {
			throw new cfct_row_exception(__('Could not save postmeta for post on row delete','carrington-build').' (post_id: '.$args['post_id'].')');
		}	
		$this->set_post_content($args['post_id']);			
		$ret = new cfct_message(array(
			'success' => true,
			'html' => $this->template->get_row($post_data['template']['rows'][$args['row_id']]['type'])->empty_block(),
			'message' => 'row id '.$args['row_id'].' '.__('delete successful', 'carrington-build')
		));
		return $ret;
	}
	
	function ajax_content_chooser_state($args) {
		$user = wp_get_current_user();
		if (update_usermeta($user->ID, 'cfct_content_chooser_state', esc_attr($args['state']))) {
			$ret = new cfct_message(array(
				'success' => true,
				'html' => '<p>'.__('State Saved','carrington-build').'</p>',
				'message' => __('usermeta saved for cfct_content_chooser_state', 'carrington-build').': '.$state
			));
		}
		else {
			throw new cfct_exception(__('could not save usermeta for content chooser state','carrington-build'));
		}
		return $ret;
	}

	/**
	 * Reset the post's build data
	 *
	 * @param array $args 
	 * @return object/exception
	 */
	public function ajax_reset_build($args) {
		if (intval($args['post_id']) < 0) {
			$ret = new cfct_message(array(
				'success' => true,
				'html' => '',
				'message' => __('cannot reset new post', 'carrington-build')
			));
		}
		elseif ($this->reset_edit_state($args['edit_state'], $args['post_id'])) {
			$ret = new cfct_message(array(
				'success' => true,
				'html' => '<div><p>'.__('Build Data Reset').'</p></div>',
				'message' => __('build data reset for', 'carrington-build').' post_id "'.$args['post_id'].'"'
			));
		}
		else {
			throw new cfct_exception(__('Build data reset failed.','carrington-build').' (post_id:'.$args['post_id'].')');
		}
		return $ret;	}

 	/**
	 * Set the edit state of the post
	 *
	 * @param array $args 
	 * @return object/exception
	 */
	public function ajax_reset_edit_state($args) {
		if (intval($args['post_id']) < 0) {
			$ret = new cfct_message(array(
				'success' => true,
				'html' => '',
				'message' => __('edit state not modified for new post', 'carrington-build')
			));
		}
		elseif ($this->reset_edit_state($args['edit_state'], $args['post_id'])) {
			$ret = new cfct_message(array(
				'success' => true,
				'html' => '<div><p>'.__('Edit state updated').'</p></div>',
				'message' => __('edit state update to', 'carrington-build').' "'.$args['edit_state'].'" '.__('successful for', 'carrington-build').' post_id "'.$args['post_id'].'"'
			));
		}
		else {
			throw new cfct_exception(__('edit state update failed','carrington-build').' (post_id: '.$args['post_id'].')');
		}
		return $ret;
	}

	/**
	 * Set the edit state of the admin page
	 *
	 * @param string $edit_state 
	 * @param int $post_id 
	 * @return bool
	 */
	private function reset_edit_state($edit_state, $post_id = null) {
		$post_id = ($post_id !== null ? $post_id : $this->post_id);

		// reset post content on switch
		delete_post_meta($post_id, CFCT_BUILD_POSTMETA);
		wp_update_post(array('ID' => $post_id, 'post_content' => ''));
		return true;
	}
	
	/**
	 * Output Admin JS
	 *
	 * @param string $js 
	 * @return void
	 */
	public function js() {
		header('Content-type: text/javascript');
		$js = '';
		
		$js .= file_get_contents(CFCT_BUILD_DIR.'js/json2.js');
		$js .= file_get_contents(CFCT_BUILD_DIR.'js/jquery.DOMWindow.js');
		$js .= file_get_contents(CFCT_BUILD_DIR.'js/jquery.placeholder/jquery.placeholder.js');
		$js .= file_get_contents(CFCT_BUILD_DIR.'js/cfct-build-admin.js');
		
		// safety wrap the included JS so we can safely use $()
		$js .= '
;(function($) {
	
			';
		$js .= $this->get_module_extras('js', true);	
		$js .= '
		
})(jQuery);		
			';
				
		// echo and leave
		echo $js;
		exit;
	}
	
	/**
	 * Output Admin CSS
	 *
	 * @param string $css 
	 * @return void
	 */
	public function css() {
		header('Content-type: text/css');
		$css = '';
		
		$css .= file_get_contents(CFCT_BUILD_DIR.'css/cfct-build-admin.css');
		$css .= file_get_contents(CFCT_BUILD_DIR.'css/cfct-build-common.css');
		$css = str_replace('../img/', CFCT_BUILD_URL.'img/', $css);
		
		$css .= $this->get_module_extras('css', true);
		$css .= $this->get_row_extras('css', true);
		
		// echo and leave
		echo $css;
		exit;
	}
	
	/**
	 * Output IE specific CSS 
	 *
	 * @return void
	 */
	public function css_ie() {
		header('Content-type: text/css');
		$css = '';
		
		$css .= file_get_contents(CFCT_BUILD_DIR.'css/cfct-build-admin-ie.css');
		$css = str_replace('../img/', CFCT_BUILD_URL.'img/', $css);
		
		// echo and leave
		echo $css;
		exit;
	}
	
	/**
	 * Insert conditional comment in the head for IE specific stylesheet
	 *
	 * @return void
	 */
	public function admin_head() {
		$html = '
<meta http-equiv="X-UA-Compatible" content="chrome=1" />
<!--[if lte IE 7]>
<link rel="stylesheet" href="'.admin_url('?cfct_action=cfct_admin_css_ie').'" type="text/css" media="screen" />
<![endif]-->
';
		echo $html;
	}
	
	/**
	 * Hide the "insert into post" button when browsing media gallery and in Build mode
	 *
	 * @todo replace with "copy" functionality?
	 * @return void
	 */
	public function admin_head_media_iframe_css() {
		$this->_init(intval($_REQUEST['post_id']),true);
		$this->set_edit_mode();
		if ($this->edit_mode == 'build') {
			echo '
<style type="text/css">
	td.savesend input.button {
		display: none;
	}
</style>
				';
		}
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