<?php

// Creates the DOCTYPE section
function charity_create_doctype() {
    $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
    $content .= '<html xmlns="http://www.w3.org/1999/xhtml"';
    echo apply_filters('charity_create_doctype', $content);
} // end charity_create_doctype


// Creates the HEAD Profile
function charity_head_profile() {
    $content = '<head profile="http://gmpg.org/xfn/11">' . "\n";
    echo apply_filters('charity_head_profile', $content);
} // end charity_head_profile


// Get the page number adapted from http://efficienttips.com/wordpress-seo-title-description-tag/
function pageGetPageNo() {
    if (get_query_var('paged')) {
        print ' | Page ' . get_query_var('paged');
    }
} // end pageGetPageNo


// Located in header.php 
// Creates the content of the Title tag
// Credits: Tarski Theme
if (function_exists('childtheme_override_doctitle'))  {
    function charity_doctitle() {
    	childtheme_override_doctitle();
    }
} else {
	function charity_doctitle() {
		$site_name = get_bloginfo('name');
	    $separator = '|';
	        	
	    if ( is_single() ) {
	      $content = single_post_title('', FALSE);
	    }
	    elseif ( is_home() || is_front_page() ) { 
	      $content = get_bloginfo('description');
	    }
	    elseif ( is_page() ) { 
	      $content = single_post_title('', FALSE); 
	    }
	    elseif ( is_search() ) { 
	      $content = __('Search Results for:', 'charity'); 
	      $content .= ' ' . esc_html(stripslashes(get_search_query()));
	    }
	    elseif ( is_category() ) {
	      $content = __('Category Archives:', 'charity');
	      $content .= ' ' . single_cat_title("", false);;
	    }
	    elseif ( is_tag() ) { 
	      $content = __('Tag Archives:', 'charity');
	      $content .= ' ' . charity_tag_query();
	    }
	    elseif ( is_404() ) { 
	      $content = __('Not Found', 'charity'); 
	    }
	    else { 
	      $content = get_bloginfo('description');
	    }
	
	    if (get_query_var('paged')) {
	      $content .= ' ' .$separator. ' ';
	      $content .= 'Page';
	      $content .= ' ';
	      $content .= get_query_var('paged');
	    }
	
	    if($content) {
	      if ( is_home() || is_front_page() ) {
	          $elements = array(
	            'site_name' => $site_name,
	            'separator' => $separator,
	            'content' => $content
	          );
	      }
	      else {
	          $elements = array(
	            'content' => $content
	          );
	      }  
	    } else {
	      $elements = array(
	        'site_name' => $site_name
	      );
	    }
	
	    // Filters should return an array
	    $elements = apply_filters('charity_doctitle', $elements);
		
	    // But if they don't, it won't try to implode
	    if(is_array($elements)) {
	      $doctitle = implode(' ', $elements);
	    }
	    else {
	      $doctitle = $elements;
	    }
	    
	    $doctitle = "\t" . "<title>" . $doctitle . "</title>" . "\n\n";
	    
	    echo $doctitle;
	} // end charity_doctitle
}

// Creates the content-type section
function charity_create_contenttype() {
    $content  = "\t";
    $content .= "<meta http-equiv=\"Content-Type\" content=\"";
    $content .= get_bloginfo('html_type'); 
    $content .= "; charset=";
    $content .= get_bloginfo('charset');
    $content .= "\" />";
    $content .= "\n\n";
    echo apply_filters('charity_create_contenttype', $content);
} // end charity_create_contenttype

// The master switch for SEO functions
function charity_seo() {
		$content = TRUE;
		return apply_filters('charity_seo', $content);
}

// Creates the canonical URL
function charity_canonical_url() {
		if (charity_seo()) {
    		if ( is_singular() ) {
        		$canonical_url = "\t";
        		$canonical_url .= '<link rel="canonical" href="' . get_permalink() . '" />';
        		$canonical_url .= "\n\n";        
        		echo apply_filters('charity_canonical_url', $canonical_url);
				}
    }
} // end charity_canonical_url


// switch use of charity_the_excerpt() - default: ON
function charity_use_excerpt() {
    $display = TRUE;
    $display = apply_filters('charity_use_excerpt', $display);
    return $display;
} // end charity_use_excerpt


// switch use of charity_the_excerpt() - default: OFF
function charity_use_autoexcerpt() {
    $display = FALSE;
    $display = apply_filters('charity_use_autoexcerpt', $display);
    return $display;
} // end charity_use_autoexcerpt


// Creates the meta-tag description
function charity_create_description() {
		$content = '';
		if (charity_seo()) {
    		if (is_single() || is_page() ) {
      		  if ( have_posts() ) {
          		  while ( have_posts() ) {
            		    the_post();
										if (charity_the_excerpt() == "") {
                    		if (charity_use_autoexcerpt()) {
                        		$content ="\t";
														$content .= "<meta name=\"description\" content=\"";
                        		$content .= charity_excerpt_rss();
                        		$content .= "\" />";
                        		$content .= "\n\n";
                    		}
                		} else {
                    		if (charity_use_excerpt()) {
                        		$content ="\t";
                        		$content .= "<meta name=\"description\" content=\"";
                        		$content .= charity_the_excerpt();
                        		$content .= "\" />";
                        		$content .= "\n\n";
                    		}
                		}
            		}
        		}
    		} elseif ( is_home() || is_front_page() ) {
        		$content ="\t";
        		$content .= "<meta name=\"description\" content=\"";
        		$content .= get_bloginfo('description');
        		$content .= "\" />";
        		$content .= "\n\n";
    		}
    		echo apply_filters ('charity_create_description', $content);
		}
} // end charity_create_description


// meta-tag description is switchable using a filter
function charity_show_description() {
    $display = TRUE;
    $display = apply_filters('charity_show_description', $display);
    if ($display) {
        charity_create_description();
    }
} // end charity_show_description


// create meta-tag robots
function charity_create_robots() {
        global $paged;
		if (charity_seo()) {
    		$content = "\t";
    		if((is_home() && ($paged < 2 )) || is_front_page() || is_single() || is_page() || is_attachment()) {
				$content .= "<meta name=\"robots\" content=\"index,follow\" />";
    		} elseif (is_search()) {
        		$content .= "<meta name=\"robots\" content=\"noindex,nofollow\" />";
    		} else {	
        		$content .= "<meta name=\"robots\" content=\"noindex,follow\" />";
    		}
    		$content .= "\n\n";
    		if (get_option('blog_public')) {
    				echo apply_filters('charity_create_robots', $content);
    		}
		}
} // end charity_create_robots


// meta-tag robots is switchable using a filter
function charity_show_robots() {
    $display = TRUE;
    $display = apply_filters('charity_show_robots', $display);
    if ($display) {
        charity_create_robots();
    }
} // end charity_show_robots


// Located in header.php
// creates link to style.css
function charity_create_stylesheet() {
    $content = "\t";
    $content .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"";
    $content .= get_bloginfo('stylesheet_url');
    $content .= "\" />";
    $content .= "\n\n";
    echo apply_filters('charity_create_stylesheet', $content);
}


// rss usage is switchable using a filter
function charity_show_rss() {
    $display = TRUE;
    $display = apply_filters('charity_show_rss', $display);
    if ($display) {
        $content = "\t";
        $content .= "<link rel=\"alternate\" type=\"application/rss+xml\" href=\"";
        $content .= get_bloginfo('rss2_url');
        $content .= "\" title=\"";
        $content .= esc_html(get_bloginfo('name'));
        $content .= " " . __('Posts RSS feed', 'charity');
        $content .= "\" />";
        $content .= "\n";
        echo apply_filters('charity_rss', $content);
    }
} // end charity_show_rss


// comments rss usage is switchable using a filter
function charity_show_commentsrss() {
    $display = TRUE;
    $display = apply_filters('charity_show_commentsrss', $display);
    if ($display) {
        $content = "\t";
        $content .= "<link rel=\"alternate\" type=\"application/rss+xml\" href=\"";
        $content .= get_bloginfo('comments_rss2_url');
        $content .= "\" title=\"";
        $content .= esc_html(get_bloginfo('name'));
        $content .= " " . __('Comments RSS feed', 'charity');
        $content .= "\" />";
        $content .= "\n\n";
        echo apply_filters('charity_commentsrss', $content);
    }
} // end charity_show_commentsrss


// pingback usage is switchable using a filter
function charity_show_pingback() {
    $display = TRUE;
    $display = apply_filters('charity_show_pingback', $display);
    if ($display) {
        $content = "\t";
        $content .= "<link rel=\"pingback\" href=\"";
        $content .= get_bloginfo('pingback_url');
        $content .= "\" />";
        $content .= "\n\n";
        echo apply_filters('charity_pingback_url',$content);
    }
} // end charity_show_pingback


// comment reply usage is switchable using a filter
function charity_show_commentreply() {
    $display = TRUE;
    $display = apply_filters('charity_show_commentreply', $display);
    if ($display)
        if ( is_singular() ) 
            wp_enqueue_script( 'comment-reply' ); // support for comment threading
} // end charity_show_commentreply

// Create the default arguments for wp_page_menu()
function charity_page_menu_args() {
	$args = array (
		'sort_column' => 'menu_order',
		'menu_class'  => 'menu',
		'include'     => '',
		'exclude'     => '',
		'echo'        => FALSE,
		'show_home'   => FALSE,
		'link_before' => '',
		'link_after'  => ''
	);
	return $args;
}
add_filter('wp_page_menu_args','charity_page_menu_args');

function charity_init_navmenu() {
	if (function_exists( 'register_nav_menu' )) {
		register_nav_menu( 'primary-menu', apply_filters('charity_primary_menu_name', __( 'Primary Menu', 'charity' ) ) );
		register_nav_menu( 'secondary-menu', apply_filters('charity_upper_menu_name', __( 'Secondary Menu', 'charity' ) ) );
	}
}

add_action('init', 'charity_init_navmenu');

// Just after the opening body tag, before anything else.
function charity_before() {
    do_action('charity_before');
} // end charity_before


// Just before the header div
function charity_aboveheader() {
    do_action('charity_aboveheader');
} // end charity_aboveheader


// Used to hook in the HTML and PHP that creates the content of div id="header">
function charity_header() {
    do_action('charity_header');
} // end charity_header


// Functions that hook into charity_header()

	// Open #branding
	// In the header div
	if (function_exists('childtheme_override_brandingopen'))  {
	    function charity_brandingopen() {
	    	childtheme_override_brandingopen();
	    }
	} else {
		function charity_brandingopen() {
			echo "<div id=\"branding\">\n";
		}
	    add_action('charity_header','charity_brandingopen',1);
	}	
	
	// Create the blog title
	// In the header div
	if (function_exists('childtheme_override_blogtitle'))  {
	    function charity_blogtitle() {
	    	childtheme_override_blogtitle();
	    }
	} else {
	    function charity_blogtitle() { ?>
	    		
	    		<?php global $up_options; ?>
	    		
	    		<?php if ( $up_options->logo ): ?>
	    		
	    		<div id="blog-title"><span><a href="<?php echo trailingslashit(get_bloginfo('url')); ?>" title="<?php bloginfo('name') ?>" rel="home"><img src="<?php echo $up_options->logo; ?>" alt="<?php bloginfo('name'); ?>"></a></span></div>
	    		
	    		<?php else: ?>
	    		
	    		<div id="blog-title"><span><a href="<?php echo trailingslashit(get_bloginfo('url')); ?>" title="<?php bloginfo('name') ?>" rel="home"><?php bloginfo('name') ?></a></span></div>
	    		<?php endif; ?>
	    		
	    <?php }
	    add_action('charity_header','charity_blogtitle',3);
	}
	
	// Create the blog description
	// In the header div
	if (function_exists('childtheme_override_blogdescription'))  {
	    function charity_blogdescription() {
	    	childtheme_override_blogdescription();
	    }
	} else {
	    function charity_blogdescription() {
	    	$blogdesc = '"blog-description">' . get_bloginfo('description');
			if (is_home() || is_front_page()) { 
	        	echo "\t\t<h1 id=$blogdesc</h1>\n\n";
	        } else {	
	        	echo "\t\t<div id=$blogdesc</div>\n\n";
	        }
	    }
	    add_action('charity_header','charity_blogdescription',5);
	}
	
	// Close #branding
	// In the header div
	if (function_exists('childtheme_override_brandingclose'))  {
	    function charity_brandingclose() {
	    	childtheme_override_brandingclose();
	    }
	} else {
	    function charity_brandingclose() {
	    	echo "\t\t</div><!--  #branding -->\n";
	    }
	    add_action('charity_header','charity_brandingclose',7);
	}
	
	// Create #access
	// In the header div
	if (function_exists('childtheme_override_access'))  {
	    function charity_access() {
	    	childtheme_override_access();
	    }
	} else {
	    function charity_access() { ?>
	    
        <div class="container_12">
            
            <div id="access" class="nav">
                            
                <?php 
                    
                if ( function_exists("wp_nav_menu") ) {
                    echo wp_nav_menu(array (
						'theme_location'	=> 'primary-menu',
						'container'			=> 'div',
						'container_id'		=> 'primary-menu',
						'container_class'	=> 'nav',
						'menu_class'		=> 'sf-menu',
						'fallback_cb'		=> 'wp_page_menu',
						'echo'				=> false
					));
                }
                
                ?>
                
            </div><!-- #access -->
            
            <div id="secondary_nav" class="nav">
            
                <?php echo wp_nav_menu(array(
                                    'theme_location'	=> 'secondary-menu',
                                    'container'			=> false,
                                    'menu_class'		=> 'sf-menu',
                                    'fallback_cb'		=> 'wp_page_menu',
                                    'echo'				=> false
                )); ?>
            
            </div><!-- #secondary_nav -->
            
        </div>
        
		<?php }
	}

    add_action('charity_header','charity_access',9);
    
// End of functions that hook into charity_header()

		
// Just after the header div
function charity_belowheader() {
    do_action('charity_belowheader');
} // end charity_belowheader
		

?>