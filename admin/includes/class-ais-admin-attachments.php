<?php

if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AIS_Admin_Attachments' ) ) :

class AIS_Admin_Attachments {

    /**
	 * Get attachments ids
	 *
     * @param string $size_name Size name
	 * @return array
	 */
    static function get_attachments_ids( $size_name = NULL ) {
		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
			'post_mime_type' => array( 'image/jpeg', 'image/gif', 'image/png' ),
			'fields'         => 'ids'
		);

		if ( !is_null( $size_name ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_wp_attachment_metadata',
					'value'   => '"' . $size_name . '"',
					'compare' => 'LIKE'
				)
			);
		}

		$args = apply_filters( 'ais_attachments_args', $args );

		return get_posts( $args );
	}

	private function remove_file( $file_path ) {
		$url_path = AIS_Admin_Helpers::url_wrapper( AIS_Admin_Helpers::get_url_path( $file_path ) );
		if ( !file_exists( $file_path ) )
			return AIS_Admin_Helpers::get_result_array( false, sprintf( __( 'File does not exist and can not be removed: %s', 'advanced-image-settings' ), $url_path ) );

		wp_delete_file( $file_path );

		if ( !file_exists( $file_path ) )
			return AIS_Admin_Helpers::get_result_array( true, sprintf( __( 'File has been removed: %s', 'advanced-image-settings' ), $url_path ) );
		else
			return AIS_Admin_Helpers::get_result_array( false, sprintf( __( 'File can not be removed due to unknown error: %s', 'advanced-image-settings' ), $url_path ) );
	}

	private function get_attachment_sizes( $attachment_id ) {
		$attachment_metadata = wp_get_attachment_metadata( $attachment_id );
		return $attachment_metadata['sizes'];
	}

	private function remove_files( $attachment_id ) {
		$file_info = pathinfo( get_attached_file( $attachment_id ) );
		$dir = opendir( $file_info['dirname'] );
		$results = array();

		if ( $dir === false )
			return AIS_Admin_Helpers::get_result_array( false, sprintf( __( 'Directory "%s" can not be opened', 'advanced-image-settings' ), $file_info['dirname'] ) );

		while ( ( $file = readdir( $dir ) ) !== false ) {
			if ( strrpos( $file, $file_info['filename'] ) !== false )
				$files[] = $file;
		}

		closedir( $dir );

		if ( empty( $files ) )
			return AIS_Admin_Helpers::get_result_array( false, sprintf( __( 'There is no file found for "%s"', 'advanced-image-settings' ), $file_info['filename'] ) );

		foreach ( $files as $file_name ) {
			$file_path = $file_info['dirname'] . DIRECTORY_SEPARATOR . $file_name;
			$file_dimensions = explode( $file_info['dirname'] . DIRECTORY_SEPARATOR . $file_info['filename'], $file_path );
	
			if ( count( explode( 'x', $file_dimensions[1] ) ) === 2 )
				$results[] = $this->remove_file( $file_path );
		}

		return $results;
	}

	private function remove_attachment_size_file( $attachment_id, $size_name ) {
		$attachment_sizes = $this->get_attachment_sizes( $attachment_id );

		if ( !isset( $attachment_sizes[ $size_name ] ) )
			return AIS_Admin_Helpers::get_result_array( false, sprintf( __( 'Size %s was not found for this file', 'advanced-image-settings' ), $size_name ) );

		$dirname = dirname( get_attached_file( $attachment_id ) );
		$file_path = $dirname . DIRECTORY_SEPARATOR . $attachment_sizes[ $size_name ]['file'];

		return $this->remove_file( $file_path );
	}

	function remove_size_file() {
		check_admin_referer( 'advanced-image-settings', 'nonce' );

		set_time_limit(0);

		$attachment_id = $_POST['attachment_id'];
		
		if ( !isset( $attachment_id ) || empty( $attachment_id ) )
			wp_send_json_error( __( 'Attachment ID is missing', 'advanced-image-settings' ) );

		if ( !is_numeric( $attachment_id ) )
			wp_send_json_error( sprintf( __( 'Attachment ID "%d" is incorrect', 'advanced-image-settings' ), $attachment_id ) );

		$image_path = get_attached_file( $attachment_id );

		if ( $image_path === '' )
			wp_send_json_error( sprintf( __( 'File not found for ID "%d"', 'advanced-image-settings' ), $attachment_id ) );

		$file_info = pathinfo( $image_path );
		$result = array(
			'path' => $image_path,
			'id' => $attachment_id,
			'name' => $file_info['filename']
		);

		$result['results'][] = $this->remove_attachment_size_file( $attachment_id, $_POST['size_name'] );

		ob_start();
		include AIS_Admin_Helpers::get_view('ais-admin-part-log');

		wp_send_json_success( ob_get_clean() );
	}

	function get_all_attachments() {
		check_admin_referer( 'advanced-image-settings', 'nonce' );

		$attachments_ids = $this->get_attachments_ids();

		if ( empty( $attachments_ids ) )
			wp_send_json_error( __( 'There is no attachment', 'advanced-image-settings' ) );

		wp_send_json_success( $attachments_ids );
	}

	function regenerate_attachment() {
		check_admin_referer( 'advanced-image-settings', 'nonce' );

		set_time_limit(0);

		$attachment_id = $_POST['attachment_id'];
		
		if ( !isset( $attachment_id ) || empty( $attachment_id ) )
			wp_send_json_error( __( 'Attachment ID is missing', 'advanced-image-settings' ) );

		if ( !is_numeric( $attachment_id ) )
			wp_send_json_error( sprintf( __( 'Attachment ID "%d" is incorrect', 'advanced-image-settings' ), $attachment_id ) );

		$image_path = get_attached_file( $attachment_id );

		if ( $image_path === '' )
			wp_send_json_error( sprintf( __( 'File not found for ID "%d"', 'advanced-image-settings' ), $attachment_id ) );

		$file_info = pathinfo( $image_path );
		$result = array(
			'path' => $image_path,
			'id'   => $attachment_id,
			'name' => $file_info['filename']
		);
		$url_path = AIS_Admin_Helpers::get_url_path( $file_info['dirname'] );

		$result['results'] = $this->remove_files( $attachment_id );
		$meta = wp_generate_attachment_metadata( $attachment_id, $image_path );
		if ( !empty( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $size => $size_info ) {
				$size_url_path = $url_path . DIRECTORY_SEPARATOR . $size_info['file'];
				$result['results'][] = AIS_Admin_Helpers::get_result_array( true, sprintf( __( '%s size has been generated: %s', 'advanced-image-settings' ), $size, AIS_Admin_Helpers::url_wrapper( $size_url_path ) ) );
			}
		} else {
			$result['results'][] = AIS_Admin_Helpers::get_result_array( true, sprintf( __( 'There is no more regeneration to do with this attachment', 'advanced-image-settings' ) ) );
		}
		wp_update_attachment_metadata( $attachment_id, $meta );

		ob_start();
		include AIS_Admin_Helpers::get_view('ais-admin-part-log');

		wp_send_json_success( ob_get_clean() );
	}
}

endif;