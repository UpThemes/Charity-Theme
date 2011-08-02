<?php
if (!class_exists('cfct_module_loop')) {
	@include_once(dirname(dirname(__FILE__)).'/loop/loop.php');
}

if (!class_exists('cfct_module_loop_subpages') && class_exists('cfct_module_loop')) {
	class cfct_module_loop_subpages extends cfct_module_loop {
		
		public function __construct() {
			global $cfct_build;
			
			// We need to enqueue the suggest script so we can use it later for type-ahead search
			$this->enqueue_scripts();

			// don't allow selection of content display in loop
			unset($this->content_display_options['content']);
			
			$opts = array(
				'description' => 'A list of sub-page titles or excerpts.',
				'icon' => 'loop-subpages/icon.png'
			);
			cfct_build_module::__construct('cfct-module-loop-subpages', __('Sub-Pages', 'carrington-build'), $opts);
		}

# Display

		/**
		 * Display the module
		 *
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function display($data) {
			// make sure that pages get menu_order applied with fallback to title order
			$this->default_display_args = array_merge($this->default_display_args, array(
				'order' => 'ASC',
				'orderby' => 'menu_order title'
			));
			return parent::display($data);
		}
		
# Admin Form
		
		/**
		 * Output the Admin Form
		 * 
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function admin_form($data) {
			return '
				<div id="'.$this->id_base.'-admin-form-wrapper">'.
					$this->admin_form_title($data).
					$this->admin_form_post_types('page', $data).
					$this->admin_form_parent_pages($data).
					$this->admin_form_display_options($data).
				'</div>';
		}

		/**
		 * Get a list of pages who have subpages
		 * 
		 * @param array $data
		 * @return string HTML
		 */
		protected function admin_form_parent_pages($data) {
			return '
				<fieldset class="cfct-ftl-border">
					<legend>Page</legend>
					<!-- parent pages -->
					<div class="'.$this->id_base.'-input-wrapper">
						'.$this->get_parent_pages_dropdown($data).'
					</div>
					<div class="clear"></div>
					<!-- parent pages -->
				</fieldset>
				';
		}

# Admin Helpers
		
		/**
		 * Displays a dropdown of all pages that have parent children
		 *
		 * @return string
		 */
		protected function get_parent_pages_dropdown($data) {
			$parent_ids = $this->_get_parent_pages_ids();
			$html = '<label for="'.$this->get_field_id('parent').'">'.__('Parent Page', 'carrington-build').': </label>';
			if (!empty($parent_ids)) {
				$selected = (!empty($data[$this->get_field_name('parent')]) ? $data[$this->get_field_name('parent')] : null);
				$html .= '
					<select name="'.$this->get_field_name('parent').'" id="'.$this->get_field_id('parent').'">
						'.$this->_get_parent_options($parent_ids, $selected).'
					</select>
				';
			}
			else {
				$this->suppress_save = true;
				$disclaimer = 'No parent pages exist. To use this module pages or hierarchical post types that have child pages must exits.';
				$html .= '
					<div class="'.$this->id_base.'-input-replacement">'.__($disclaimer, 'carrington-build').'</div>
					<input type="hidden" name="'.$this->get_field_name('parent').'" value="" />
				';
			}
			return $html;
		}
		
		protected function _get_parent_options($parent_ids, $selected) {
			$pages = get_transient('cfct-build-loop-parent_pages');
			if (empty($pages)) {
				$page_opts = array(
					'include' => $parent_ids
				);
				$pages = get_pages($page_opts);
				set_transient('cfct-build-loop-parent_pages', $html, 3600);
			}
			
			$html = ''; // set it just to be sure we're clean
			if (!empty($pages)) {
				foreach ($pages as $pages) {
					$html .= '
						<option value="'.esc_attr($pages->ID).'" '.selected($pages->ID, $selected, false).'>'.esc_html($pages->post_title).'</option>';
				}
				
			}
			return $html;
		}
		
		protected function _get_parent_pages_ids($exclude = 0) {
			global $wpdb;
			return $wpdb->get_col( $wpdb->prepare("SELECT DISTINCT post_parent FROM $wpdb->posts WHERE post_parent != %d AND post_type = 'page' ORDER BY menu_order", $exclude) );
		}
	}

	// Register our module...
	cfct_build_register_module('cfct-module-loop-subpages','cfct_module_loop_subpages');

	/**
	 * Hook for clearing the page parent options transient
	 * Doesn't fire inside ajax since save_post is run on each module save
	 *
	 * @param int $post_id 
	 * @param object $post 
	 */
	function cfct_module_loop_subpages_post_save($post_id, $post) {
		global $cfct_build;
		if (!$cfct_build->in_ajax() && $post->post_type == 'page') {
			delete_transient('cfct-build-loop-parent_page_options');
		}
	}
	add_action('save_post', 'cfct_module_loop_subpages_post_save', 10, 2);
}
?>