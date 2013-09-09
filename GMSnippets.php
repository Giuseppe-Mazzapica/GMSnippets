<?php
/**
 * Plugin Name: GM Snippets
 *
 * Description: Use a CPT to handle reusable content trought a site. Reusable content can be used via template tag, shortcode or widget.
 * Version: 0.1.0
 * Author: Giuseppe Mazzapica
 * Requires at least: 3.5
 * Tested up to: 3.6
 *
 * Text Domain: gmsnippets
 * Domain Path: /lang/
 *
 * @package GMSnippets
 * @author Giuseppe Mazzapica
 *
 */
 
add_action('after_setup_theme', 'init_GMSnippets');
add_action('wp_loaded', 'GMSnippets_template_tags');

/**
 * Inizialize plugin
 *
 * @since 0.1.0
 * @return null
 *
 */
function init_GMSnippets() {
	if ( ! defined('ABSPATH') ) die();
	define('GMSNIPPETSPATH', plugin_dir_path( __FILE__ ) );
	require_once( GMSNIPPETSPATH . 'inc/GMSnippets.class.php');
	// allow disabling plugin from another plugin or theme via filter
	if ( apply_filters('gm_snippets_enable', true) ) {
		load_plugin_textdomain('gmsnippets', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		GMSnippets::init();
	}
}

/**
 * Register the template tags
 *
 * @since 0.1.0
 * @return null
 *
 */
function GMSnippets_template_tags() {	
	if ( ! function_exists('snippet') ) { function snippet( $id = 0, $n = '', $add_content = '' ) { echo GMSnippets::snippet($id, $n, $add_content);  } }
	if ( ! function_exists('the_snippet') ) { function the_snippet( $id = 0, $n = '', $add_content = '' ) { echo GMSnippets::snippet($id, $n, $add_content); } }
	if ( ! function_exists('load_snippet') ) { function load_snippet( $file = '' ) { echo GMSnippets::load_snippet($file); } }
	if ( ! function_exists('get_snippets') ) { function get_snippets() { return GMSnippets::snippets();  } }
}