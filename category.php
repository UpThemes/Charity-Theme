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
	        
	            // displays the page title
	            charity_page_title();
	
	            // create the navigation above the content
	            charity_navigation_above();
				
	            // action hook for placing content above the category loop
	            charity_above_categoryloop();			
	
	            // action hook creating the category loop
	            charity_categoryloop();
	
	            // action hook for placing content below the category loop
	            charity_below_categoryloop();			
	
	            // create the navigation below the content
	            charity_navigation_below();
	            
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