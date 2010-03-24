<?php
/**
 * Plugin Name: Recent Posts With Authors Widget
 * Plugin URI: http://wordpress.org/extend/plugins/recent-posts-with-authors/
 * Description: Shows a list of recent posts with the author of each post - for multi-author blogs.
 * Version: 1.0
 * Author: Yonat Sharon
 * Author URI: http://ootips.org/yonat
 * Kudos: Aaron Campbell for http://xavisys.com/wordpress-widget/ on which this is based
 */

class Recent_With_Authors_Widget extends WP_Widget {

	function Recent_With_Authors_Widget() {
		$widget_ops = array('classname' => 'widget_recent_with_authors', 'description' => __( "The most recent posts on your blog") . ' + ' . __("Author") . ' ' . __("Name") );
		$this->WP_Widget('recent-with-authors', __('Recent Posts') . ' + ' . __('Author'), $widget_ops);

		add_action( 'publish_post', array(&$this, 'flush_widget_cache') );
		add_action( 'private_to_publish', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_recent_with_authors', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) )
			return $cache[$args['widget_id']];

		ob_start();
		extract($args);

		$title = empty($instance['title']) ? __('Recent Posts') : apply_filters('widget_title', $instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		$queryArgs = array(
			'showposts'			=> $number,
			'what_to_show'		=> 'posts',
			'nopaging'			=> 0,
			'post_status'		=> 'publish',
			'caller_get_posts'	=> 1,
		);

		$r = new WP_Query($queryArgs);
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>
		<ul>
		<?php while ($r->have_posts()) : $r->the_post(); ?>
		<li>
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			<?php _e('by') ?> <b><?php the_author(); ?></b>
		</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
			wp_reset_query();  // Restore global post data stomped by the_post().
		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_add('widget_recent_with_authors', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_with_authors']) )
			delete_option('widget_recent_with_authors');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_with_authors', 'widget');
	}

	function form( $instance ) {
		$title = attribute_escape($instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">
		<?php _e('Title:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>">
		<?php _e('Number of posts to show:'); ?>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></label>
		<br /><small><?php _e('(at most 15)'); ?></small></p>
<?php
	}
}
function registerRecentWithAuthorsWidget() {
	register_widget('Recent_With_Authors_Widget');
}
add_action('widgets_init', 'registerRecentWithAuthorsWidget');
