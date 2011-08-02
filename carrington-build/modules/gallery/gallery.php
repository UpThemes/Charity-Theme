<?php
if (!class_exists('cfct_module_image')) {
	require_once(dirname(dirname(__FILE__)).'/image/image.php');
}
if (!class_exists('cfct_module_gallery') && class_exists('cfct_module_image')) {
	class cfct_module_gallery extends cfct_module_image {
		
		/**
		 * Set up the module
		 */
		public function __construct() {
			$opts = array(
				'description' => __('Select and insert images as a gallery.', 'carrington-build'),
				'icon' => 'gallery/icon.png'
			);
			
			cfct_build_module::__construct('cfct-module-gallery', __('Gallery', 'carrington-build'), $opts);
		}

		/**
		 * Display the module content in the Post-Content
		 * 
		 * @param array $data - saved module data
		 * @return array string HTML
		 */
		public function display($data) {
			if (!empty($data[$this->get_field_name('post_image')])) {
				$gallery_atts = array(
					'id' => 0,
					'include' => $data[$this->get_field_name('post_image')],
					'size' => $data[$this->get_field_name('post_image').'-size']
				);
				remove_filter('post_gallery', 'cfct_post_gallery', 10, 2);
				$gallery_html = gallery_shortcode($gallery_atts);
				add_filter('post_gallery', 'cfct_post_gallery', 10, 2);
			}
			else {
				$gallery_html = null;
			}
			
			return $this->load_view($data, compact('gallery_html'));
		}

		/**
		 * Build the admin form
		 * 
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function admin_form($data) {
			$html = '
				<div id="'.$this->id_base.'-post-image-wrap">
					'.$this->post_image_selector($data, true).'
				</div>
				';
			return $html;
		}

		/**
		 * Return a textual representation of this module.
		 *
		 * @param array $data - saved module data
		 * @return string text
		 */
		public function text($data) {
			$items = __('No Images Selected', 'carrington-build');
			if (!empty($data[$this->get_field_name('post_image')])) {
				$num_items = count(explode(',', $data[$this->get_field_name('post_image')]));
				$items = $num_items > 1 ? __('1 Image Selected', 'carrington-build') : sprintf(__('%b Images Selected', 'carrington-build'), $num_items);
			}
			return strip_tags('Gallery: '.$items);
		}

		/**
		 * Modify the data before it is saved, or not
		 *
		 * @param array $new_data 
		 * @param array $old_data 
		 * @return array
		 */
		public function update($new_data, $old_data) {
			return $new_data;
		}
		
		/**
		 * Add custom javascript to the post/page admin
		 *
		 * @return string JavaScript
		 */
		public function admin_js() {
			$js = '
				cfct_builder.addModuleSaveCallback("'.$this->id_base.'", function() {
					// find the non-active image selector and clear his value
					$("#'.$this->id_base.'-image-selectors .cfct-module-tab-contents>div:not(.active)").find("input:hidden").val("");
					return true;
				});
			';
			$js .= $this->post_image_selector_js('post_image', array('direction' => 'horizontal'));
			return $js;
		}

	}
	// register the module with Carrington Build
	cfct_build_register_module('cfct-module-gallery', 'cfct_module_gallery');
}
?>