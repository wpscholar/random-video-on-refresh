<?php

/**
 *
 * Plugin Name:       Random Video on Refresh
 * Description:       Show a random video on page refresh.
 * Author:            Kris Cochran
 * Author URI:        https://github.com/Kcor555
 * Version:           1.0
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Copyright 2021 by Kris Cochran - All rights reserved.
 */

function shortcode_init() {
	add_shortcode(
		'random_video_on_refresh',
		function ( $atts, $content, $tag ) {

			$atts = shortcode_atts( array( 'videos' => '' ), $atts );

			$videos = explode( ',', $atts['videos'] );

			foreach ( $videos as $index => $video ) {
				$videos[ $index ] = trim( $video );
			}

			$videos = array_filter( $videos );

			$key = array_rand( $videos );

			global $wp_embed;

			return $wp_embed->run_shortcode(
				'[embed]' . $videos[ $key ] . '[/embed]' );
		}
	);
}

add_action( 'init', 'shortcode_init' );
