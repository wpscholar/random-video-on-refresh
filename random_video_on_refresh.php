<?php

/**
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
		'random_vimeo_on_refresh',
		function ( $atts, $content, $tag ) {

			$atts = shortcode_atts(
				[
					'ids' => '653004282, 646622427',
				],
				$atts,
				$tag
			);

			$ids = array_filter( array_map( 'trim', explode( ',', $atts['ids'] ) ) );

			$key = array_rand( $ids );
			$id  = $ids[ $key ];

			$query_string = http_build_query(
				[
					'autoplay'   => 1,
					'loop'       => 1,
					'background' => 1,
					'title'      => 0,
					'byline'     => 0,
					'portrait'   => 0
				],
				null,
				'&'
			);

			$url = esc_url( "https://player.vimeo.com/video/{$id}?{$query_string}" );

			return <<<HTML
<div style="padding:39.95% 0 0 0;position:relative;">
	<iframe
		src="{$url}"
		allow="autoplay; fullscreen; picture-in-picture"
		allowfullscreen=""
		frameborder="0"
		style="position:absolute;top:0;left:0;width:100%;height:100%;"
	></iframe>
</div>
<script src="https://player.vimeo.com/api/player.js"></script>
HTML;
		},
		10,
		3
	);

	add_shortcode(
		'random_video_on_refresh',
		function ( $atts, $content, $tag ) {

			$atts = shortcode_atts(
				array(
					'autoplay' => 'off',
					'height'   => '',
					'loop'     => 'off',
					'poster'   => '',
					'videos'   => '',
					'width'    => '',
				),
				$atts
			);

			$videos = explode( ',', $atts['videos'] );

			foreach ( $videos as $index => $video ) {
				$videos[ $index ] = trim( $video );
			}

			$videos = array_filter( $videos );

			$key = array_rand( $videos );

			$props = [
				'src' => $videos[ $key ],
			];

			if ( $atts['autoplay'] !== 'off' ) {
				//$props['autoplay'] = 'on';
			}

			if ( $atts['loop'] !== 'off' ) {
				$props['loop'] = 'on';
			}

			if ( ! empty( $atts['poster'] ) ) {
				$props['poster'] = $atts['poster'];
			}

			if ( ! empty( $atts['width'] ) ) {
				$props['width'] = absint( $atts['width'] );
			}

			if ( ! empty( $atts['height'] ) ) {
				$props['height'] = absint( $atts['height'] );
			}

			$att_string = implode(
				' ',
				array_map(
					function ( $key ) use ( $props ) {
						return "$key=\"$props[$key]\"";
					},
					array_keys( $props )
				)
			);

			$shortcode = '[video ' . $att_string . ']';

			$id = md5( $shortcode );

			$html = <<<HTML
<div id="$id">
	$shortcode
</div>
HTML;

			$style = <<<HTML
<style>
	#$id .wp-video {
		width: 100% !important;	
	}
	#$id .mejs-container {
		height: 0 !important;
		padding-bottom: 56.25%;
	}
	#$id iframe {
		max-height: 100%;
	}
</style>
HTML;

			$js = <<<HTML
<script>
{
	
	const el = document.getElementById('$id');
	
	const debounce = (callback, wait) => {
	  let timeoutId = null;
	  return (...args) => {
	    window.clearTimeout(timeoutId);
	    timeoutId = window.setTimeout(() => {
	      callback.apply(null, args);
	    }, wait);
	  };
	}
	
	const isInViewport = (element) => {
	    const rect = element.getBoundingClientRect();
	    return (
	        rect.top >= 0 &&
	        rect.left >= 0 &&
	        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
	        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
	    );
	}
	
	let addAutoplay = () => {
	  window[el.querySelector('mediaelementwrapper').getAttribute('id')].play();
	}
	
	const onScroll = debounce(
		() => {
			const isVisible = isInViewport(el);
			if(isVisible) {
				if(addAutoplay) {
					addAutoplay();
					addAutoplay = null;
					document.removeEventListener('scroll', onScroll, {passive: true});
				}
			}					
		},
		200
  );
			
	document.addEventListener('scroll', onScroll, {passive: true});
}
</script>
HTML;

			return do_shortcode( $html ) . $style . $js;
		}
	);
}

add_action( 'init', 'shortcode_init' );
