<?php

/**
 * 3 Column Row
 *
 * @package Carrington Build
 */
if (!class_exists('cfct_row_abc')) {
	class cfct_row_abc extends cfct_build_row {
		public function __construct() {
			$config = array(
				'name' => __('3 Column', 'carrington-build'),
				'description' => __('A 3 column row.', 'carrington-build'),
				'icon' => '3col/icon.png',
				'class' => 'cfct-row-a-b-c',
				'blocks' => array(
					array(
						'class' => 'cfct-block-a',
					),
					array(
						'class' => 'cfct-block-b',
					),
					array(
						'class' => 'cfct-block-c',
					)
				)
			);
			parent::__construct($config);
		}
	}
	cfct_build_register_row('row-abc', 'cfct_row_abc');
}

?>