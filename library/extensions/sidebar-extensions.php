<?php


// Filter to create the sidebar
function charity_sidebar() {

  $show = TRUE;

	// Filters should return Boolean 
	$show = apply_filters('charity_sidebar', $show);
	
	if ($show) {
    get_sidebar();}
	
	return;
} // end charity_sidebar


// Main Aside Hooks
        
        // Located in sidebar.php 
	// Just before the home page
	function charity_homepage() {
	    do_action('charity_homepage');
	} // end charity_homepage
        
        // Located in sidebar.php 
	// regular hook for home page
	function widget_area_home_page() {
	    do_action('widget_area_home_page');
	} // end widget_area_home_page
        
        
        // Located in sidebar-index-top.php
	function charity_abovehomepage() {
		do_action('charity_abovehomepage');
	} // end charity_abovehomepage
        
        // Located in sidebar-index-top.php
	function charity_belowhomepage() {
		do_action('charity_belowhomepage');
	} // end charity_belowhomepage
        
        
	// Located in sidebar.php 
	// Just before the main asides (commonly used as sidebars)
	function charity_abovemainasides() {
	    do_action('charity_abovemainasides');
	} // end charity_abovemainasides


	// Located in sidebar.php 
	// regular hook for primary asides
	function widget_area_primary_aside() {
	    do_action('widget_area_primary_aside');
	} // end widget_area_primary_aside
	
	
	// Located in sidebar.php 
	// Between the main asides (commonly used as sidebars)
	function charity_betweenmainasides() {
	    do_action('charity_betweenmainasides');
	} // end charity_betweenmainasides


	// Located in sidebar.php 
	// regular hook for primary asides
	function widget_area_secondary_aside() {
	    do_action('widget_area_secondary_aside');
	} // end widget_area_secondary_aside
	
	
	// Located in sidebar.php 
	// after the main asides (commonly used as sidebars)
	function charity_belowmainasides() {
	    do_action('charity_belowmainasides');
	} // end charity_belowmainasides
	

// Index Aside Hooks

	
	// Located in sidebar-index-top.php
	function charity_aboveindextop() {
		do_action('charity_aboveindextop');
	} // end charity_aboveindextop
	

	// Located in sidebar-index-top.php
	function widget_area_index_top() {
		do_action('widget_area_index_top');
	} // end widget_area_index_top
	
	
	// Located in sidebar-index-top.php
	function charity_belowindextop() {
		do_action('charity_belowindextop');
	} // end charity_belowindextop
	
	
	// Located in sidebar-index-insert.php
	function charity_aboveindexinsert() {
		do_action('charity_aboveindexinsert');
	} // end charity_aboveindexinsert
	
	// ocated in sidebar-index-insert.php
	function widget_area_index_insert() {
		do_action('widget_area_index_insert');
	} // end widget_area_index_insert
	
	
	// Located in sidebar-index-insert.php
	function charity_belowindexinsert() {
		do_action('charity_belowindexinsert');
	} // end charity_belowindexinsert	
	

	// Located in sidebar-index-bottom.php
	function charity_aboveindexbottom() {
		do_action('charity_aboveindexbottom');
	} // end charity_aboveindexbottom
	
	// Located in sidebar-index-bottom.php
	function widget_area_index_bottom() {
		do_action('widget_area_index_bottom');
	} // end widget_area_index_bottom
	
	
	// Located in sidebar-index-bottom.php
	function charity_belowindexbottom() {
		do_action('charity_belowindexbottom');
	} // end charity_belowindexbottom	
	

// Footer Asides


	// Located in sidebar-footer-one.php
	function widget_area_footer_one() {
		do_action('widget_area_footer_one');
	} // end charity_abovesingletop


	// Located in sidebar-footer-two.php
	function widget_area_footer_two() {
		do_action('widget_area_footer_two');
	} // end charity_abovesingletop

	// Located in sidebar-footer-three.php
	function widget_area_footer_three() {
		do_action('widget_area_footer_three');
	} // end charity_abovesingletop


// Single Post Asides


	// Located in sidebar-single-top.php
	function charity_abovesingletop() {
		do_action('charity_abovesingletop');
	} // end charity_abovesingletop


	// Located in sidebar-single-top.php
	function widget_area_single_top() {
		do_action('widget_area_single_top');
	} // end charity_abovesingletop

	
	// Located in sidebar-single-top.php
	function charity_belowsingletop() {
		do_action('charity_belowsingletop');
	} // end charity_belowsingletop
	
	
	// Located in sidebar-single-insert.php
	function charity_abovesingleinsert() {
		do_action('charity_abovesingleinsert');
	} // end charity_abovesingleinsert
	
	
	// Located in sidebar-single-insert.php
	function widget_area_single_insert() {
		do_action('widget_area_single_insert');
	} // end charity_abovesingleinsert
	
	
	// Located in sidebar-single-insert.php
	function charity_belowsingleinsert() {
		do_action('charity_belowsingleinsert');
	} // end charity_belowsingleinsert	
	

	// Located in sidebar-single-bottom.php
	function charity_abovesinglebottom() {
		do_action('charity_abovesinglebottom');
	} // end charity_abovesinglebottom
	

	// Located in sidebar-single-bottom.php
	function widget_area_single_bottom() {
		do_action('widget_area_single_bottom');
	} // end widget_area_single_bottom
	
	
	// Located in sidebar-single-bottom.php
	function charity_belowsinglebottom() {
		do_action('charity_belowsinglebottom');
	} // end charity_belowsinglebottom	
	


// Page Aside Hooks


	// Located in sidebar-page-top.php
	function charity_abovepagetop() {
		do_action('charity_abovepagetop');
	} // end charity_abovepagetop


	// Located in sidebar-page-top.php
	function widget_area_page_top() {
		do_action('widget_area_page_top');
	} // end widget_area_page_top
	
	
	// Located in sidebar-page-top.php
	function charity_belowpagetop() {
		do_action('charity_belowpagetop');
	} // end charity_belowpagetop


	// Located in sidebar-page-bottom.php
	function charity_abovepagebottom() {
		do_action('charity_abovepagebottom');
	} // end charity_abovepagebottom


	// Located in sidebar-page-bottom.php
	function widget_area_page_bottom() {
		do_action('widget_area_page_bottom');
	} // end widget_area_page_bottom

	
	// Located in sidebar-page-bottom.php
	function charity_belowpagebottom() {
		do_action('charity_belowpagebottom');
	} // end charity_belowpagebottom	



// Subsidiary Aside Hooks
	

	// Located in sidebar-subsidiary.php
	function charity_abovesubasides() {
		do_action('charity_abovesubasides');
	} // end charity_abovesubasides
	

	// Located in sidebar-subsidiary.php
	function charity_belowsubasides() {
		do_action('charity_belowsubasides');
	} // end charity_belowsubasides
    
    function charity_subsidiaryopen() {
        if ( is_active_sidebar('1st-subsidiary-aside') || is_active_sidebar('2nd-subsidiary-aside') || is_active_sidebar('3rd-subsidiary-aside') ) { // one of the subsidiary asides has a widget ?>
            
            <div id="subsidiary">
            
        <?php
        }
    }
    add_action('widget_area_subsidiaries', 'charity_subsidiaryopen', 10);
	

	// Located in sidebar-subsidiary.php
	function charity_before_first_sub() {
		do_action('charity_before_first_sub');
	} // end charity_before_first_sub
    
    function add_before_first_sub() {
        if ( is_active_sidebar('1st-subsidiary-aside') || is_active_sidebar('2nd-subsidiary-aside') || is_active_sidebar('3rd-subsidiary-aside') ) { // one of the subsidiary asides has a widget
            charity_before_first_sub();
        }
    }
    add_action('widget_area_subsidiaries', 'add_before_first_sub',20);

	// Located in sidebar-subsidiary.php
	function widget_area_subsidiaries() {
		do_action('widget_area_subsidiaries');
	} // end widget_area_1st_subsidiary_aside

	// Located in sidebar-subsidiary.php
	function charity_between_firstsecond_sub() {
		do_action('charity_between_firstsecond_sub');
	} // end charity_between_firstsecond_sub
    
    function add_between_firstsecond_sub() {
        if ( is_active_sidebar('1st-subsidiary-aside') || is_active_sidebar('2nd-subsidiary-aside') || is_active_sidebar('3rd-subsidiary-aside') ) { // one of the subsidiary asides has a widget
            charity_between_firstsecond_sub();
        }
    }
    add_action('widget_area_subsidiaries', 'add_between_firstsecond_sub',40);


	// Located in sidebar-subsidiary.php
	function charity_between_secondthird_sub() {
		do_action('charity_between_secondthird_sub');
	} // end charity_between_secondthird_sub
    
    function add_between_secondthird_sub() {
        if ( is_active_sidebar('1st-subsidiary-aside') || is_active_sidebar('2nd-subsidiary-aside') || is_active_sidebar('3rd-subsidiary-aside') ) { // one of the subsidiary asides has a widget
            charity_between_secondthird_sub();
        }
    }
    add_action('widget_area_subsidiaries', 'add_between_secondthird_sub',60);
	
	
	// Located in sidebar-subsidiary.php
	function charity_after_third_sub() {
		do_action('charity_after_third_sub');
	} // end charity_after_third_sub	

    
    function add_after_third_sub() {
        if ( is_active_sidebar('1st-subsidiary-aside') || is_active_sidebar('2nd-subsidiary-aside') || is_active_sidebar('3rd-subsidiary-aside') ) { // one of the subsidiary asides has a widget
            charity_after_third_sub();
        }
    }
    add_action('widget_area_subsidiaries', 'add_after_third_sub',80);
    
    function charity_subsidiaryclose() {
        if ( is_active_sidebar('1st-subsidiary-aside') || is_active_sidebar('2nd-subsidiary-aside') || is_active_sidebar('3rd-subsidiary-aside') ) { // one of the subsidiary asides has a widget ?>
            
            </div><!-- #subsidiary -->
            
        <?php
        }
    }
    add_action('widget_area_subsidiaries', 'charity_subsidiaryclose', 200);