<?php
/**
 * GMSnippetsWidget class
 *
 * @package GMSnippets
 * @author Giuseppe Mazzapica
 *
 */
class GMSnippetsWidget extends WP_Widget {


	/**
	 * Construct the widget
	 *
	 * @access public
	 * @since 0.1.0
	 * @return null
	 *
	 */
	function __construct() {
		add_filter('gmsnippets_widget_title', array($this, 'widget_title_filter'), 10, 2);
		$widget_ops = array( 'description' => __('Show a snippet in a widget', 'gmsnippets') );
		parent::WP_Widget( false, $name = 'GM Snippets Widget', $widget_ops );
	}


	/**
	 * Print the widget on frontend
	 *
	 * @access public
	 * @since 0.1.0
	 * @return null
	 *
	 */
	function widget( $args, $instance = array() ) {
		extract($args);
		$snippet = intval($instance['snippet']);
		$title = apply_filters( 'gmsnippets_widget_title', empty( $instance['title'] ) ? '' : esc_html( $instance['title'] ), $snippet );
		$add_content = empty( $instance['add_content'] ) ? '' : apply_filters('the_content', $instance['add_content']);
		$before = empty( $instance['before'] ) ? '' : $instance['before'];
		$after = empty( $instance['after'] ) ? '' : $instance['after'];
		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;
		if ( $before ) echo $before;
		echo GMSnippets::snippet( $snippet, '', $add_content);
		if ( $after ) echo $after;
		echo $after_widget;
		
	}


	/**
	 * Print the widget form on backend
	 *
	 * @access public
	 * @since 0.1.0
	 * @return null
	 *
	 */
 	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title'=>'', 'snippet'=>'', 'before'=>'', 'after'=>'', 'add_content'=>'' ) );

		$title = esc_attr( $instance['title'] );
		$snippet = (string)intval($instance['snippet']);
		$before = $instance['before'];
		$after = $instance['after'];
		$add_content = $instance['add_content'];
		$fields = array(
			'title'			=> _x('Widget Title', 'Widget form',  'gmsnippets'),
			'snippet'		=> _x('Snippet', 'Widget form', 'gmsnippets'),
			'before'		=> _x('Before Content', 'Widget form',  'gmsnippets'),
			'after'			=> _x('After Content', 'Widget form',  'gmsnippets'),
			'add_content'	=> _x('Additional Content', 'Widget form',  'gmsnippets'),
		);
		foreach ( $fields as $field => $label) {
			$fid = $this->get_field_id($field);
			$fname = $this->get_field_name($field);
			$value = $$field;
			echo '<p><label for="' . $fid . '">' . $label . ':</label>';
			if ( $field != 'snippet' && $field != 'add_content' ) {
				echo '<input class="widefat" id="' . $fid . '" name="' . $fname . '" type="text" value="' . $value . '" />';
			} elseif ( $field == 'snippet' ) {
				GMSnippets::dropdown( array('name' => $fname, 'id' => $fid, 'selected' => $value ) );
			} elseif ( $field == 'add_content' ) {
				echo '<textarea class="widefat" id="' . $fid . '" name="' . $fname . '">' . esc_textarea( $value ) . '</textarea>';
			}
			echo '</p>';
		}
	}
	

	/**
	 * Update widget option on saving
	 *
	 * @access public
	 * param  array $new_instance
	 * param  array $old_instance
	 * @since 0.1.0
	 * @return array
	 *
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['snippet'] = intval( $new_instance['snippet'] );
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['before'] = $new_instance['before'];
		$instance['after'] = $new_instance['after'];
		$instance['add_content'] = $new_instance['add_content'];
		if ( ! current_user_can('unfiltered_html') ) {
			$instance['before'] = $instance['after'] = '';
			$instance['add_content'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['add_content']) ) );
		}
		return $instance;
	}
	
	
	/**
	 * Handle the widget title filter
	 *
	 * @access public
	 * param  string $title
	 * param  int $snippet
	 * @since 0.1.0
	 * @return string
	 *
	 */	
	function widget_title_filter( $title = '', $snippet = 0 ) {
		return apply_filters( 'widget_title', $title );
	}
}