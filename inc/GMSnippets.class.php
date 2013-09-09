<?php
/**
 * GMSnippets class
 *
 * @package GMSnippets
 * @author Giuseppe Mazzapica
 *
 */
class GMSnippets {
	
	
	
	/**
	 * Inizialize the main plugin class. Run on after_setup_theme
	 *
	 * @access public
	 * @since 0.1.0
	 * @return null
	 *
	 */	
	static function init() {
		self::register();
		self::shortcode();
		self::widget();
	}
	
	
	
	/**
	 * Register the snippet post type
	 *
	 * @access public
	 * @since 0.1.0
	 * @return null
	 *
	 */		
	static function register() {
		do_action('gmsnippets_pre_register');
		$labels = array(
			'name'					=> _x('Snippets','Snippets post type label name','gmsnippets'),
        	'singular_name'			=> _x('Snippet','Snippets post type label singular_name','gmsnippets'),
        	'menu_name'				=> _x('Snippets','Snippets post type label menu_name','gmsnippets'),
        	'all_items'				=> _x('Snippets','Snippets post type label all_items','gmsnippets'),
        	'add_new'				=> _x('Add New','Snippets post type label add_new','gmsnippets'),
        	'add_new_item'			=> _x('Add New Snippet','Snippets post type label add_new_item','gmsnippets'),
        	'edit_item'				=> _x('Edit Snippet','Snippets post type label edit_item','gmsnippets'),
        	'new_item'				=> _x('New Snippet','Snippets post type label new_item','gmsnippets'),
        	'view_item'				=> _x('View Snippet','Snippets post type label view_item','gmsnippets'),
        	'search_items'			=> _x('Search Snippets','Snippets post type label search_items','gmsnippets'),
        	'not_found'				=> _x('Snippets not found','Snippets post type label not_found','gmsnippets'),
        	'not_found_in_trash'	=> _x('Snippets not found in trash','Snippets post type label not_found_in_trash','gmsnippets'),
        	'parent_item_colon'		=> _x('Parent Snippet','Snippets post type label parent_item_colon','gmsnippets')
		);
		$labels = apply_filters('gmsnippets_register_labels', $labels);
		$args = array(
			'label' => _x('Snippets','Snippets post type label','gmsnippets'),
			'labels' => $labels,
			'public' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'hierarchical' => false,
			'supports' => array('title', 'editor'),
			'has_archive' => false
		);
		$args = apply_filters('gmsnippets_register_args', $args);
		register_post_type( 'snippet', $args );
		do_action('gmsnippets_registered');
	}
	
	
	
	/**
	 * Register the hooks for the snippet shortcode
	 *
	 * @access public
	 * @since 0.1.0
	 * @return null
	 *
	 */		
	static function shortcode() {
		add_filter('gmsnippets_content', array(__CLASS__, 'content_filter'), 10, 2);
		add_shortcode('snippet', array(__CLASS__, 'snippet_shortcode') );
	}
	
	
	
	/**
	 * Handle the snippet shortcode
	 *
	 * @access public
	 * param array $atts	the shortcode atts
	 * param string $add_content	additional content to be appendend to snippet
	 * @since 0.1.0
	 * @return string	the snippet content
	 *
	 */		
	static function snippet_shortcode( $atts, $add_content = '' ) {
		extract( shortcode_atts( array(
			  'id' => 0,
			  'n' => '',
			  'load' => ''
		), $atts ) );
		if ( $load && ( file_exists($load) || file_exists(get_template_directory() . $load) ) ) {
			$path = file_exists($load) ? $load : get_template_directory() . $load;
			$add_content = wp_filter_post_kses( file_get_contents($path) );
			
		}
		return snippet($id, $n, $add_content);
	}
	
	
	
	/**
	 * Print a snippet
	 *
	 * @access public
	 * param int $id	the snippet id
	 * param string $n	the snippet slug or title
	 * param string $add_content	additional content to be appendend to snippet
	 * @since 0.1.0
	 * @return string	the snippet content
	 *
	 */	
	static function snippet( $id = 0, $n = '', $add_content = '' ) {
		if ( empty($id) && empty($n) && empty($add_content) ) return;
		$snippet = null;
		if ( ! empty($n) && is_string($n) ) {
			$field = sanitize_title($n) != $n ? 'post_title' : 'post_name';
			global $wpdb;
			$snippet = $wpdb->get_row( $wpdb->prepare(
			  "SELECT ID, post_title, post_name, post_content FROM $wpdb->posts WHERE $field = %s", $n
			) );
		} elseif ( ! empty($id) && intval($id) ) {
			global $wpdb;
			$snippet = $wpdb->get_row( $wpdb->prepare(
			  "SELECT ID, post_title, post_name, post_content FROM $wpdb->posts WHERE ID = %d", $id
			) );
		}
		
		if ( ! isset($snippet->post_content) && empty( $add_content) ) return;
		$content = ( isset($snippet->post_content) ) ? $snippet->post_content : '';
		if ( $add_content ) $content .= apply_filters('gmsnippets_add_content', $add_content, $snippet, $content);
		$content = apply_filters('gmsnippets_content', $content, $snippet);
		return $content;
	}
	
	
	
	/**
	 * Load a snippet from html documents
	 *
	 * @access public
	 * param string $file	the snippet path (absolute or relative to template directory)
	 * @since 0.1.0
	 * @return string	the snippet content
	 *
	 */	
	static function load_snippet( $file = '' ) {
		if ( $file && ( file_exists($file) || file_exists(get_template_directory() . $file) ) ) {
			$path = file_exists($file) ? $file : get_template_directory() . $file;
			$add_content = wp_filter_post_kses( file_get_contents($file) );
			return snippet( 0, '', $add_content );
		}
	}
	
	
	
	
	/**
	 * Handle the filter for the snippet content
	 *
	 * @access public
	 * param string $content	the snippet content
	 * param objecy $snippet	the snippet post row
	 * @since 0.1.0
	 * @return string	the snippet content
	 *
	 */	
	static function content_filter( $content, $snippet ) {
		return apply_filters('the_content', $content);
	}
	
	
	
	/**
	 * Prepare widget registration on init
	 *
	 * @access public
	 * @since 0.1.0
	 * @return null
	 *
	 */	
	static function widget() {
		add_action('widgets_init', array( __CLASS__, 'register_widget'));
	}
	
	
	
	/**
	 * Register the widget on widgets init
	 *
	 * @access public
	 * @since 0.1.0
	 * @return null
	 *
	 */
	static function register_widget() {
		require_once( GMSNIPPETSPATH . 'inc/GMSnippetsWidget.class.php');
		register_widget('GMSnippetsWidget');
	}
	
	
	
	/**
	 * Get all the snippets
	 *
	 * @access public
	 * @since 0.1.0
	 * @return array	array of the snippets post object
	 *
	 */
	static function snippets() {
		return get_posts( array('post_type' => 'snippet', 'posts_per_page' -1) );
	}
	
	
	
	/**
	 * Print a dropdown of snippets
	 *
	 * @access public
	 * @param array $args	options that modify the markup of the menu
	 * @since 0.1.0
	 * @return null
	 *
	 */
	static function dropdown( $args = array() ) {
		$defaults = array(
			'name' => 'snippet',
			'id' => 'snippet_select',
			'show_option_none' => __('Select a Snippet','gmsnippets'),
			'show_option_none_value' => '',
			'selected' => 0
		);
		$args = wp_parse_args($args, $defaults);
		$snippets = self::snippets();
		$out = '';
		if ( ! empty($snippets) ) {
			$out .= '<select name="'  . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '">';
			if ( $args['show_option_none'] ) $out .= '<option value="' .  esc_attr( $args['show_option_none_value'] ) . '">' . esc_html( $args['show_option_none'] ) . '</option>';
			foreach ( $snippets as $snippet ) {
				$out .= sprintf('<option value="%d"%s>%s</option>', $snippet->ID, selected($snippet->ID, $args['selected'], false), apply_filters('the_title', $snippet->post_title) );
			}
			$out .= '</select>';
			echo apply_filters('gmsnippets_dropdown', $out);
		} else {
			echo '<p>' . _x('Snippets not found','Snippets dropdown','gmsnippets') . '</p>';
		}
		unset($snippets);
	}
	
	
	
	
}