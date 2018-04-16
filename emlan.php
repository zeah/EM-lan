<?php 

/**
	Plugin Name: EM-Lån
	Description: liste over lån for effektiv markedsforing
	Version: 0.0.10
*/

require_once 'inc/emlan-shortcode.php';
require_once 'inc/emlan-posttype.php';

defined('ABSPATH') or die('Blank Space');

define('EMLAN_PLUGIN_URL', plugin_dir_url(__FILE__));

function emlan_init() {

	// if (is_admin())
		Emlan_Posttype::get_instance();
	// else
		Emlan_Shortcode::get_instance();

}

add_action('plugins_loaded', 'emlan_init');