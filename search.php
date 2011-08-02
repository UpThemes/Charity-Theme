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
	            
	            if (have_posts()) {
	
	                // displays the page title
	                charity_page_title();
	
	                // create the navigation above the content
	                charity_navigation_above();
				
	                // action hook for placing content above the search loop
	                charity_above_searchloop();			
	
	                // action hook creating the search loop
	                charity_searchloop();
	
	                // action hook for placing content below the search loop
	                charity_below_searchloop();			
	
	                // create the navigation below the content
	                charity_navigation_below();
	
	            } else {
	            	
	           		charity_abovepost();
	                
	                ?>
	
				<div id="post-0" class="post noresults">
					<h1 class="entry-title"><?php _e('Nothing Found', 'charity') ?></h1>
					<div class="entry-content">
						<p><?php _e('Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'charity') ?></p>
					</div><!-- .entry-content -->
					<form id="noresults-searchform" method="get" action="<?php bloginfo('url') ?>/">
						<div>
							<input id="noresults-s" name="s" type="text" value="<?php echo esc_html(stripslashes($_GET['s'])) ?>" size="40" />
							<input id="noresults-searchsubmit" name="searchsubmit" type="submit" value="<?php _e('Find', 'charity') ?>" />
						</div>
					</form>
				</div><!-- #post -->
	
	            <?php
	            
	            	charity_belowpost();
	            
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