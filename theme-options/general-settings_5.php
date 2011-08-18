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
    __("", "charity")
*/

$options = array (

    array(  "name" => __("Twitter", "charity"), 
            "desc" => __("Add your Twitter username (without the '@' symbol). This is used when someone tweets one of your entries.", "charity"),
            "id" => "twitter",
            "type" => "text"),

    array(  "name" => __("Facebook Page URL", "charity"),
            "desc" => __("Add your URL to your Facebook page.", "charity"),
            "id" => "facebook",
            "type" => "text"),

    array(  "name" => __("YouTube Username", "charity"),
            "desc" => __("Add your YouTube username.", "charity"),
            "id" => "youtube",
            "type" => "text"),

    array(  "name" => __("Feedburner", "charity"),
            "desc" => __("Add your username to redirect RSS feeds to Feedburner", "charity"),
            "id" => "feedburner",
            "type" => "text"),

    array(  "name" => __("Navigation Links Above Blog Posts", "charity"),
            "desc" => __("Do you want to show pagination links above single posts?", "charity"),
            "id" => "show_nav_above",
            "type" => "select",
			"default_text" => __("No", "charity"),
			"options" => array(
				__("Yes", "charity") => true
			)),

    array(  "name" => __("Navigation Links Below Blog Posts", "charity"),
            "desc" => __("Do you want to show pagination links below single posts?", "charity"),
            "id" => "show_nav_below",
            "type" => "select",
			"default_text" => __("No", "charity"),
			"options" => array(
				__("Yes", "charity") => true
			)),

    array(  "name" => __("Footer Text", "charity"),
            "desc" => __("Enter the text you'd like to have in the footer.", "charity"),
            "id" => "footer_text",
            "type" => "textarea",
            "value" => __("Copyright &copy; ", "charity") .date('Y'). __(" Charity. All rights reserved.<br />Charity is a registered trademark of ", "charity") . get_bloginfo('name') . '<br />' . __("All charitable donations to Charity are tax receiptable in Canada", "charity").'<br />'.__("Registered Charity No. 1234567890.", "charity"),
            "attr" => array("rows" => "8"))
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