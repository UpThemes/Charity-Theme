<?php

/**
 * 2 Column Row, Column 2 is wide
 *
 * @package Carrington Build
 */
if (!class_exists('cfct_row_a_bc')) {
	class cfct_row_a_bc extends cfct_build_row {
		public function __construct() {
			$config = array(
				'name' => __('Left Sidebar', 'carrington-build'),
				'description' => __('2 Columns. The second column is wider than the first.', 'carrington-build'),
				'icon' => '2col-2wide/icon.png',
				'class' => 'cfct-row-a-bc',
				'blocks' => array(
					array(
						'class' => 'cfct-block-a',
					),
					array(
						'class' => 'cfct-block-bc',
					)
				)
			);
			parent::__construct($config);
		}
	}
	cfct_build_register_row('row-a-bc', 'cfct_row_a_bc');
}

?>