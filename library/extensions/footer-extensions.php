<?php


// Located in footer.php
// Just before the footer div
function charity_abovefooter() {
    do_action('charity_abovefooter');
} // end charity_abovefooter

// Located in footer.php
// Just after the footer div
function charity_footer() {
    do_action('charity_footer');
} // end charity_footer

// Located in footer.php
// Just after the footer div
function charity_belowfooter() {
    do_action('charity_belowfooter');
} // end charity_belowfooter


// Located in footer.php 
// Just before the closing body tag, after everything else.
function charity_after() {
    do_action('charity_after');
} // end charity_after


// Functions that hook into charity_footer()
	
	if (function_exists('childtheme_override_subsidiaries'))  {
		function charity_subsidiaries() {
			childtheme_override_subsidiaries();
		}
	}
    
	if (function_exists('childtheme_override_siteinfoopen'))  {
		function charity_siteinfoopen() {
			childtheme_override_siteinfoopen();
		}
	}
    
	if (function_exists('childtheme_override_siteinfo'))  {
		function charity_siteinfo() {
			childtheme_override_siteinfo();
		}
	}
    
	if (function_exists('childtheme_override_siteinfoclose'))  {
		function charity_siteinfoclose() {
			childtheme_override_siteinfoclose();
		}
	}