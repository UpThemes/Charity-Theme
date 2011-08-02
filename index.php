<?php
    
    global $up_options;
    
    // check if is home and if blog posts are disabled
    if(is_home() && $up_options->home_toggle)$disable_blog = TRUE;
    if(is_home())$home = TRUE;
    
    // calling the header.php
    get_header();

    // action hook for placing content above #container
    charity_abovecontainer();

?>

		<div id="container">
	
			<?php //charity_abovecontent(); ?>
	
			<div id="content">

				<?php 
				
				// create the above index content
				charity_above_indexcontent();

            	// create the navigation above the content
            	if(!$disable_blog)charity_navigation_above();
				
            	// calling the widget area 'index-top'
            	get_sidebar('index-top');
				
            	// action hook for placing content above the index loop
            	if(!$disable_blog)charity_above_indexloop();
				
            	// action hook creating the index loop
            	if(!$disable_blog)charity_indexloop();
				
            	// action hook for placing content below the index loop
            	if($disable_blog)charity_below_indexloop();
				
            	// calling the widget area 'index-bottom'
            	if($disable_blog)get_sidebar('index-bottom');
				
            	// create the navigation below the content
            	if(!$disable_blog)charity_navigation_below();
            	
            	?>
            					
			</div><!-- #content -->
		
			<?php charity_belowcontent(); ?> 
			
			<?php charity_sidebar(); ?>				
			
		</div><!-- #container -->

<?php 

    // action hook for placing content below #container
    charity_belowcontainer();
        
    // calling footer.php
    get_footer();

?>