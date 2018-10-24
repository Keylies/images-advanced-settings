<?php

if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AIS_Admin' ) ) :

class AIS_Admin {

	private $hook;
	private $ais_sizes;
	private $ais_attachments;
	private $data;

	function __construct() {
		$this->load_dependencies();

		add_action( 'admin_menu', array( $this, 'option_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );

		// Sizes
		add_action( 'wp_ajax_ais_disable_default', array( $this->ais_sizes, 'disable_default' ) );
		add_action( 'wp_ajax_ais_add_size', array( $this->ais_sizes, 'add_size' ) );
		add_action( 'wp_ajax_ais_update_sizes', array( $this->ais_sizes, 'update_sizes' ) );
		add_action( 'wp_ajax_ais_remove_size', array( $this->ais_sizes, 'remove_size' ) );
		
		add_filter( 'intermediate_image_sizes_advanced', array( $this->ais_sizes, 'disable_sizes_generation' ) );
		
		// Attachments
		add_action( 'wp_ajax_ais_remove_size_file', array( $this->ais_attachments, 'remove_size_file' ) );
	 	add_action( 'wp_ajax_ais_regenerate_attachment', array( $this->ais_attachments, 'regenerate_attachment' ) );
		add_action( 'wp_ajax_ais_get_all_attachments', array( $this->ais_attachments, 'get_all_attachments' ) );
	}

	private function load_dependencies() {
		include_once 'includes/class-ais-admin-helpers.php';

		include_once 'includes/class-ais-admin-sizes.php';
		$this->ais_sizes = new AIS_Admin_Sizes();

		include_once 'includes/class-ais-admin-attachments.php';
		$this->ais_attachments = new AIS_Admin_Attachments();
	}

	/**
	 * Get all AJAX actions
	 *
	 * @return array
	 */
	private function get_actions() {
		return array(
			'default'           => 'ais_disable_default',
			'add'               => 'ais_add_size',
			'update'            => 'ais_update_sizes',
			'remove'            => 'ais_remove_size',
			'regenerate'        => 'ais_regenerate_attachment',
			'getAllAttachments' => 'ais_get_all_attachments',
			'removeSizeFile'    => 'ais_remove_size_file'
		);
	}

	/**
	 * Get JS translated messages
	 *
	 * @return array
	 */
	private function get_messages() {
		return apply_filters( 'ais_messages',
			array(
				'ajaxFailure' => array(
					'server'     => __( 'Server error, please retry', 'wpas' ),
					'connection' => __( 'Connection error, please retry', 'wpas' )
				)
			)
		);
	}

	/**
	 * Get plugin sections for tabs
	 *
	 * @return array
	 */
	private function get_view_sections() {
		return array(
			'sizes'        => __( 'Sizes', 'ais' ),
			'regeneration' => __( 'Regeneration', 'ais' )
		);
	}

	/**
	 * Create plugin option page
	 *
	 * @return void
	 */
	function option_page() {
		$this->hook = add_options_page(
			__( 'Image settings', 'ais' ),
			__( 'Image settings', 'ais' ),
			'manage_options',
			'image-settings',
			array( $this, 'page_display' )
		);
	}

	/**
	 * Display plugin option page
	 *
	 * @return void
	 */
	function page_display() {
		if ( !current_user_can( 'manage_options' ) ) return;

		$crop_positions = $this->ais_sizes->get_crop_positions();
		$sections = $this->get_view_sections();
		$default_sizes = array_diff( get_intermediate_image_sizes(), $this->ais_sizes->get_custom_sizes_names() );
		$this->data = $this->ais_sizes->data;

		include AIS_Admin_Helpers::get_view('ais-admin-page');
	}

	function scripts( $hook ) {
		if ( $this->hook !== $hook ) return;

		wp_enqueue_script( 'ais-admin', plugins_url( 'js/ais-admin.js', __FILE__ ), array(), Advanced_Image_Settings::$version, true );
		wp_localize_script( 'ais-admin', 'AIS',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'actions'  => $this->get_actions(),
				'nonce'    => wp_create_nonce( 'advanced-image-settings' ),
				'messages' => $this->get_messages(),
			)
		);
	}

	function styles( $hook ) {
		if ( $this->hook !== $hook ) return;

		wp_enqueue_style( 'ais-admin', plugins_url( 'css/ais-admin.css', __FILE__ ), array(), Advanced_Image_Settings::$version );
	}
}

new AIS_Admin();

endif;

