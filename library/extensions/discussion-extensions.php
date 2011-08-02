<?php

// Located in discussion.php
// after comment-author
if (function_exists('childtheme_override_commentmeta'))  {
	function charity_commentmeta() {
		childtheme_override_commentmeta();
	}
} else {
	// Creates comment meta
	function charity_commentmeta($print = TRUE) {
		$content = '<div class="comment-meta">' . 
					sprintf( __('<a class="comment-permalink" href="%3$s" title="Permalink to this comment">%1$s at %2$s</a>', 'charity' ),
					get_comment_date(),
					get_comment_time(),
					'#comment-' . get_comment_ID() );
		
		$content .= '</div>' . "\n";
			
		return $print ? print(apply_filters('charity_commentmeta', $content)) : apply_filters('charity_commentmeta', $content);

	} // end charity_commentmeta
}


// Located in discussion.php
// At the beginning of li#comment-[id]. Note that this is *per comment*.
function charity_abovecomment() {
	do_action('charity_abovecomment');
}


// Located in discussion.php
// Just after the comment reply link. Note that this is *per comment*.
function charity_belowcomment() {
	do_action('charity_belowcomment');
}

?>