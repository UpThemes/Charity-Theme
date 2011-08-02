
    </div><!-- #wrapper -->
    
	<?php
	
	global $up_options;
	
    // action hook for placing content above the footer
    charity_abovefooter();
    
    ?>

	<div id="footer">
    
    	<div class="inner">
        
			<?php if( $up_options->widgetized_footer ): ?>
            
            	<div class="alpha grid_3">
	                <?php get_sidebar('footer-one'); ?>
                </div>
                
                <div class="grid_3">
	                <?php get_sidebar('footer-two'); ?>
				</div>
                
                <div class="omega grid_3">
                	<?php get_sidebar('footer-three'); ?>
                </div>
                
            <?php else: ?>
    
                <div class="nav">
                    <h3 class="widgettitle"><?php _e("Pages"); ?></h3>
					<ul><?php wp_list_pages("title_li="); ?></ul>
                </div>
                <div class="nav">
                    <h3 class="widgettitle"><?php _e("Categories"); ?></h3>
                    <ul>
                    <?php wp_list_categories("title_li="); ?>
                    </ul>
                </div>
                <div class="nav">
                    <h3 class="widgettitle"><?php _e("Connect with Us"); ?></h3>
                    <ul>
                        <?php if( $up_options->facebook ): ?>
                        <li><a href="<?php echo $up_options->facebook; ?>" title="Facebook"><?php _e("Facebook"); ?></a></li>
                        <?php endif; ?>
                        <?php if( $up_options->twitter ): ?>
                        <li><a href="<?php echo "http://twitter.com/" . $up_options->twitter; ?>" title="Twitter"><?php _e("Twitter"); ?></a></li>
                        <?php endif; ?>
                        <?php if( $up_options->youtube ): ?>
                        <li><a href="<?php echo "http://www.youtube.com/" . $up_options->youtube; ?>" title="YouTube"><?php _e("YouTube"); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="copyright">
                    <p><?php echo $up_options->footer_text; ?></p>
                </div>
                
                <div class="clear"></div>
                
            <?php endif; ?>
        
			<?php
            
            // action hook creating the footer 
            charity_footer();
            
            ?>
                        
        </div>
        
	</div><!-- #footer -->
	
    <?php
    
    // action hook for placing content below the footer
    charity_belowfooter();
    
    if (apply_filters('charity_close_wrapper', true)) {
    	echo '</div><!-- #wrapper .hfeed -->';
    }
    
    ?>  

<?php 

// calling WordPress' footer action hook
wp_footer();

// action hook for placing content before closing the BODY tag
charity_after(); 

?>

</body>
</html>