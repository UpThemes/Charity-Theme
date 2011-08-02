<?php

if (!class_exists('cfct_row_two_col_float_left')) {
	class cfct_row_two_col_float_left extends cfct_build_row {
		public function __construct() {
			$config = array(
				'name' => __('Full Column, Left Float', 'carrington-build'),
				'description' => __('A full-width column with a second column floated to the left. Contents of the full-width column wrap around the floated column.', 'carrington-build'),
				'icon' => '2col-1floatleft/icon.png',
				'class' => 'cfct-row-float-a',
				'html' => '<div id="{id}" class="{class}">{block_0}{block_1}</div>', // specify each block instead of {blocks} to dictate the output order
				'blocks' => array(
					array(
						'class' => 'cfct-block-float-a cfct-block-a', // use built in styles for admin layout
						'html' => '<div id="{id}" class="cfct-block cfct-block-float-a">{module}</div>' // use our own styles for client side layout
					),
					array(
						'class' => 'cfct-block-float-abc cfct-block-bc', // use built in styles for admin layout
						'html' => '<div id="{id}" class="cfct-block cfct-block-abc">{module}</div>' // use our own styles for client side layout
					)
				)
			);
			parent::__construct($config);
		}
		
		public function css() {
			return apply_filters('cfct_row_two_col_float_left_css', '
				.cfct-row-float-a .cfct-block-float-abc {
					float: none;
					width: auto;
				}
				.cfct-row-float-a .cfct-block-float-a {
					float: left;
				}
				');
		}
	}
	cfct_build_register_row('two-col-float-left', 'cfct_row_two_col_float_left');
}

?>