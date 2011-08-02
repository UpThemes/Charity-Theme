<?php
/*
Template Name: Archives Page
*/
?>
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
	
	                    // action hook for the 404 content
	                    charity_archives();
	
	                    edit_post_link(__('Edit', 'charity'),'<span class="edit-link">','</span>');
	                    
	                    ?>
	
					</div><!-- .entry-content -->
				</div><!-- #post -->
	
	        <?php
	        
	        charity_belowpost();
	        
	        // calling the comments template
	        	// calling the comments template
        		if (CHARITY_COMPATIBLE_COMMENT_HANDLING) {
       				if ( get_post_custom_values('comments') ) {
						// Add a key/value of "comments" to enable comments on pages!
	        			charity_comments_template();
        			}
        		} else {
       				charity_comments_template();
        		}
	        
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