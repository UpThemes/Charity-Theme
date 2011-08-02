<?php


// Located in comments.php
// Just before #comments
function charity_abovecomments() {
    do_action('charity_abovecomments');
}


// Located in comments.php
// Just before #comments-list
function charity_abovecommentslist() {
    do_action('charity_abovecommentslist');
}


// Located in comments.php
// Just after #comments-list
function charity_belowcommentslist() {
    do_action('charity_belowcommentslist');
}


// Located in comments.php
// Just before #trackbacks-list
function charity_abovetrackbackslist() {
    do_action('charity_abovetrackbackslist');
}


// Located in comments.php
// Just after #trackbacks-list
function charity_belowtrackbackslist() {
    do_action('charity_belowtrackbackslist');
}


// Located in comments.php
// Just before the comments form
function charity_abovecommentsform() {
    do_action('charity_abovecommentsform');
}


// Adds the Subscribe to comments button
function charity_show_subscription_checkbox() {
    if(function_exists('show_subscription_checkbox')) { show_subscription_checkbox(); }
}
add_action('comment_form', 'charity_show_subscription_checkbox', 98);


// Located in comments.php
// Just after the comments form
function charity_belowcommentsform() {
    do_action('charity_belowcommentsform');
}


// Adds the Subscribe without commenting button
function charity_show_manual_subscription_form() {
    if(function_exists('show_manual_subscription_form')) { show_manual_subscription_form(); }
}
add_action('charity_belowcommentsform', 'charity_show_manual_subscription_form', 5);


// Located in comments.php
// Just after #comments
function charity_belowcomments() {
    do_action('charity_belowcomments');
}

// Located in comments.php
// Creates the standard text for one comment
function charity_singlecomment_text() {
    $content = __('<span>One</span> Comment', 'charity');
    return apply_filters( 'charity_singlecomment_text', $content );
}

// Located in comments.php
// Creates the standard text for more than one comment
function charity_multiplecomments_text() {
    $content = __('<span>%d</span> Comments', 'charity');
    return apply_filters( 'charity_multiplecomments_text', $content );
}

// creates the list comments arguments
function list_comments_arg() {
	$content = 'type=comment&callback=charity_comments';
	return apply_filters('list_comments_arg', $content);
}

// Located in comments.php
// Creates the standard text 'Post a Comment'
function charity_postcomment_text() {
    $content = __('Post a Comment', 'charity');
    return apply_filters( 'charity_postcomment_text', $content );
}

// Located in comments.php
// Creates the standard text 'Post a Reply to %s'
function charity_postreply_text() {
    $content = __('Post a Reply to %s', 'charity');
    return apply_filters( 'charity_postreply_text', $content );
}

// Located in comments.php
// Creates the standard text 'Comment' for the text box
function charity_commentbox_text() {
    $content = __('Comment', 'charity');
    return apply_filters( 'charity_commentbox_text', $content );
}


// Located in comments-extensions.php
// Creates the standard text 'Cancel reply'
function charity_cancelreply_text() {
    $content = __('Cancel reply', 'charity');
    return apply_filters( 'charity_cancelreply_text', $content );
}

// Located in comments.php
// Creates the standard text 'Post Comment' for the send button
function charity_commentbutton_text() {
    $content = __('Post Comment', 'charity');
    return apply_filters( 'charity_commentbutton_text', $content );
}

// Located in comments.php
// Creates the standard arguments for comment_form()
function charity_comment_form_args( $post_id = null ) {
	global $user_identity, $id;
	
	if ( null === $post_id )
          $post_id = $id;
      else
          $id = $post_id;
	
	$req = get_option( 'require_name_email' );
	
	$commenter = wp_get_current_commenter();
	
	$aria_req = ( $req ? " aria-required='true'" : '' );
	
	$fields =  array(
		'author' => '<div id="form-section-author" class="form-section"><div class="form-label">' . '<label for="author">' . __( 'Name', 'charity' ) . '</label> ' . ( $req ? __('<span class="required">*</span>', 'charity') : '' ) . '</div>' . '<div class="form-input">' . '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' .  ' maxlength="20" tabindex="3"' . $aria_req . ' /></div></div><!-- #form-section-author .form-section -->',
		'email'  => '<div id="form-section-email" class="form-section"><div class="form-label"><label for="email">' . __( 'Email', 'charity' ) . '</label> ' . ( $req ? __('<span class="required">*</span>', 'charity') : '' ) . '</div><div class="form-input">' . '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" maxlength="50" tabindex="4"' . $aria_req . ' /></div></div><!-- #form-section-email .form-section -->',
		'url'    => '<div id="form-section-url" class="form-section"><div class="form-label"><label for="url">' . __( 'Website', 'charity' ) . '</label></div>' . '<div class="form-input"><input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" maxlength="50" tabindex="5" /></div></div><!-- #form-section-url .form-section -->',
	);

	
	$args = array(
		'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
		'comment_field'        => '<div id="form-section-comment" class="form-section"><div class="form-label"><label for="comment">' . __(charity_commentbox_text(), 'charity') . '</label></div><div class="form-textarea"><textarea id="comment" name="comment" cols="45" rows="8" tabindex="6" aria-required="true"></textarea></div></div><!-- #form-section-comment .form-section -->',
		'comment_notes_before' => '<p class="comment-notes">' . __( 'Your email is <em>never</em> published nor shared.', 'charity' ) . ( $req ? ' ' . __('Required fields are marked <span class="required">*</span>', 'charity') : '' ) . '</p>',
		'must_log_in'          => '<p id="login-req">' .  sprintf( __('You must be <a href="%s" title="Log in">logged in</a> to post a comment.', 'charity' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'logged_in_as'         => '<p id="login">' . sprintf( __('<span class="loggedin">Logged in as <a href="%1$s" title="Logged in as %2$s">%2$s</a>.</span> <span class="logout"><a href="%3$s" title="Log out of this account">Log out?</a></span>', 'charity'),  admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'comment_notes_after'  => '<div id="form-allowed-tags" class="form-section"><p><span>' . __('You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:', 'charity') . '</span> <code>' . allowed_tags() . '</code></p></div>',


		'id_form'              => 'commentform',
		'id_submit'            => 'submit',
		'title_reply'          => charity_postcomment_text(),
		'title_reply_to'       => charity_postreply_text(),
		'cancel_reply_link'    => charity_cancelreply_text(),
		'label_submit'         => charity_commentbutton_text(),

	);
	return apply_filters( 'charity_comment_form_args', $args );	
}

// Produces an avatar image with the hCard-compliant photo class
function charity_commenter_link() {
	$commenter = get_comment_author_link();
	if ( ereg( '<a[^>]* class=[^>]+>', $commenter ) ) {
		$commenter = ereg_replace( '(<a[^>]* class=[\'"]?)', '\\1url ' , $commenter );
	} else {
		$commenter = ereg_replace( '(<a )/', '\\1class="url "' , $commenter );
	}
	echo $commenter;
} // end charity_commenter_link


// A hook for the standard comments template
function charity_comments_template() {
	do_action('charity_comments_template');
} // end charity_comments


	// The standard comments template is injected into charity_comments_template() by default
	function charity_include_comments() {
		comments_template('', true);
	} // end charity_include_comments
	
	add_action('charity_comments_template','charity_include_comments',5);
	
	