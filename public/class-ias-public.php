<?php

if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'IAS_Public' ) ) :

class IAS_Public {

	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'add_lazy_attributes' ) );
	}

	function add_lazy_attributes( $attr ) {
		$attr['class'] .= ' lazy';
		$attr['data-src'] = $attr['src'];

		if ( isset( $attr['srcset'] ) )
			$attr['data-srcset'] = $attr['srcset'];

		if ( isset( $attr['sizes'] ) )
			$attr['data-sizes'] = $attr['sizes'];

		return $attr;
	}

	function scripts() {
		wp_enqueue_script( 'lazyload', plugins_url( 'js/vendor/lazyload.min.js', __FILE__ ), array(), Images_Advanced_Settings::$version, true );
		wp_enqueue_script( 'ias-public', plugins_url( 'js/ias-public.js', __FILE__ ), array(), Images_Advanced_Settings::$version, true );
	}
}

new IAS_Public();

endif;

