<?php
/*
Plugin Name: Images Advanced Settings
Description: Additional settings for images as custom sizes or regeneration.
Version: 1.0.0
Author: Clément Leboucher
Author URI: https://github.com/keylies
Text Domain: images-advanced-settings
Domain Path: /languages
*/

if ( !defined( 'ABSPATH' ) ) die;

if ( !class_exists( 'Images_Advanced_Settings' ) ) :

class Images_Advanced_Settings {

	public static $version;

	function __construct() {
		self::$version = '1.0.0';

		add_action( 'plugins_loaded', array( $this, 'textdomain' ) );

		if ( is_admin() )
			include_once 'admin/class-ias-admin.php';
	}

	function textdomain() {
		load_plugin_textdomain( 'images-advanced-settings', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
	}
}

new Images_Advanced_Settings();

endif;