<?php
/**
 * Random Video on Refresh
 *
 * @package           RandomVideoOnRefresh
 * @author            Micah Wood
 * @copyright         Copyright 2021 by Micah Wood - All rights reserved.
 * @license           GPL2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Random Video on Refresh
 * Plugin URI:
 * Description:       Show a random video on page refresh.
 * Version:           1.0
 * Requires PHP:      7.0
 * Requires at least: 5.6
 * Author:            Micah Wood
 * Author URI:        https://wpscholar.com
 * Text Domain:       random-video-on-refresh
 * Domain Path:       /languages
 * License:           GPL V2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

function shortcode_init() {

	add_shortcode(
		'random_vimeo_on_refresh',
		function ( $atts, $content, $tag ) {

			$atts = shortcode_atts(
				[
					'ids'        => '653004282, 646622427',
					'autoplay'   => 'true',
					'autopause'  => 'false',
					'background' => 'true',
					'byline'     => 'false',
					'loop'       => 'true',
					'portrait'   => 'false',
					'responsive' => 'true',
					'title'      => 'false',
				],
				$atts,
				$tag
			);

			$ids = array_filter( array_map( 'trim', explode( ',', $atts['ids'] ) ) );

			$idCollection = '[' . esc_js( implode( ',', $ids ) ) . ']';

			$optionNames = array_keys( $atts );
			unset( $optionNames['ids'] );

			$options = [];

			foreach ( $optionNames as $optionName ) {
				$options[ $optionName ] = filter_var( $atts[ $optionName ], FILTER_VALIDATE_BOOL );
			}

			$optionCollection = wp_json_encode( $options );

			$html_id = esc_attr( md5( wp_json_encode( $atts ) ) );

			$js = <<<SCRIPT
{
	const options = {$optionCollection};
	const ids = {$idCollection};
	const id = ids[Math.floor(Math.random() * ids.length)];
	
	options.id = id;
	
	const player = new Vimeo.Player( '{$html_id}', options);
	
	setTimeout(
		() => {
			player.getPaused().then((paused) => player.play());			
		},
		0
	);

}
SCRIPT;

			wp_enqueue_script( 'vimeo-player', 'https://player.vimeo.com/api/player.js', [], false, true );
			wp_add_inline_script( 'vimeo-player', $js );

			return '<div id="' . esc_attr( $html_id ) . '"></div>';
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
