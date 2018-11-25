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
	public static $option_name;

	function __construct() {
		self::$version = '1.0.0';
		self::$option_name = 'images_advanced_settings';

		add_action( 'plugins_loaded', array( $this, 'textdomain' ) );

		$this->init_option();

		if ( is_admin() )
			include_once 'admin/class-ias-admin.php';
		else {
			include_once 'public/class-ias-public.php';
		}
	}

	/**
	 * Build default structure for option
	 *
	 * @return array
	 */
	private function get_default_data() {
		return array(
			'default_sizes_disabled' => array(),
			'sizes'                  => array(),
			'lazy_loading'           => '0'
		);
	}

	/**
	 * Add option with default data if not exists
	 */
	private function init_option() {
		if ( empty( get_option( self::$option_name ) ) )
			add_option( self::$option_name, $this->get_default_data() );
	}

	function textdomain() {
		load_plugin_textdomain( 'images-advanced-settings', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
	}
}

new Images_Advanced_Settings();

endif;