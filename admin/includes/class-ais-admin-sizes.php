<?php

if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AIS_Admin_Sizes' ) ) :

class AIS_Admin_Sizes {

	public $data;
    private $option_name;
    private $images_types;

	function __construct() {
		$this->option_name = 'advanced_image_settings';
		$this->init_data();
	}

	/**
	 * Build default structure for option
	 *
	 * @return array
	 */
	private function get_default_data() {
		return array(
			'default_sizes_disabled' => array(),
			'sizes'                  => array()
		);
	}

	/**
	 * Register custom image sizes
	 *
	 * @return void
	 */
	private function set_image_sizes() {
		if ( empty( $this->data['sizes'] ) ) return;

		foreach ( $this->data['sizes'] as $size ) {
			$crop = $size['crop'] ? $this->get_crop_position_array( $size['crop_position'] ) : false;
			add_image_size( $size['name'], $size['width'], $size['height'], $crop );
		}
	}

	/**
	 * Get option data or add option with default data if not exists
	 *
	 * @return void
	 */
	private function init_data() {
		$this->images_types = apply_filters( 'ais_images_types', 
			array(
				'jpeg',
				'jpg',
				'png',
				'gif'
			)
		);

		$current_data = get_option( $this->option_name );

		if ( !empty( $current_data ) ) {
			$this->data = $current_data;
			$this->set_image_sizes();
		} else {
			add_option( $this->option_name, $this->get_default_data() );
		}
	}

	/**
	 * Explode crop positions string to array
	 *
	 * @param string $crop_position A string with x and y crop position
	 * @return array
	 */
	private function get_crop_position_array( $crop_position ) {
		return explode( '_', $crop_position );
    }
    
	/**
	 * Update image sizes option
	 *
	 * @return void
	 */
	private function update_option() {
		update_option( $this->option_name, $this->data );
    }

    /**
	 * Return generated HTML of custom sizes view
	 *
	 * @return string
	 */
	private function get_custom_sizes_view() {
		ob_start();
		$crop_positions = $this->get_crop_positions();

		include AIS_Admin_Helpers::get_view('ais-admin-part-custom-sizes');

		return ob_get_clean();
    }

    /**
	 * Test if a dimension is correct
	 *
	 * @param int $field_value Dimension
	 * @return boolean
	 */
	private function is_good_dimension( $field_value ) {
		$is_good = is_numeric( $field_value ) && $field_value > 0;

		return apply_filters( 'ais_dimension', $is_good );
    }

    /**
	 * Return a sanitized size name
	 *
	 * @param string $size Size name
	 * @return string
	 */
    private function sanitize_size( $size ) {
		foreach( $size as $key => $value )
			$size[ $key ] = sanitize_text_field( $value );

		return $size;
	}

    private function validate_size( $size, $update = false ) {
		$required_keys = array( 'disabled', 'width', 'height', 'crop', 'crop_position' );
		if ( !$update )
			$required_keys[] = 'name';
		$required_keys = apply_filters( 'ais_required_keys', $required_keys );

		$wp_error = new WP_Error();

		foreach ( $required_keys as $key ) {
			if ( !isset( $size[ $key ] ) ) {
				$wp_error->add( 'missing-field', sprintf( __( '%s field is missing', 'advanced-image-settings' ), $key ) );
			} elseif ( $key !== 'crop' && $key !== 'disabled' && empty( $size[ $key ] ) ) {
				$wp_error->add( 'empty-field', sprintf( __( '%s field is empty', 'advanced-image-settings' ), $key ) );
			} elseif ( ( $key === 'width' || $key === 'height' ) && !$this->is_good_dimension( $size[ $key ] ) ) {
				$wp_error->add( 'bad-dimension', sprintf( __( '%s dimension is invalid', 'advanced-image-settings' ), $key ) );
			} elseif ( !$update && $key === 'name' ) {
				$current_sizes = get_intermediate_image_sizes();

				if ( in_array( $size[ $key ], $current_sizes ) )
					$wp_error->add( 'existing-name', sprintf( __( '%s already exists', 'advanced-image-settings' ), $size[ $key ] ) );
			}
		}

		return !empty( $wp_error->get_error_messages() ) ? $wp_error : true;
	}

	/**
     * Get names of all custom sizes
	 *
     * @return array
	 */
    function get_custom_sizes_names() {
        $sizes_names = array();
        
		foreach ( $this->data['sizes'] as $custom_size ) {
            $sizes_names[] = $custom_size['name'];
		}
        
		return $sizes_names;
	}

    /**
     * Get all possible crop positions
     *
     * @return array
     */
    function get_crop_positions() {
        $x = array(
            'center' => __( 'Center', 'advanced-image-settings' ),
            'left'   => __( 'Left', 'advanced-image-settings' ),
            'right'  => __( 'Right', 'advanced-image-settings' ),
        );

        $y = array(
            'center' => __( 'center', 'advanced-image-settings' ),
            'top'    => __( 'top', 'advanced-image-settings' ),
            'bottom' => __( 'bottom', 'advanced-image-settings' ),
        );

        foreach ( $x as $x_pos => $x_pos_label )
            foreach ( $y as $y_pos => $y_pos_label )
                $crops[ $x_pos . '_' . $y_pos ] = $x_pos_label . ' ' . $y_pos_label;

        return $crops;
    }

	/**
	 * (AJAX) Update option with array of disabled default sizes
	 *
	 * @return void
	 */
	function disable_default() {
		check_admin_referer( 'advanced-image-settings', 'nonce' );

		$this->data['default_sizes_disabled'] = !isset( $_POST['default_sizes_disabled'] ) ? array() : $_POST['default_sizes_disabled'];
		$this->update_option();

		wp_send_json_success( array(
			'message' => __( 'Disabled', $this->plugin_name )
		) );
    }

    /**
	 * Filter for disable a size eneration on image upload
	 *
	 * @param array $sizes Sizes which are generated on image upload
	 * @return array
	 */
	function disable_sizes_generation( $sizes ) {
		foreach( $this->data['sizes'] as $size ) {

			if ( $size['disabled'] === '1' )
				unset( $sizes[ $size['name'] ] );
		}
		foreach( $this->data['default_sizes_disabled'] as $size )

			if ( in_array( $size, array_keys( $sizes ) ) ) {
				unset( $sizes[ $size ] );
		}

		return $sizes;
    }

    /**
	 * (AJAX) Add a custom size
	 *
	 * @return void
	 */
    function add_size() {
		check_admin_referer( 'advanced-image-settings', 'nonce' );

		$validation = $this->validate_size( $_POST['new_size'] );

		if ( is_wp_error( $validation ) )
			wp_send_json_error( array( 'message' => $validation->get_error_messages() ) );
 
		$this->data['sizes'][] = $this->sanitize_size( $_POST['new_size'] );
		$this->update_option();

		wp_send_json_success( array(
			'message' => __( 'Image size added', $this->plugin_name ),
			'content' => $this->get_custom_sizes_view()
		) );
    }

    /**
	 * (AJAX) Update custom sizes
	 *
	 * @return void
	 */
    function update_sizes() {
		check_admin_referer( 'advanced-image-settings', 'nonce' );

		$updated_sizes = $_POST['updated_sizes'];
		$sizes_number = count( $updated_sizes['name'] );
		$formated_sizes = array();

		for ( $i = 0, $l = $sizes_number; $i < $l; $i++ ) {
			$formated_size = array(
				'disabled'      => $updated_sizes['disabled'][ $i ],
				'name'          => $updated_sizes['name'][ $i ],
				'width'         => $updated_sizes['width'][ $i ],
				'height'        => $updated_sizes['height'][ $i ],
				'crop'          => $updated_sizes['crop'][ $i ],
				'crop_position' => $updated_sizes['crop_position'][ $i ]
			);

			$validation = $this->validate_size( $formated_size, true );
			if ( is_wp_error( $validation ) )
				wp_send_json_error( array( 'message' => $validation->get_error_messages() ) );

			$formated_sizes[] = $this->sanitize_size( $formated_size );
		}

		$this->data['sizes'] = $formated_sizes;
		$this->update_option();

		wp_send_json_success( array(
			'message' => __( 'Image sizes updated', 'advanced-image-settings' ),
			'content' => $this->get_custom_sizes_view()
		) );
	}

	/**
	 * (AJAX) Remove a custom size
	 *
	 * @return void
	 */
	function remove_size() {
		check_admin_referer( 'advanced-image-settings', 'nonce' );

		$size_name = $this->data['sizes'][ $_POST['index'] ]['name'];

		remove_image_size( $size_name );
		unset( $this->data['sizes'][ $_POST['index'] ] );

		$this->data['sizes'] = array_values( $this->data['sizes'] );
		$this->update_option();

		$return = array(
			'message' => __( 'Image size deleted', 'advanced-image-settings' ),
			'content' => $this->get_custom_sizes_view()
		);

		if ( isset( $_POST['remove_images'] ) && $_POST['remove_images'] !== 'false' ) {
			$ais_attachments = new AIS_Admin_Attachments();
			$attachments_ids = $ais_attachments->get_attachments_ids( $size_name );
			$return['attachments_ids'] = !empty( $attachments_ids ) ? $attachments_ids : __( 'No file with this size was found', 'advanced-image-settings' );
			$return['size_name'] = $size_name;
		}

		wp_send_json_success( $return );
	}
}
endif;