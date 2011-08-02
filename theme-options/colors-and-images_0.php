<?php
/*  Array Options:
   
   name (string)
   desc (string)
   id (string)
   type (string) - text, color, image, select, multiple, textarea, page, pages, category, categories
   value (string) - default value - replaced when custom value is entered - (text, color, select, textarea, page, category)
   options (array)
   attr (array) - any form field attributes
   url (string) - for image type only - defines the default image

*/

$options = array (
    
    array(  "name" => __("Color Scheme", "charity"),
            "desc" => __("Select the color scheme you want to use.", "charity"),
            "id" => "style",
            "type" => "select",
			"default_text" => __("Default/Light Theme", "charity"),
            "options" => array(
						 __("Dark Theme", "charity") => __("dark", "charity")
						 )
    ),
    
    array(  "name" => __("Logo Image", "charity"),
            "desc" => __("Upload your own image or select from the gallery.", "charity"),
            "id" => "logo",
            "type" => "image",
            "value" => __("Upload Your Logo", "charity"),
			"url" => get_bloginfo('stylesheet_directory') . "/images/logo.png"
    ),
    
    array(  "name" => __("Main Header Image", "charity"),
            "desc" => __("Upload your own image or select from the gallery.", "charity"),
            "id" => "header_image",
            "type" => "image",
            "value" => __("Upload Image", "charity"),
			"url" => get_bloginfo('stylesheet_directory') . "/images/header_bg.jpg"
    ),
    
    array(  "name" => __("Default Link Color", "charity"),
            "desc" => __("Select a custom link color for the default state.", "charity"),
            "id" => "linkcolor",
            "type" => "color"
    ),
    
    array(  "name" => __("Hover Link Color", "charity"),
            "desc" => __("Select a custom link color for the hover state.", "charity"),
            "id" => "hovercolor",
            "type" => "color"
    ),
	array(  "name" => __("Active Link Color", "charity"),
            "desc" => __("Select a custom link color for the hover state.", "charity"),
            "id" => "activecolor",
            "type" => "color"
    )
);

/* ------------ Do not edit below this line ----------- */

//Check if theme options set
global $default_check;
global $default_options;

if(!$default_check):
    foreach($options as $option):
        if($option['type'] != 'image'):
            $default_options[$option['id']] = $option['value'];
        else:
            $default_options[$option['id']] = $option['url'];
        endif;
    endforeach;
    $update_option = get_option('up_themes_'.UPTHEMES_SHORT_NAME);
    if(is_array($update_option)):
        $update_option = array_merge($update_option, $default_options);
        update_option('up_themes_'.UPTHEMES_SHORT_NAME, $update_option);
    else:
        update_option('up_themes_'.UPTHEMES_SHORT_NAME, $default_options);
    endif;
endif;

render_options($options);

?>