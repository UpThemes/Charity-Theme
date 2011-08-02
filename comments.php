<?php charity_abovecomments() ?>
			<div id="comments" class="section">
<?php
	$req = get_option('require_name_email'); // Checks if fields are required.
	if ( 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']) )
		die ( 'Please do not load this page directly. Thanks!' );
	if ( ! empty($post->post_password) ) :
		if ( $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password ) :
?>
				<div class="nopassword"><?php _e('This post is password protected. Enter the password to view any comments.', 'charity') ?></div>
			</div><!-- .comments -->
<?php
		return;
	endif;
endif;
?>

<?php if ( have_comments() ) : ?>

<?php /* numbers of pings and comments */
$ping_count = $comment_count = 0;
foreach ( $comments as $comment )
	get_comment_type() == "comment" ? ++$comment_count : ++$ping_count;
?>

<?php if ( ! empty($comments_by_type['comment']) ) : ?>

<?php charity_abovecommentslist() ?>

					<h3><?php printf($comment_count > 1 ? __(charity_multiplecomments_text(), 'charity') : __(charity_singlecomment_text(), 'charity'), $comment_count) ?></h3>
				
					<ol class="section commentlist clearfix">
					<?php wp_list_comments(list_comments_arg()); ?>
					</ol>
                    
                    <div class="clear"></div>

        			<div id="comments-nav-below" class="comment-navigation">
        			     <div class="paginated-comments-links"><?php paginate_comments_links(); ?></div>
                    </div>

<?php charity_belowcommentslist() ?>			

<?php endif; /* if ( $comment_count ) */ ?>

<?php if ( ! empty($comments_by_type['pings']) ) : ?>

<?php charity_abovetrackbackslist() ?>

				<div id="trackbacks-list" class="section">
                
					<h3><?php printf($ping_count > 1 ? __('<span>%d</span> Trackbacks', 'charity') : __('<span>One</span> Trackback', 'charity'), $ping_count) ?></h3>
					
					<ol class="section commentlist clearfix">
					<?php wp_list_comments('type=pings&callback=charity_pings'); ?>
					</ol>
                    
                    <div class="clear"></div>
					
				</div><!-- #trackbacks-list .comments -->			

<?php charity_belowtrackbackslist() ?>				

<?php endif /* if ( $ping_count ) */ ?>
<?php endif /* if ( $comments ) */ ?>

<?php
	if ( 'open' == $post->comment_status ) : 
		if (CHARITY_COMPATIBLE_COMMENT_FORM) {?>
				<div id="respond" class="section">
    				<h3><?php comment_form_title( __('Leave A Comment', 'charity'), __(charity_postreply_text(), 'charity') ); ?></h3>
    				
    				<div id="cancel-comment-reply"><?php cancel_comment_reply_link() ?></div>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
					<p id="login-req"><?php printf(__('You must be <a href="%s" title="Log in">logged in</a> to post a comment.', 'charity'),
					site_url('/wp-login.php?redirect_to=' . get_permalink() ) )  ?></p>

<?php else : ?>
					<div class="formcontainer">	
					
<?php charity_abovecommentsform() ?>					

						<form id="commentform" action="<?php echo site_url('/wp-comments-post.php') ?>" method="post">

<?php if ( $user_ID ) : ?>
							<p id="login"><?php printf(__('<span class="loggedin">Logged in as <a href="%1$s" title="Logged in as %2$s">%2$s</a>.</span> <span class="logout"><a href="%3$s" title="Log out of this account">Log out?</a></span>', 'charity'),
								site_url('/wp-admin/profile.php'),
								esc_html($user_identity),
								wp_logout_url(get_permalink()) ) ?></p>

<?php else : ?>

							<?php  /* <p id="comment-notes"><?php _e('Your email is <em>never</em> published nor shared.', 'charity') ?> <?php if ($req) _e('Required fields are marked <span class="required">*</span>', 'charity') ?></p> */?>

                            <div>
                            	<label for="author"><?php _e('Name', 'charity') ?> <?php //if ($req) _e('<span class="required">*</span>', 'charity') ?></label>
                                <input id="author" name="author" type="text" value="<?php echo $comment_author ?>" size="30" maxlength="20" tabindex="3" />
                            </div>

                            <div>
                            	<label for="email"><?php _e('Email', 'charity') ?> <?php //if ($req) _e('<span class="required">*</span>', 'charity') ?></label>
                                <input id="email" name="email" type="text" value="<?php echo $comment_author_email ?>" size="30" maxlength="50" tabindex="4" />
                            </div>

                            <div>
                            	<label for="url"><?php _e('Website', 'charity') ?></label>
                                <input id="url" name="url" type="text" value="<?php echo $comment_author_url ?>" size="30" maxlength="50" tabindex="5" />
                            </div>

<?php endif /* if ( $user_ID ) */ ?>

                            <div>
                            	<label for="comment"><?php _e('Message', 'charity') ?></label>
                                <textarea id="comment" name="comment" cols="45" rows="8" tabindex="6"></textarea>
                            </div>
                            
                            <?php  /* <div id="form-allowed-tags" class="form-section">
                                <p><span><?php _e('You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:', 'charity') ?></span> <code><?php echo allowed_tags(); ?></code></p>
                            </div>
                            */?>
							
                  <?php do_action('comment_form', $post->ID); ?>
                  
							<div class="form-submit"><input id="submit" name="submit" type="submit" value="<?php _e(charity_commentbutton_text(), 'charity') ?>" tabindex="7" /><input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" /></div>

                            <?php comment_id_fields(); ?>    

						</form><!-- #commentform -->
						
<?php charity_belowcommentsform() ?>
						
					</div><!-- .formcontainer -->
<?php endif /* if ( get_option('comment_registration') && !$user_ID ) */ ?>

				</div><!-- #respond -->
<?php
		} else { ?>

						<form id="commentform" action="<?php echo site_url('/wp-comments-post.php') ?>" method="post">

<?php if ( $user_ID ) : ?>
							<p id="login"><?php printf(__('<span class="loggedin">Logged in as <a href="%1$s" title="Logged in as %2$s">%2$s</a>.</span> <span class="logout"><a href="%3$s" title="Log out of this account">Log out?</a></span>', 'charity'),
								site_url('/wp-admin/profile.php'),
								esc_html($user_identity),
								wp_logout_url(get_permalink()) ) ?></p>

<?php else : ?>

							<p id="comment-notes"><?php _e('Your email is <em>never</em> published nor shared.', 'charity') ?> <?php if ($req) _e('Required fields are marked <span class="required">*</span>', 'charity') ?></p>

                            <div>
                            	<label for="author"><?php _e('Name', 'charity') ?> <?php if ($req) _e('<span class="required">*</span>', 'charity') ?></label>
                                <input id="author" name="author" type="text" value="<?php echo $comment_author ?>" size="30" maxlength="20" tabindex="3" />
                            </div>

                            <div>
                            	<label for="email"><?php _e('Email', 'charity') ?> <?php if ($req) _e('<span class="required">*</span>', 'charity') ?></label>
                                <input id="email" name="email" type="text" value="<?php echo $comment_author_email ?>" size="30" maxlength="50" tabindex="4" />
                            </div>

                            <div>
                            	<label for="url"><?php _e('Website', 'charity') ?></label>
                                <input id="url" name="url" type="text" value="<?php echo $comment_author_url ?>" size="30" maxlength="50" tabindex="5" />
                            </div>

<?php endif /* if ( $user_ID ) */ ?>

                            <div>
                            	<label for="comment"><?php _e(charity_commentbox_text(), 'charity') ?></label>
                                <textarea id="comment" name="comment" cols="45" rows="8" tabindex="6"></textarea>
                            </div>
                            
                            <div id="form-allowed-tags" class="form-section">
                                <p><span><?php _e('You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:', 'charity') ?></span> <code><?php echo allowed_tags(); ?></code></p>
                            </div>
							
                  <?php do_action('comment_form', $post->ID); ?>
                  
							<div class="form-submit"><input id="submit" name="submit" type="submit" value="<?php _e(charity_commentbutton_text(), 'charity') ?>" tabindex="7" /><input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" /></div>

                            <?php comment_id_fields(); ?>    

						</form><!-- #commentform -->            
<?php
		}
	endif /* if ( 'open' == $post->comment_status ) */ ?>

			</div><!-- #comments -->
<?php charity_belowcomments() ?>