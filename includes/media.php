<?php
/**
 * Media
 *
 * Image and video functions.
 */

/**
 * Enable upscaling of images
 *
 * Normally WordPress will only generate resized/cropped images if the source is larger than the target.
 * This forces an image to be made for all sizes, even if the source is smaller than the target.
 * This makes responsive images work more consistently (automatic height via CSS, for example)
 *
 * Note: This framework feature must be enabled using add_theme_support()
 *
 * Credit: levi (http://wordpress.stackexchange.com/users/19801/levi)
 * via Stack Exchange: http://wordpress.stackexchange.com/a/64953
 */

add_filter( 'image_resize_dimensions', 'image_resize_dimensions_upscale', 10, 6 );

function image_resize_dimensions_upscale( $default, $orig_w, $orig_h, $new_w, $new_h, $crop ) {

	// if not cropping or no theme support, let core function handle it
	if ( ! current_theme_supports( 'ctc-image-upscaling' ) || ! $crop ) {
		return null; 
	}

	$aspect_ratio = $orig_w / $orig_h;
	$size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );

	$crop_w = round( $new_w / $size_ratio );
	$crop_h = round( $new_h / $size_ratio );

	$s_x = floor( ( $orig_w - $crop_w ) / 2 );
	$s_y = floor( ( $orig_h - $crop_h ) / 2 );

	return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );

}

/**
 * Responsive Embeds
 *
 * Add container to WordPress video embeds so it can be styled responsive
 * See embed_oembed_html filter
 * See WordPress embeds: http://codex.wordpress.org/Embeds
 */

add_filter( 'embed_oembed_html', 'ctc_responsive_embeds', 10, 4 ); // make WordPress video embeds responsive by giving container to style	

function ctc_responsive_embeds( $html, $url, $attr, $post_ID ) {

	if ( preg_match( '/^<(iframe|embed|object)/', $html ) ) { // no img
		$html = '<div class="ctc-responsive-embed">' . $html . '</div>';
	}

	return $html;

}

/**
 * Video
 *
 * Return YouTube or Vimeo data, ID and HTML player code based on URL
 */
 
// CAN WP OEMBED HELP MAKE THIS SIMPLER?
// CAN WP OEMBED HELP MAKE THIS SIMPLER?
// CAN WP OEMBED HELP MAKE THIS SIMPLER?
// CAN WP OEMBED HELP MAKE THIS SIMPLER?
// CAN WP OEMBED HELP MAKE THIS SIMPLER?
// CAN WP OEMBED HELP MAKE THIS SIMPLER?

// MAKE NAME MORE DESCRIPTIVE
// MAKE NAME MORE DESCRIPTIVE
// MAKE NAME MORE DESCRIPTIVE
// MAKE NAME MORE DESCRIPTIVE
// MAKE NAME MORE DESCRIPTIVE

// MAKE FILTERABLE WITH ATTS?
// MAKE FILTERABLE WITH ATTS?
// MAKE FILTERABLE WITH ATTS?
// MAKE FILTERABLE WITH ATTS?
// MAKE FILTERABLE WITH ATTS?
 
function ctc_video( $video_url, $width = false, $height = false, $options = array() ) {

	$video = array();
	
	$video_url = isset( $video_url ) ? trim( $video_url ) : '';
	
	if ( ! empty( $video_url ) ) {

		// Default options
		$options['autoplay'] = ! empty( $options['autoplay'] ) ? '1' : '0';
			
		// YouTube
		if ( preg_match( '/youtu/i', $video_url ) ) {
		
			// source
			$video['source'] = 'youtube';
			
			// default size
			$width = ! empty( $width ) ? $width : 560;
			$height = ! empty( $height ) ? $height : 350;
			
			// video ID and embed code
			$video['video_id'] = '';
			$video['embed_code'] = '';
			preg_match( '/.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/', $video_url, $match );
			if ( ! empty( $match[1] ) && strlen( $match[1] ) == 11 ) {
				$video['video_id'] = $match[1];
				$video['embed_code'] = '<iframe src="http://www.youtube.com/embed/' . $video['video_id'] . '?wmode=transparent&amp;autoplay=' . $options['autoplay'] . '&amp;rel=0&amp;showinfo=0&amp;color=white&amp;modestbranding=1" width="' . $width . '" height="' . $height . '" frameborder="0" allowfullscreen></iframe>';
			}				
			
		}
		
		// Vimeo
		else if ( preg_match( '/vimeo/i', $video_url ) ) {
		
			// source
			$video['source'] = 'vimeo';
			
			// default size
			$width = ! empty( $width ) ? $width : 500;
			$height = ! empty( $height ) ? $height : 281;
			
			// video ID and embed code
			$video['video_id'] = '';
			$video['embed_code'] = '';
			preg_match( '/\d+/', $video_url, $match );
			if ( ! empty( $match[0] ) ) {
				$video['video_id'] = $match[0];
				$video['embed_code'] = '<iframe src="http://player.vimeo.com/video/' . $video['video_id'] . '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;autoplay=' . $options['autoplay'] . '" width="' . $width . '" height="' . $height . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
			}
			
		}
		
		// Video Container
		if ( ! empty( $video['embed_code'] ) ) {
			$video['embed_code'] = '<div class="ctc-video-container ctc-' . $video['source'] . '-video">' . $video['embed_code'] . '</div>';
		}
	
	}
	
	return $video;		

}
