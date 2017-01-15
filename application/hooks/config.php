<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Config extends CI_Controller {

	function config() {
		$CI = &get_instance();
		$CI->load->model('core', 'core');
		foreach ($CI->core->readSettings()->result() as $app_configs) {
			$CI->config->set_item($app_configs->key, $app_configs->value);
		}
		if ($CI->config->item('app_default_timezone')) {
			date_default_timezone_set($CI->config->item('app_default_timezone'));
		} else {
			date_default_timezone_set('Asia/Jakarta');
		}
	}
}

