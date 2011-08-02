<?php

    // Creating the doctype
    charity_create_doctype();
    echo " ";
    language_attributes();
    echo ">\n";
    
    // Creating the head profile
    charity_head_profile();

    // Creating the doc title
    charity_doctitle();
    
    // Creating the content type
    charity_create_contenttype();
    
    // Creating the description
    charity_show_description();
    
    // Creating the robots tags
    charity_show_robots();
    
    // Creating the canonical URL
    charity_canonical_url();
    
    // Loading the stylesheet
    charity_create_stylesheet();

	if (CHARITY_COMPATIBLE_FEEDLINKS) {    
    	// Creating the internal RSS links
    	charity_show_rss();
    
    	// Creating the comments RSS links
    	charity_show_commentsrss();
   	}
    
    // Creating the pingback adress
    charity_show_pingback();
    
    // Enables comment threading
    charity_show_commentreply();

    // Calling WordPress' header action hook
    wp_head();
    
?>

</head>

<?php 

charity_body();

// action hook for placing content before opening #wrapper
charity_before(); 

    // action hook for placing content above the theme header
    charity_aboveheader(); 
    
    ?>   

    <div id="header">
    
        <?php 
        
        // action hook creating the theme header
        charity_header();
        
        ?>

	</div><!-- #header-->
    <?php
    
    // action hook for placing content below the theme header
    charity_belowheader();
    
    ?>   
    <div id="wrapper">