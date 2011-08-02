<?php
/**
 * Template Name: Blog
 *
 * This template allows you to display the latest posts on any page of the site.
 *
 */

    // calling the header.php
    get_header();

    // action hook for placing content above #container
    charity_abovecontainer();

?>

		<div id="container">
	
			<?php charity_abovecontent(); ?>
	
			<div id="content">
            
            <?php
			$wp_query = new WP_Query();
			$wp_query->query( array( 'posts_per_page' => get_option( 'posts_per_page' ), 'paged' => $paged ) );
			$more = 0;
			?>

				<?php 
            	
            	// create the navigation above the content
            	charity_navigation_above();
				
            	// calling the widget area 'index-top'
            	get_sidebar('index-top');
				
            	// action hook for placing content above the index loop
            	charity_above_indexloop();
				
            	// action hook creating the index loop
            	charity_indexloop();
				
            	// action hook for placing content below the index loop
            	charity_below_indexloop();
				
            	// calling the widget area 'index-bottom'
            	get_sidebar('index-bottom');
				
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