<?php

/**
 * 2 Column Row
 *
 * @package Carrington Build
 */
if (!class_exists('cfct_row_ab')) {
	class cfct_row_ab extends cfct_build_row {
		public function __construct() {
			$config = array(
				'name' => __('2 Columns', 'carrington-build'),
				'description' => __('A 2 column row.', 'carrington-build'),
				'icon' => '2col/icon.png',
				'class' => 'cfct-row-d-e',
				'blocks' => array(
					array(
						'class' => 'cfct-block-d',
					),
					array(
						'class' => 'cfct-block-e',
					)
				)
			);
			parent::__construct($config);
		}
	}
	cfct_build_register_row('row-ab', 'cfct_row_ab');
}

?>