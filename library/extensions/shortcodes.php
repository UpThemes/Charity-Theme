<?php

// Providing information about Charity

function theme_name() {
    return THEMENAME;
}
add_shortcode('theme-name', 'theme_name');

function theme_author() {
    return THEMEAUTHOR;
}
add_shortcode('theme-author', 'theme_author');

function theme_uri() {
    return THEMEURI;
}
add_shortcode('theme-uri', 'theme_uri');

function theme_version() {
    return CHARITYVERSION;
}
add_shortcode('theme-version', 'theme_version');

// Providing information about the child theme

function child_name() {
    return TEMPLATENAME;
}
add_shortcode('child-name', 'child_name');

function child_author() {
    return TEMPLATEAUTHOR;
}
add_shortcode('child-author', 'child_author');

function child_uri() {
    return TEMPLATEURI;
}
add_shortcode('child-uri', 'child_uri');

function child_version() {
    return TEMPLATEVERSION;
}
add_shortcode('child-version', 'child_version');

?>