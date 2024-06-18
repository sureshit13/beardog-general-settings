<?php 
if (!defined('WPINC')) {
    die;
}
function convert_uploaded_images_to_webp( $attachment_id ) {
    // Check if GD library is available
    if ( function_exists( 'imagewebp' ) ) {
        $mime_type = get_post_mime_type( $attachment_id );
        // Check if the uploaded file is an image
        if ( strpos( $mime_type, 'image' ) !== false ) {
            $file_path = get_attached_file( $attachment_id );

            // Check if image is not already in WebP format
            if ( ! preg_match( '/\.webp$/i', $file_path ) ) {
                $image = wp_get_image_editor( $file_path );

                if ( ! is_wp_error( $image ) ) {
                    // Convert and save as WebP with lossless compression
                    $webp_file_path = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file_path );
                    $image->set_quality( 100 ); // Set quality to 100 for lossless compression
                    $image->save( $webp_file_path, 'image/webp' );

                    // Update attachment metadata
                    update_attached_file( $attachment_id, $webp_file_path );
                } else {
                    // Handle WP_Error
                    $error_message = $image->get_error_message();
                    // Return a custom error message
                    wp_send_json_error( $error_message );
                }
            }
        }
    }
}
add_action( 'add_attachment', 'convert_uploaded_images_to_webp' );
