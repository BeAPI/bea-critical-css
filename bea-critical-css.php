<?php
/*
 Plugin Name:  Critical CSS
 Description:  Load CriticalCSS file in theme
 Plugin URI:   http://www.beapi.fr
 Version:      1.0.0
 Author:       BE API Technical team
 Author URI:   http://www.beapi.fr

 ----

 Copyright 2016 BE API Technical team (human@beapi.fr)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

class BEA_Critical_CSS {

	/**
	 * Contain styles to move in footer
	 * @var array
	 */
	public $styles_to_move;

	public function __construct() {
		add_action( 'wp_head', [ $this, 'critical_css' ], 1 );
		add_action( 'wp_footer', [ $this, 'add_style_in_footer' ], 0 );
	}

	/**
	 * Load CriticalCSS file if exist
	 *
	 * @return void|bool
	 */
	public function critical_css() {

		$object = get_queried_object();
		$url = get_permalink( $object->ID );

		if ( empty( $url ) ) {
			return false;
		}

		$critical_css = self::critical_css_file( $url );

		if ( empty( $critical_css ) ) {
			return false;
		}

		add_action( 'wp_print_styles', [ $this, 'move_style_in_footer' ], 20 );

		echo '<!-- CriticalCSS -->
		<style>' . $critical_css . '</style>
		<!-- CriticalCSS -->';
	}

	/**
	 * Test if theme support CriticalCSS and move to Footer neccessary styles
	 *
	 * @link http://wordpress.stackexchange.com/a/162545
	 *
	 * @return void|bool
	 */
	public function move_style_in_footer() {
		if ( ! doing_action( 'wp_head' ) ) { // ensure we are on head
			return false;
		}

		// test if theme support CriticalCSS
		$theme_styles = get_theme_support( 'bea-critical-css' );
		if ( empty( $theme_styles ) ) {
			return false;
		}
		$theme_styles = reset( $theme_styles );

		// Get loaded styles
		global $wp_styles;

		$styles_to_move = [];
		foreach ( $theme_styles as $style ) {
			if ( ! in_array( $style, $wp_styles->queue ) ) {
				continue;
			}
			$styles_to_move[] = $style;
		}


		if ( empty( $styles_to_move ) ) {
			return false;
		}

		$this->styles_to_move = $styles_to_move;

		$styles_to_load = array_diff( $wp_styles->queue, $styles_to_move );
		$wp_styles->queue = $styles_to_load;
		$wp_styles->to_do = $styles_to_load;
	}

	/**
	 * Print styles in Footer
	 *
	 * @return  bool|void
	 */
	public function add_style_in_footer() {
		if ( empty( $this->styles_to_move ) ) {
			return false;
		}

		global $wp_styles;

		$styles = array_merge( $wp_styles->queue, $this->styles_to_move );

		$wp_styles->queue  = $styles;
		$wp_styles->to_do  = $styles;
	}

	/**
	 * Test if CurrentURL has CriticalCSS file
	 *
	 * @param  string $url
	 *
	 * @return bool|string
	 */
	public static function critical_css_file( $url ) {
		$config = locate_template( apply_filters( 'bea-critical-css/config_file', 'assets/css/critical/conf/bea-critical-conf.json' ) );

		if ( ! $config ) {
			return false;
		}

		$json = file_get_contents( $config );
		$json = json_decode( $json, true );

		if ( empty( $json ) ) {
			return false;
		}

		$url = self::parse_url( $url );

		foreach ( $json['pages'] as $page ) {
			if ( $url == $page['url'] ) {
				$name = $page['name'];
				break;
			}
		}

		if ( empty( $name ) ) {
			return false;
		}

		$viewport = ( wp_is_mobile() ) ? 'mobile' : 'desktop';
		$filename = $name . '-' . $viewport . '.css';

		$file = locate_template( apply_filters( 'bea-critical-css/folder', 'assets/css/critical/' ) . $filename );

		if ( empty( $file ) ) {
			return false;
		}

		return file_get_contents( $file );
	}

	/**
	 * Extract part URL
	 *
	 * @param  string $url
	 *
	 * @return string
	 */
	public static function parse_url( $url ) {
		return str_replace( home_url(), '', $url );
	}
}

new BEA_Critical_CSS();
