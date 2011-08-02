<?php

    @header("HTTP/1.1 404 Not found", true, 404);

    // calling the header.php
    get_header();

    // action hook for placing content above #container
    charity_abovecontainer();

?>

		<div id="container">
		
			<?php charity_abovecontent(); ?>
		
			<div id="content">
			
				<?php charity_abovepost(); ?>
		
				<div id="post-0" class="post error404">
				
				<?php
		
    	            // action hook for the 404 content
    	            charity_404()
		
    	        ?>
				
				</div><!-- .post -->
				
				<?php charity_belowpost(); ?>
		
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