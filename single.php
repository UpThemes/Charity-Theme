<?php

	global $up_options;

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
    	        
    	        // create the navigation above the content
				if( $up_options->show_nav_above )
					charity_navigation_above();
		
    	        // calling the widget area 'single-top'
    	        get_sidebar('single-top');
		
    	        // action hook creating the single post
    	        charity_singlepost();
				
    	        // calling the widget area 'single-insert'
    	        get_sidebar('single-insert');
		
    	        // create the navigation below the content
				if( $up_options->show_nav_below )
					charity_navigation_below();
		
    	        // calling the comments template
    	        charity_comments_template();
		
    	        // calling the widget area 'single-bottom'
    	        get_sidebar('single-bottom');
    	        
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