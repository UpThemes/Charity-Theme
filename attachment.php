<?php

    // calling the header.php
    get_header();

    // action hook for placing content above #container
    charity_abovecontainer();

?>

		<div id="container">
		
			<?php charity_abovecontent(); ?>
		
			<div id="content">
	
	            <?php
	            
	            the_post();
	            
	            // displays the page title
				charity_page_title();
				
				charity_abovepost();
	            
	            ?>
	            
				<div id="post-<?php the_ID();
					echo '" ';
					if (!(CHARITY_COMPATIBLE_POST_CLASS)) {
						post_class();
						echo '>';
					} else {
						echo 'class="';
						charity_post_class();
						echo '">';
					}
	                
	                // creating the post header
	                charity_postheader();
	                
	                ?>
	                
					<div class="entry-content">
						<div class="entry-attachment"><?php the_attachment_link($post->post_ID, true) ?></div>
	                    
	                        <?php 
	                        
	                        the_content(more_text());
	
	                        wp_link_pages('before=<div class="page-link">' .__('Pages:', 'charity') . '&after=</div>');
	                        
	                        ?>
	                        
					</div><!-- .entry-content -->
	                
					<?php
	                
	                // creating the post footer
	                charity_postfooter();
	                
	                ?>
	                
				</div><!-- #post -->
	
	            <?php
	            
	            charity_belowpost();
	            
	            comments_template();
	            
	            ?>
	
			</div><!-- #content -->
			
			<?php charity_belowcontent(); ?> 
			
		</div><!-- #container -->

<?php 

    // action hook for placing content below #container
    charity_belowcontainer();

    // calling the standard sidebar 
    charity_sidebar();

    // calling footer.php
    get_footer();

?>