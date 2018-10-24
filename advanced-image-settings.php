<?php
/*
Plugin Name: Advanced Image Settings
Description: Additional settings for images as custom sizes or regeneration.
Version: 1.0.0
Author: Clément Leboucher
Author URI: https://github.com/keylies
Text Domain: advanced-image-settings
Domain Path: /languages
*/

if ( !defined( 'ABSPATH' ) ) die;

if ( !class_exists( 'Advanced_Image_Settings' ) ) :

class Advanced_Image_Settings {

	public static $version;

	function __construct() {
		self::$version = '1.0.0';

		add_action( 'plugins_loaded', array( $this, 'textdomain' ) );

		if ( is_admin() )
			include_once 'admin/class-ais-admin.php';
	}

	function textdomain() {
		load_plugin_textdomain( 'advanced-image-settings', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
	}
}

new Advanced_Image_Settings();

endif;