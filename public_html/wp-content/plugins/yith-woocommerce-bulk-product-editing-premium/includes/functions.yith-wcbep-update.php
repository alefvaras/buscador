<?php
/**
 * Update functions
 *
 * @package YITH\BulkEditing\Functions
 */

if ( ! function_exists( 'yith_wcbep_update_230_table_views_conditions' ) ) {
	/**
	 * Update table views conditions
	 *
	 * @return void
	 */
	function yith_wcbep_update_230_table_views_conditions() {
		$table_views = yith_wcbep_table_views()->get_table_views_option();

		foreach ( $table_views as &$view ) {
			foreach ( $view['conditions'] as &$condition ) {

				if ( 'shipping-class' === $condition['type'] ) {
					if ( array_key_exists( 'value', $condition['options'] ) ) {
						$condition['options'] = array(
							'condition'  => 'is',
							'taxonomies' => array(
								$condition['options']['value']['id'] => $condition['options']['value']['name'],
							),
						);
					}
				}
			}
		}

		yith_wcbep_table_views()->update_table_views( $table_views );

	}
}
