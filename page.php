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
	        
	            // calling the widget area 'page-top'
	            get_sidebar('page-top');
	
	            the_post();
	            
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
	
	                    <?php
	                    
	                    the_content();
	                    
	                    wp_link_pages("\t\t\t\t\t<div class='page-link'>".__('Pages: ', 'charity'), "</div>\n", 'number');
	                    
	                    edit_post_link(__('Edit', 'charity'),'<span class="edit-link">','</span>') ?>
	
					</div><!-- .entry-content -->
				</div><!-- #post -->
	
	        <?php
	        
	        charity_belowpost();
	        
	        // calling the comments template
       		if (CHARITY_COMPATIBLE_COMMENT_HANDLING) {
				if ( get_post_custom_values('comments') ) {
					// Add a key/value of "comments" to enable comments on pages!
					charity_comments_template();
				}
			} else {
				charity_comments_template();
			}
	        
	        // calling the widget area 'page-bottom'
	        get_sidebar('page-bottom');
	        
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