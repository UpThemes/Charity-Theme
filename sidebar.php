<?php
    
    // check if is home and if blog posts are disabled
    global $up_options;
    if(is_home())$home = TRUE;
    
    // action hook for placing content above the main asides
    if($home)charity_homepage();
    
    // action hook for placing content above the main asides
    if(!$home)charity_abovemainasides();

    // action hook creating the primary aside
    widget_area_primary_aside();	
	
    // action hook for placing content between primary and secondary aside
    if(!$home)charity_betweenmainasides();

    // action hook creating the secondary aside
    widget_area_secondary_aside();		
	
    // action hook for placing content below the main asides
    charity_belowmainasides(); 
    
?>