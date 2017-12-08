<?php

class WP_Dominant_Color {

	/**
	 * Mime types we can accept
	 *
	 * @var array
	 */
	private $valid_mime_types = array(
		'image/jpg',
		'image/jpeg',
		'image/png',
		'image/gif'
	);

	/**
	 * Add hooks to update attachment on
	 */
	public function __construct() {
		add_action( 'add_attachment', array( $this, 'update_attachment' ) );
		add_action( 'edit_attachment', array( $this, 'update_attachment' ) );
	}

	/**
	 * Update the post meta of the attachment with rgb and hex color values
	 *
	 * @param  int $post_id
	 * @return void
	 */
	public function update_attachment( $post_id ) {

		$mime_type = get_post_mime_type( $post_id );
		$url = wp_get_attachment_url( $post_id );

		// make sure theres an image url and valid mime type
		if ( $url && in_array( $mime_type, $this->valid_mime_types ) ) {

			$color = $this->get_image_color( $url, $mime_type );

			if ( $color ) {
				update_post_meta( $post_id, 'dominant_color_rgb', $color['rgb'] );
				update_post_meta( $post_id, 'dominant_color_hex', $color['hex'] );
			}
		}
	}

	/**
	 * Gets the dominant color of an image
	 *
	 * @param  string $url
	 * @param  string $type mime-type of image
	 * @return bool|array
	 */
	private function get_image_color( $url, $type ) {

		switch ( $type ) {

			case 'image/jpeg':
			case 'image/jpg':
				$im = imagecreatefromjpeg( $url );
				break;

			case 'image/png':
				$im = imagecreatefrompng( $url );
				break;

			case 'image/gif':
				$im = imagecreatefromgif( $url );
				break;

			default:
				return false;
		}

		// get width and height
		$w = imagesx($im);
		$h = imagesy($im);

		// make a 1x1 image
		$dest = imagecreatetruecolor(1, 1);

		// copy the original image into the 1x1
		imagecopyresampled( $dest, $im, 0, 0, 0, 0, 1, 1, $w, $h );

		// get the color value at 0, 0
		$index = imagecolorat( $im, 0, 0 );

		// translate into something more parseable
		$rgb = imagecolorsforindex( $im, $index );

		// get the hex value too
		$hex = sprintf( '#%02x%02x%02x', $rgb['red'], $rgb['green'], $rgb['blue'] );

		return array(
			'rgb' => $rgb,
			'hex' => $hex
		);
	}
}
new WP_Dominant_Color();
