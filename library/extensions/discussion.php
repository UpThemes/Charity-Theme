<?php

// Custom callback to list comments in the Charity style
function charity_comments($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
	$GLOBALS['comment_depth'] = $depth;
    ?>
    	<li id="comment-<?php comment_ID() ?>" class="<?php charity_comment_class() ?>">
    		
			<?php charity_abovecomment() ?>
            
            <?php if ($comment->comment_approved == '0') _e("\t\t\t\t\t<span class='unapproved'>Your comment is awaiting moderation.</span>\n", 'charity') ?>

            <div class="commenter">
                <?php charity_commenter_link() ?> <br>
                <span class="time"><?php charity_commentmeta(TRUE); ?></span>
            </div>
            
            <div class="comment">
                <?php comment_text() ?>
                
				<?php // echo the comment reply link with help from Justin Tadlock http://justintadlock.com/ and Will Norris http://willnorris.com/
                    if($args['type'] == 'all' || get_comment_type() == 'comment') :
                        comment_reply_link(array_merge($args, array(
                            'reply_text' => __('Reply','charity'), 
                            'login_text' => __('Log in to reply.','charity'),
                            'depth' => $depth,
                            'before' => '<div class="comment-reply-link">', 
                            'after' => '</div>'
                        )));
                    endif;
                ?>
            </div>
            
			<?php charity_belowcomment() ?>
            <div class="clear"></div>
<?php }

// Custom callback to list pings in the Charity style
function charity_pings($comment, $args, $depth) {
       $GLOBALS['comment'] = $comment;
        ?>
    		<li id="comment-<?php comment_ID() ?>" class="<?php charity_comment_class() ?>">           
            
            	<?php if ($comment->comment_approved == '0') _e('\t\t\t\t\t<span class="unapproved">Your trackback is awaiting moderation.</span>\n', 'charity') ?>
            
    			<div class="commenter"><?php printf(__('%1$s on %2$s at %3$s', 'charity'),
    					get_comment_author_link(),
    					get_comment_date(),
    					get_comment_time() );
    					edit_comment_link(__('Edit', 'charity'), ' <span class="meta-sep">|</span> <span class="edit-link">', '</span>'); ?></div>
                        
                <div class="comment">
                    <?php comment_text() ?>
                </div>
	            <div class="clear"></div>
<?php }

?>