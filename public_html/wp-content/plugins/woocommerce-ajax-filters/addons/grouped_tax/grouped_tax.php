<?php
class BeRocket_aapf_grouped_tax_addon extends BeRocket_framework_addon_lib {
	public $addon_file = __FILE__;
	public $plugin_name = 'ajax_filters';
	public $php_file_name   = '%plugindir%/business/addons/grouped_tax/grouped_tax_include';
	function get_addon_data() {
		$data = parent::get_addon_data();
		return array_merge($data, array(
			'addon_name'    => __('Grouped Attributes and Taxonomy', 'BeRocket_AJAX_domain'),
			'tooltip'       => __('', 'BeRocket_AJAX_domain'),
			'business'      => true
		));
	}
}
new BeRocket_aapf_grouped_tax_addon();
