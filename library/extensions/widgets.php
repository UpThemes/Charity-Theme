<?php


// CSS markup before the widget
function charity_before_widget() {
	$content = '<li id="%1$s" class="widgetcontainer %2$s">';
	return apply_filters('charity_before_widget', $content);
}

// CSS markup after the widget
function charity_after_widget() {
	$content = '</li>';
	return apply_filters('charity_after_widget', $content);
}

// CSS markup before the widget title
function charity_before_title() {
	$content = "<h3 class=\"widgettitle\">";
	return apply_filters('charity_before_title', $content);
}

// CSS markup after the widget title
function charity_after_title() {
	$content = "</h3>\n";
	return apply_filters('charity_after_title', $content);
}

/**
 * Search widget class
 *
 * @since 0.9.6.3
 */
class THM_Widget_Search extends WP_Widget {

	function THM_Widget_Search() {
		$widget_ops = array('classname' => 'widget_search', 'description' => __( "A search form for your blog") );
		$this->WP_Widget('search', __('Search', 'charity'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Search', 'charity') : $instance['title']);

		echo $before_widget;
		if ( $title )
			echo $before_title ?><label for="s"><?php echo $title ?></label><?php echo $after_title;

		// Use current theme search form if it exists
		get_search_form();

		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = $instance['title'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

}

/**
 * Meta widget class
 *
 * Displays log in/out
 *
 * @since 0.9.6.3
 */
class THM_Widget_Meta extends WP_Widget {

	function THM_Widget_Meta() {
		$widget_ops = array('classname' => 'widget_meta', 'description' => __( "Log in/out and admin", 'charity') );
		$this->WP_Widget('twitter', __('Meta', 'charity'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Meta', 'charity') : $instance['title']);

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
?>
			<ul>
			<?php wp_register(); ?>
			<li><?php wp_loginout(); ?></li>
			<?php wp_meta(); ?>
			</ul>
<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<?php
	}
}
    
/**
 * RSS links widget class
 *
 * @since 0.9.6.3
 */
class THM_Widget_RSSlinks extends WP_Widget {

	function THM_Widget_RSSlinks() {
		$widget_ops = array( 'description' => __('Links to your posts and comments feed', 'charity') );
		$this->WP_Widget( 'rss-links', __('RSS Links', 'charity'), $widget_ops);
	}

	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? __('RSS Links', 'charity') : $instance['title']);
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
?>
			<ul>
				<li><a href="<?php bloginfo('rss2_url') ?>" title="<?php echo esc_html(get_bloginfo('name')) ?> <?php _e('Posts RSS feed', 'charity'); ?>" rel="alternate nofollow" type="application/rss+xml"><?php _e('All posts', 'charity') ?></a></li>
				<li><a href="<?php bloginfo('comments_rss2_url') ?>" title="<?php echo esc_html(get_bloginfo('name')) ?> <?php _e('Comments RSS feed', 'charity'); ?>" rel="alternate nofollow" type="application/rss+xml"><?php _e('All comments', 'charity') ?></a></li>
			</ul>
<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<?php
	}
}

/**
 * Charity Post Preview
 */
class CharityPostPreview extends WP_Widget {

    function CharityPostPreview() {
        $widget_ops = array( 'classname' => 'widget_charity_post', 'description' => __( 'Showcase an excerpt of a post or page with a read more button', 'charity' ) );
        $control_ops = array( 'id_base' => 'widget_charity_post' );
        parent::WP_Widget('widget_charity_post', __('Charity Post Preview', 'charity'), $widget_ops, $control_ops);	
    }

    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $post_id = $instance['post_id'];
        $post = get_post($post_id);
        $post_thumbnail = get_the_post_thumbnail($post_id, array(60, 60));
        echo $before_widget;
        if($title) echo $before_title . $title . $after_title;
        echo '<h1 class="post-preview-title">'.$post->post_title.'</h1>';
        if($post_thumbnail) echo '<span class="alignleft">'.$post_thumbnail.'</span>';
        echo '<p class="message">'.strip_tags(substr($post->post_content, 0, 175)).'...</p>';
        echo '<a class="more" href="'.get_permalink($post_id).'">'.__('Read More', 'charity').'</a>';
        echo '<div class="clear"></div>';
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
        $instance['post_id'] = strip_tags($new_instance['post_id']);
        return $instance;
    }

    function form($instance) {				
        $title = esc_attr($instance['title']);
        $post_id = esc_attr($instance['post_id']);
        global $wpdb;
        $posts = $wpdb->get_results("SELECT ID, post_title from $wpdb->posts WHERE post_status = 'publish' AND post_type = 'page' OR post_type = 'post'"); ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>">
                    <?php _e('Title:', 'charity'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('post_id'); ?>">
                    <?php _e('Post/Page:', 'charity'); ?>
                    <select class="widefat" id="<?php echo $this->get_field_id('post_id'); ?>" name="<?php echo $this->get_field_name('post_id'); ?>">
                        <?php foreach($posts as $post): ?>
                            <option value="<?php echo $post->ID ?>" <?php if($post->ID == $post_id) echo 'selected="selected"'?>><?php echo $post->post_title; ?></option>
                        <?php endforeach;?>
                    </select>
                </label>
            </p>
    <?php }

}
add_action('widgets_init', create_function('', 'return register_widget("CharityPostPreview");'));


/* Charity Text Widget
 */
class CharityText extends WP_Widget {

    function CharityText() {
        $widget_ops = array( 'classname' => 'widget_charity_text', 'description' => __( 'Post a message with a read more link.', 'charity' ) );
        $control_ops = array( 'id_base' => 'widget_charity_text' );
        parent::WP_Widget('widget_charity_text', __('Charity Text Widget', 'charity'), $widget_ops, $control_ops);	
    }

    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $message = $instance['message'];
        $link = $instance['link'];
        $link_text = $instance['link_text'];
        if(!$link_text)$link_text = __('Read More', 'charity');
        echo $before_widget;
        if($title) echo $before_title . $title . $after_title;
        echo '<p class="message">'.$message.'</p>';
        echo '<a class="more" href="'.$link.'">'.$link_text.'</a>';
        echo '<div class="clear"></div>';
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
        $instance['message'] = strip_tags($new_instance['message']);
        $instance['link'] = strip_tags($new_instance['link']);
        $instance['link_text'] = strip_tags($new_instance['link_text']);
        return $instance;
    }

    function form($instance) {				
        $title = esc_attr($instance['title']);
        $message = esc_attr($instance['message']);
        $link = esc_attr($instance['link']);
        $link_text = esc_attr($instance['link_text']);
        global $wpdb;
        $posts = $wpdb->get_results("SELECT ID, post_title from $wpdb->posts WHERE post_status = 'publish' AND post_type = 'page' OR post_type = 'post'"); ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>">
                    <?php _e('Title:', 'charity'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
                </label>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('message'); ?>">
                    <?php _e('Message:', 'charity'); ?>
                    <textarea rows="10" class="widefat" id="<?php echo $this->get_field_id('message'); ?>" name="<?php echo $this->get_field_name('message'); ?>"><?php echo $message; ?></textarea>
                </label>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('link'); ?>">
                    <?php _e('Link URL:', 'charity'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo $link; ?>" />
                </label>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('link_text'); ?>">
                    <?php _e('Link Text:', 'charity'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('link_text'); ?>" name="<?php echo $this->get_field_name('link_text'); ?>" type="text" value="<?php echo $link_text; ?>" />
                </label>
            </p>
            
    <?php }

}
add_action('widgets_init', create_function('', 'return register_widget("CharityText");'));


if(function_exists('sp_get_events')):

/* Charity Events List
 */
class CharityEvents extends WP_Widget {

    function CharityEvents() {
        $widget_ops = array( 'classname' => 'widget_charity_events', 'description' => __( 'Post a message with a read more link.', 'charity' ) );
        $control_ops = array( 'id_base' => 'widget_charity_events' );
        parent::WP_Widget('widget_charity_events', __('Charity Events Widget', 'charity'), $widget_ops, $control_ops);	
    }

    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        
        $meta_value = $instance['meta_value'];
        $meta_key = $instance['meta_key'];
        $cat = $instance['category'];
        $limit = $instance['limit'];
        $events = sp_get_events(array('numResults' => $limit, 'eventCat' => $cat, 'metaKey' => $meta_key, 'metaValue' => $meta_value));
        if(is_array($events)):
            echo $before_widget;
            if($title) echo $before_title . $title . $after_title;
            echo '<div id="events">';
            echo '<ul>';
            foreach($events as $event):
                $name = $event->post_title;
                $date = sp_get_start_date($event->ID, '', 'Y-m-d');
                $month = sp_get_start_date($event->ID, '', 'M');
                $day = sp_get_start_date($event->ID, '', 'j');
                if($cat):
                    $category = get_term_by('slug', $cat, 'sp_events_cat');
                    $category = $category->name;
                else:
                    $categories = get_the_terms($event->ID, 'sp_events_cat');
                    $category = $categories[0]->name;
                endif;
                echo '<li>
                        <a title="'.$name.'" href="'.get_permalink($event->ID).'">
                            <div class="time">'.$month.' <span>'.$day.'</span></div>
                            <h2>'.$category.'</h2>
                            <br />'.$name.'
                        </a>
                    </li>';
            endforeach;
            echo '</ul></div>';
            echo '<div class="clear"></div>';
            echo $after_widget;
        endif;
    }

    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
        $instance['limit'] = strip_tags($new_instance['limit']);
        $instance['category'] = strip_tags($new_instance['category']);
        $instance['meta_value'] = strip_tags($new_instance['meta_value']);
        $instance['meta_key'] = strip_tags($new_instance['meta_key']);
        return $instance;
    }

    function form($instance) {				
        $title = esc_attr($instance['title']);
        $limit = esc_attr($instance['limit']);
        $cat = esc_attr($instance['category']);
        $meta_key = esc_attr($instance['meta_key']);
        $meta_value = esc_attr($instance['meta_value']);
        $categories = get_categories(array('taxonomy' => 'sp_events_cat'));
        global $wpdb;
        $posts = $wpdb->get_results("SELECT ID, post_title from $wpdb->posts WHERE post_status = 'publish' AND post_type = 'page' OR post_type = 'post'"); ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>">
                    <?php _e('Title:', 'charity'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
                </label>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('limit'); ?>">
                    <?php _e('Number of Events:', 'charity'); ?>
                    <select class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>">
                            <option value="1" <?php if($limit == 1) echo 'selected="selected"'?>>1</option>
                            <option value="2" <?php if($limit == 2) echo 'selected="selected"'?>>2</option>
                            <option value="3" <?php if($limit == 3) echo 'selected="selected"'?>>3</option>
                            <option value="4" <?php if($limit == 4) echo 'selected="selected"'?>>4</option>
                            <option value="5" <?php if($limit == 5) echo 'selected="selected"'?>>5</option>
                            <option value="6" <?php if($limit == 6) echo 'selected="selected"'?>>6</option>
                            <option value="7" <?php if($limit == 7) echo 'selected="selected"'?>>7</option>
                            <option value="8" <?php if($limit == 8) echo 'selected="selected"'?>>8</option>
                            <option value="9" <?php if($limit == 9) echo 'selected="selected"'?>>9</option>
                            <option value="10" <?php if($limit == 10) echo 'selected="selected"'?>>10</option>
                    </select>
                </label>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id('category'); ?>">
                    <?php _e('Category:', 'charity'); ?>
                    <select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
                            <option value="" <?php if(!$cat) echo 'selected="selected"'?>><?php _e('None', 'charity')?></option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category->slug ?>" <?php if($cat == $category->slug) echo 'selected="selected"'?>><?php echo $category->name ?></option>
                            <?php endforeach;?>
                    </select>
                    
                </label>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('meta_key'); ?>">
                    <?php _e('Meta Key Search:', 'charity'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('meta_key'); ?>" name="<?php echo $this->get_field_name('meta_key'); ?>" type="text" value="<?php echo $meta_key; ?>" />
                </label>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('meta_value'); ?>">
                    <?php _e('Meta Value Search:', 'charity'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('meta_value'); ?>" name="<?php echo $this->get_field_name('meta_value'); ?>" type="text" value="<?php echo $meta_value; ?>" />
                </label>
            </p>
            
    <?php }

}
add_action('widgets_init', create_function('', 'return register_widget("CharityEvents");'));

endif;