<?php
/**
 * Carrington Build Loop Module
 * Performs a loop based on several different filter criteria
 * set via admin interface.
 * 
 * There's a base class that outputs full loop content, but 2 class 
 * extensions which extend it, but change it to "excerpts" or "titles"
 */
if (!class_exists('cfct_module_loop') && class_exists('cfct_build_module')) {
	class cfct_module_loop extends cfct_build_module {
		
		protected $content_display_options = array(
			'title' => 'Titles Only',
			'excerpt' => 'Titles &amp; Excerpts',
			'content' => 'Titles &amp; Post Content'
		);
		
		protected $default_display_args = array();
		
		protected $default_item_count = 10;
		
		public function __construct() {
			// We need to enqueue the suggest script so we can use it later for type-ahead search
			$this->enqueue_scripts();
			
			$opts = array(
				'description' => 'Choose and display a set of posts (any post type).',
				'icon' => 'loop/icon.png'
			);
			parent::__construct('cfct-module-loop', __('Loop', 'carrington-build'), $opts);
			
			// Taxonomy Filter Request Handler
			$this->request_handler();
		}
		
		/**
		 * Handle ajax requests
		 *
		 * @return void - function echoes back over ajax
		 */
		protected function request_handler() {
			if (!empty($_GET['cf_action'])) {
				switch ($_GET['cf_action']) {
					case 'cf_taxonomy_by_object_autocomplete':
						$post_type = strip_tags(stripslashes($_GET['cf_object_type']));
						
						$html = $this->get_taxonomy_section($post_type);
						
						echo cf_json_encode(array('items' => count($taxes), 'html' => $html));
						exit;
					case 'cf_taxonomy_filter_autocomplete':
						$search = '%'.strip_tags(stripslashes($_GET['q'])); // We want a wildcard in front as well.
						$tax = strip_tags(stripslashes($_GET['tax']));
						$html = '';
						
						// Build our HTML
						$items = array();
						if (!empty($search)) {
							$terms = get_terms($tax, array('name__like' => $search));
							if (is_array($terms)) {
								foreach ($terms as $term) {
									$items[] = $term->name;
								}
							}
						}
						if (!empty($items)) {
							echo implode("\n", $items);
						}
						else {
							echo __('No Matching Taxonomies', 'carrington-build');
						}
						exit;
				}
			}
			
		}

# Output
	
		/**
		 * Display the module
		 *
		 * @param array $data - saved module data
		 * @param array $args - previously set up arguments from a child class
		 * @return string HTML
		 */
		public function display($data) {
			// Set default
			$args = $this->default_display_args;

			// Figure out post type
			$args['post_type'] = $data[$this->get_field_name('post_type')];
			
			// Filter by taxonomy?
			if (
				!empty($data[$this->get_field_name('taxonomy')]) 
				&& ($term = get_term_by(
						'id', 
						$data[$this->get_field_name('tax_filter')], 
						$data[$this->get_field_name('taxonomy')])
				)) {
				$args['taxonomy'] = $data[$this->get_field_name('taxonomy')];
				$args['term'] = $term->slug;
			}
				
			// Post Parent??
			$args['post_parent'] = !empty($data[$this->get_field_name('parent')]) ? $data[$this->get_field_name('parent')] : null;
			
			// Filter by Author?
			$args['author'] = !empty($data[$this->get_field_name('author')]) ? $data[$this->get_field_name('author')] : null;
			
			// Number of items
			$args['posts_per_page'] = intval(!empty($data[$this->get_field_name('item_count')]) ? $data[$this->get_field_name('item_count')] : null);
			
			// Don't respect stickies ???
			$args['caller_get_posts'] = 1;

			// Don't include this post, otherwise we'll get an infinite loop
			global $post;
			$args['post__not_in'] = array($post->ID);
			$args['display'] = $data[$this->get_field_name('display_type')];

			// put it all together now
			$title = ( !empty($data[$this->get_field_name('title')]) ? esc_html($data[$this->get_field_name('title')]) : '');
			$content = $this->get_custom_loop($data, $args);
			if ((!empty($data[$this->get_field_name('show_pagination')]) ? $data[$this->get_field_name('show_pagination')] : '') == 'yes' && !empty($data[$this->get_field_name('next_pagination_link')])) {
				$pagination_url = esc_attr($data[$this->get_field_name('next_pagination_link')]);
				$pagination_text = esc_html($data[$this->get_field_name('next_pagination_text')]);
			}
			
			return $this->load_view($data, compact('title', 'content', 'pagination_url', 'pagination_text'));
		}
		
		/**
		 * Echos out the custom loop
		 *
		 * @param array $data Custom options from the module
		 * @param array $args
		 * @return void - function echoes
		 */
		protected function custom_loop($data, $args = array()) {
			echo $this->get_custom_loop($data, $args);
		}

		/**
		 * Returns the HTML of the custom loop
		 *
		 * @param array $data Custom options from the module
		 * @param array $args
		 * @return string HTML
		 */
		protected function get_custom_loop($data, $args = array()) {
			if ($args['display'] == 'title') {
				return $this->get_custom_loop_ul($data, $args);
			}
			else {
				return $this->get_custom_loop_default($data, $args);
			}
		}
		
		/**
		 * Returns the loop as default, with content or excerpt.  No list.
		 *
		 * @param string $data 
		 * @param string $args 
		 * @return string HTML
		 */
		protected function get_custom_loop_default($data, $args = array()) {
			ob_start();
			$query = new WP_Query($args);

			global $post;
			$post_bup = $post;
			
			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					
					ob_start();
					if ($args['display'] == 'excerpt') {
						$this->post_item_excerpts();
					}
					elseif ($args['display'] == 'content') {
						$this->post_item_content();
					}
					echo apply_filters('cfct-build-loop-item', ob_get_clean(), $data, $args, $query);
					
				}
			}
			
			// Reset the global $post object to the backed-up $post
			$post = $post_bup;
			setup_postdata($post);
			return apply_filters('cfct-build-loop-html', ob_get_clean(), $data, $args, $query);
		}
		
		/**
		 * Returns the loop as a UL with LIs
		 *
		 * @param string $data 
		 * @param string $args 
		 * @return string HTML
		 */
		protected function get_custom_loop_ul($data, $args = array()) {
			ob_start();

			$query = new WP_Query($args);
			$class = apply_filters('cfct-build-loop-title-ul-class', $this->id_base, $data, $args, $query);

			global $post;
			$post_bup = $post;

			if ($query->have_posts()) {
				echo '<ul class="'.esc_attr($class).'">';
				while ($query->have_posts()) {
					$query->the_post();

					ob_start();
					$this->post_item_li();
					echo apply_filters('cfct-build-loop-item', ob_get_clean(), $data, $args, $query);
				}
				?>
				</ul><!-- /<?php esc_attr_e($class); ?> -->
				<?php
			}

			// Reset the global $post object to the backed-up $post
			$post = $post_bup;
			setup_postdata($post);

			return apply_filters('cfct-build-loop-html', ob_get_clean(), $data, $args, $query);
		}
		
		/**
		 * Outputs the post item for an LI
		 *
		 * @return void - function echoes
		 */
		protected function post_item_li() {
			?>
			<li><a href="<?php the_permalink(); ?>" title="<?php esc_attr_e(get_the_title()); ?>"><?php the_title(); ?></a></li>
			<?php
		}	

		/**
		 * The way the twentyten theme does the excerpt of a post
		 *
		 * @return void - function echoes
		 */
		protected function the_excerpt() {
			?>
			<div data-post-id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'carrington-build' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

				<div class="entry-meta">
					<?php $this->posted_on(); ?>
				</div><!-- .entry-meta -->

				<div class="entry-summary">
					<?php the_excerpt(); ?>
				</div><!-- .entry-summary -->

				<div class="entry-utility">
					<?php if ( count( get_the_category() ) ) : ?>
						<span class="cat-links">
							<?php printf( __( '<span class="%1$s">Posted in</span> %2$s', 'carrington-build' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?>
						</span>
						<span class="meta-sep">|</span>
					<?php endif; ?>
					<?php
						$tags_list = get_the_tag_list( '', ', ' );
						if ( $tags_list ):
					?>
						<span class="tag-links">
							<?php printf( __( '<span class="%1$s">Tagged</span> %2$s', 'carrington-build' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
						</span>
						<span class="meta-sep">|</span>
					<?php endif; ?>
					<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'carrington-build' ), __( '1 Comment', 'carrington-build' ), __( '% Comments', 'carrington-build' ) ); ?></span>
					<?php edit_post_link( __( 'Edit', 'carrington-build' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
				</div><!-- .entry-utility -->
			</div><!-- #post-<?php the_ID(); ?>## -->
			<?php
		}

		/**
		 * Run excerpt functions
		 * 
		 * @return void
		 */
		protected function post_item_excerpts() {
			if (function_exists('cfct_excerpt')) {
				cfct_excerpt();
			}
			else {
				$this->the_excerpt();
			}
		}

		/**
		 * Run content functions
		 *
		 * @return void
		 */
		protected function post_item_content() {
			if (function_exists('cfct_content')) {
				cfct_content();
			}
			else {
				the_content();
			}
		}

# Admin Form
		
		/**
		 * Output the Admin Form
		 * 
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function admin_form($data) {			
			return '
				<div id="'.$this->id_base.'-admin-form-wrapper">'.
					$this->admin_form_title($data).
					$this->admin_form_post_types('post', $data).
					$this->admin_form_taxonomy_filter($data).
					$this->admin_form_display_options($data).
				'</div>';
		}
		
		/**
		 * Show module title input
		 *
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function admin_form_title($data) {
			return '
				<fieldset>
					<!-- title -->
					<div class="'.$this->id_base.'-input-wrapper">
						<label for="'.$this->get_field_id('title').'">'.__('Title', 'carrington-build').'</label>
						<input type="text" name="'.$this->get_field_id('title').'" id="'.$this->get_field_id('title').'" value="'.$data[$this->get_field_name('title')].'" />
					</div>
					<div class="clear"></div>
					<!-- /title -->
				</fieldset>
				';
		}
		
		/**
		 * Show module post types filter
		 * If only 1 post type is available the method ouputs a hidden
		 * element instead of a select list
		 *
		 * @see this::get_post_type_dropdown() for how $type is used
		 * @param string $type - 'post' or 'page'
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function admin_form_post_types($type, $data) {
			$post_types = $this->get_post_types($type);
			if (count($post_types) > 1) {
				$html .= '
					<fieldset class="cfct-ftl-border">
						<legend>Type</legend>
						<!-- post types select -->
						<div class="'.$this->id_base.'-input-wrapper '.$this->id_base.'-post-types-select">
							'.$this->get_post_type_dropdown($type, $post_types, $data).'
						</div>
						<div class="clear"></div>
						<!-- / post types select -->	
					</fieldset>
				';				
			}
			else {
				// if we only have one option then just set a hidden element
				$html .= '<input type="hidden" name="'.$this->get_field_name('post_type').'" value="'.$type.'" />';
			}
			return $html;
		}
		
		/**
		 * Show module taxonomy filter options
		 * 
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function admin_form_taxonomy_filter($data) {
			$html = '
				<fieldset class="cfct-ftl-border">
					<legend>Filtering</legend>
					<!-- taxonomy select -->
					<div class="'.$this->id_base.'-input-wrapper '.$this->id_base.'-post-category-select cfct-mod-loop-tax-wrapper">
				';
			$post_type = ($data[$this->get_field_name('post_type')]) ? $data[$this->get_field_name('post_type')] : 'post';
			$html .= $this->get_taxonomy_section($post_type, $data);
			$html .= '
					</div>
					<div class="clear"></div>
					<!-- /taxonomy select -->

					<!-- author select -->
					<div class="'.$this->id_base.'-input-wrapper '.$this->id_base.'-author-select">
						'.$this->get_author_dropdown($data).'
					</div>
					<div class="clear"></div>
					<!-- /author select -->
				</fieldset>
			';
			return $html;
		}
		
		/**
		 * Show module output display options
		 * 
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function admin_form_display_options($data) {
			return '
				<fieldset class="cfct-ftl-border">
					<legend>Display</legend>
					<!-- display type -->
					<div class="'.$this->id_base.'-input-wrapper '.$this->id_base.'-display-type">
						'.$this->get_display_type($data).'
					</div>
					<!-- / display type -->
					
					<!-- num posts input -->
					<div class="'.$this->id_base.'-input-wrapper '.$this->id_base.'-num-posts">
						'.$this->get_item_count_input($data).'
					</div>
					<div class="clear"></div>
					<!-- / num posts input -->
			
					<!-- pagination -->
					<div class="'.$this->id_base.'-input-wrapper '.$this->id_base.'-pagination">
						'.$this->get_pagination_section($data).'
					</div>
					<div class="clear"></div>
					<!-- /pagination -->				
				</fieldset>
			';
		}

# Admin helpers
		
		/**
		 * Get the display type select list
		 * 
		 * @param array $data
		 * @return string HTML
		 */
		protected function get_display_type($data) {
			$args = array(
				'label' => __('Show', 'carrington-build'),
				'default' => (!empty($data[$this->get_field_name('display_type')]) ? $data[$this->get_field_name('display_type')] : null)
			);
			$value = (!empty($data[$this->get_field_name('display_type')]) ? esc_attr($data[$this->get_field_name('display_type')]) : null);
			return $this->dropdown('display_type', $this->content_display_options, $value, $args);
		}
		
		/**
		 * Returns the entire taxonomy HTML structure and elements
		 *
		 * @param string $post_type
		 * @return string
		 */
		protected function get_taxonomy_section($post_type = 'post', $data = array()) {
			
			// Get our available taxonomies
			$taxes = get_object_taxonomies($post_type, 'objects');
			
			// If we have taxonomies for this type, then show the dropdown, etc.
			if (count($taxes)) {
				
				// Taxonomy Dropdown 
				$html = $this->get_taxonomy_dropdown($taxes, $data);
				
				$html .= '<div class="clear"></div>';

				// Taxonomy Filter
				$html .= $this->get_taxonomy_filter_section($data);
			}
			else {
				$html = '<p>'.__('No Taxonomies for selected post type.', 'carrington-build').'</p>';
				$html .= '<input type="hidden" name="'.$this->get_field_name('taxonomy').'" id="'.$this->get_field_id('taxonomy').'" value="0" />';
				$html .= '<input type="hidden" name="'.$this->get_field_name('tax_filter').'" id="tax-filter-type-ahead" value="" />';
			}
			return $html;
		}
		/**
		 * Returns a dropdown for available taxonomies
		 *
		 * @param array $items array of taxonomy objects
		 * @return string
		 */
		protected function get_taxonomy_dropdown($items, $data) {
			// Prepare our options
			foreach ($items as $k => $v) {
				$options[$k] = $v->labels->name;
			}	
			
			$value = (isset($data[$this->get_field_name('taxonomy')])) ? $data[$this->get_field_name('taxonomy')] : 0;
			$html = $this->dropdown(
				'taxonomy', 
				$options, 
				$value, 
				array(
					'label' => __('Taxonomy', 'carrington-build'), 
					'default' => array(
						'value' => 0, 
						'text' => 'Any'
					),
					'excludes' => array(
						'link_category',
						'nav_menu',
					)
				)
			);
			return $html;
		}

		/**
		 * Outputs the appropriate HTML structure/elements for the filtering of taxonomies
		 * - Category Dropdown
		 * - Type-ahead text input
		 * 
		 * @return string
		 **/
		protected function get_taxonomy_filter_section($data) {
			$tax = $data[$this->get_field_name('taxonomy')];
			
			// Wrapper class
			$wrap_class = $tax ? '' : ' class="hidden"';
			
			// Value 
			$value = isset($data[$this->get_field_name('tax_filter')]) ? $data[$this->get_field_name('tax_filter')] : '';
			
			// Set our wrapper and disabled states
			$select_wrapper_class = $type_ahead_wrapper_class = ' hidden';
			$select_disabled = $type_ahead_disabled = '';
			if ($tax) {
				// Figure out if we need to show the category dropdown or the type-ahead input
				$term = get_term_by('id', $value, $tax);
				if ($term) {
					// we have a term, now figure out if it's a category or something else
					if ($tax == 'category') {
						// we are a category
						$select_wrapper_class = '';
						$type_ahead_wrapper_class = ' hidden';
						$select_disabled = '';
						$type_ahead_disabled = ' disabled="disabled"';
					}
					else {
						// something besides a category
						$select_wrapper_class = ' hidden';
						$type_ahead_wrapper_class = '';
						$select_disabled = ' disabled="disabled"'; // because we can't add the disabled attr to the dropdown function....
						$type_ahead_disabled = '';
						
						// Set value = term name
						$value = esc_attr($term->name);
					}
				}
			}
			
			// Build the category dropdown
			$args = array(
				'echo' => 0,	
				'taxonomy' => 'category', // we only want the dropdown for categories, not tags, etc.
				'name' => $this->get_field_name('tax_filter'),
				'class' => null,
				'id' => 'tax-filter-select',
				'selected' => $value,
			);
			$dropdown = wp_dropdown_categories($args);
			
			// If the dropdown should be disabled do the deed here
			if (!empty($select_disabled)) {
				// Ugly way to hack in the disabled attr, but can't pass it to the function
				// that builds the dropdown.
				$dropdown = str_replace('<select ', '<select'.$select_disabled.' ', $dropdown);
			}
			
			// Do a type-ahead search
			$html = '
				<div'.$wrap_class.' id="tax-filter-wrapper">
					<label for="tax-filter-type-ahead">'.__('Filter Taxonomy', 'carrington-build').':  </label>
					
					<div class="tax-filter-select-wrapper'.$select_wrapper_class.'">
						'.$dropdown.'
					</div><!-- /tax-filter-select-wrapper -->
					
					<div class="tax-filter-text-wrapper'.$type_ahead_wrapper_class.'">
						<input name="'.$this->get_field_name('tax_filter').'" id="tax-filter-type-ahead" type="text" value="'.$value.'"'.$type_ahead_disabled.' />
					</div><!-- /tax-filter-text-wrapper -->
				</div>
			';
			
			return $html;
		}
		
		/**
		 * Get a list of post types available for selection
		 * Automatically excludes attachments, revisions, and nav_menu_items
		 * Post Type must be public to appear in this list
		 *
		 * @param string $type - 'post' for non-heirarchal objects, 'page' or heirarchal objects
		 * @return array
		 */
		protected function get_post_types($type) {
			$type_opts = array(
				'hierarchical' => ($type == 'post' ? false : true),
				'show_in_nav_menus' => 1
			);
			$post_types = get_post_types($type_opts, 'objects');
			
			// be safe, filter out the undesirables
			foreach (array('attachment', 'revision', 'nav_menu_item') as $item) {
				if (!empty($post_types[$item])) {
					unset($post_types[$item]);
				}
			}
			return $post_types;
		}
		
		/**
		 * Returns a dropdown for available post types
		 *
		 * @param array $items - array of post types objects
		 * @param array $data - saved module data
		 * @return string
		 */
		protected function get_post_type_dropdown($type, $items, $data) {
			foreach ($items as $k => $v) {
				$options[$k] = $v->labels->name;
			}
			
			$value = (isset($data[$this->get_field_name('post_type')])) ? $data[$this->get_field_name('post_type')] : $type;
			$html .= $this->dropdown(
				'post_type', 
				$options, 
				$value, 
				array(
					'label' => __('Post Type', 'carrington-build'),
					'excludes' => array(
						'attachment',
						'revision',
						'nav_menu_item',
					),
				)
			);
			return $html;
		}
		
		protected function get_item_count_input($data) {
			$html = '
				<label for="'.$this->get_field_id('item_count').'">'.__('Number of Items:', 'carrington-build').'</label>
				<input id="'.$this->get_field_id('item_count').'" name="'.$this->get_field_name('item_count').'" type="text" value="'.(!empty($data[$this->get_field_name('item_count')]) ? esc_attr($data[$this->get_field_name('item_count')]) : $this->default_item_count).'" />
			';
			return $html;
		}
		
		/**
		 * Pagination selection items
		 * 
		 * @param array $data - module save data
		 * @return string HTML
		 */
		protected function get_pagination_section($data) {
			$checkbox_value = (!empty($data[$this->get_field_name('show_pagination')])) ? $data[$this->get_field_name('show_pagination')] : '';
			$url_value = (!empty($data[$this->get_field_name('next_pagination_link')])) ? $data[$this->get_field_name('next_pagination_link')] : '';
			$text_value = (!empty($data[$this->get_field_name('next_pagination_text')])) ? $data[$this->get_field_name('next_pagination_text')] : '';
			$html = '
					<input type="checkbox" name="'.$this->get_field_name('show_pagination').'" id="'.$this->get_field_name('show_pagination').'" value="yes"'.checked('yes', $checkbox_value, false).' />
					<label for="'.$this->get_field_id('show_pagination').'">'.__('Pagination Link', 'carrington-build').'</label>
					<div id="pagination-wrapper">
						<div>
							<label for="'.$this->get_field_id('next_pagination_link').'">'.__('Link URL', 'carrington-build').'</label>
							<input type="text" name="'.$this->get_field_name('next_pagination_link').'" id="'.$this->get_field_id('next_pagination_link').'" value="'.$url_value.'" />
						</div>
						<div>
							<label for="'.$this->get_field_id('next_pagination_text').'">'.__('Link Text', 'carrington-build').'</label>
							<input type="text" name="'.$this->get_field_name('next_pagination_text').'" id="'.$this->get_field_id('next_pagination_text').'" value="'.$text_value.'" />
						</div>
					</div>
			';
			return $html;
		}
		
		/**
		 * Don't contribute to the post_content stored in the database
		 *
		 * @return null
		 */
		public function text() {
			return null;
		}
		
		public function admin_text($data) {
			return strip_tags($data[$this->get_field_name('title')]);
		}
				
		public function update($new_data,$old_data) {
			// Set default for item count
			$count =  $new_data[$this->get_field_id('item_count')];
			if (empty($count) && $count !== '0') {
				$new_data[$this->get_field_id('item_count')] = 10;
			} 
			
			// Change Name of Taxonomy filter to integer
			$tax = $new_data[$this->get_field_name('taxonomy')];
			if (!empty($tax)) {
				$filter = $new_data[$this->get_field_name('tax_filter')];
				$term_id = (is_numeric($filter)) ? $filter : 0;
				if (!empty($filter) && !is_numeric($filter) && !empty($tax)) {
					$term = get_term_by('name', $filter, $tax);
					if (!empty($term)) {
						$term_id = $term->term_id;
					}
				}
				$new_data[$this->get_field_name('tax_filter')] = $term_id;
			}
			return $new_data;
		}

		public function admin_css() {
			return preg_replace('/(\t){4}/m', '', '
				#pagination-wrapper,
				#pagination-wrapper div,
				#'.$this->id_base.'-admin-form-wrapper .'.$this->id_base.'-input-wrapper {
					clear: both;
				}
				#'.$this->id_base.'-admin-form-wrapper .'.$this->id_base.'-input-wrapper {
					margin: 10px 0;
				}
				#'.$this->id_base.'-admin-form-wrapper label {
					display: block;
					float: left;
					width: 130px;
					line-height: 30px;
				}
				#'.$this->id_base.'-admin-form-wrapper input[type=text],
				#'.$this->id_base.'-admin-form-wrapper checkbox,
				#'.$this->id_base.'-admin-form-wrapper select,
				.'.$this->id_base.'-input-replacement {
					float: left;
				}
				#'.$this->id_base.'-admin-form-wrapper input[type=text],
				.'.$this->id_base.'-input-replacement {
					width: 300px;
				}
				.'.$this->id_base.'-input-replacement {
					margin-top: 5px;
				}
				');
		}
		
		public function admin_js() {
			return preg_replace('/^(\t){3}/m', '', '
			cfct_builder.addModuleLoadCallback("'.$this->id_base.'",function(form) {

				// Post Type change
				$("#'.$this->get_field_id('post_type').'").change(function() {
					cfct_module_get_taxonomies_by_post_type($(this).val());
				});

				// Taxonomy change
				$("#'.$this->get_field_id('taxonomy').'").live("change", function() {
					var tmp_val = $(this).val();

					// If it is a category
					if (tmp_val == "category") {
						// Hide our text input, and disable it so it doesn\'t submit
						$(".tax-filter-text-wrapper").addClass("hidden");
						$("#tax-filter-type-ahead").attr("disabled", "disabled");

						// Enable the dropdown
						$("#tax-filter-select").removeAttr("disabled");
						
						// Show our dropdown
						$("#tax-filter-wrapper, .tax-filter-select-wrapper").removeClass("hidden");
					}
					// If it is "all"
					else if (tmp_val == 0) {
						// If we are doing "all" then remove value, hide
						$("#tax-filter-wrapper, .tax-filter-select-wrapper, .tax-filter-text-wrapper").addClass("hidden");
						$("#tax-filter-type-ahead").val("");
					}
					// If we are not doing the "all" or "category"
					else {
						// Set the filter value
						$("#tax-filter-type-ahead").val("");
						
						// Hide our category select, and disable so it doesn\'t submit
						$(".tax-filter-select-wrapper").addClass("hidden");
						$("#tax-filter-select").attr("disabled", "disabled");
						
						// Enable the text input
						$("#tax-filter-type-ahead").removeAttr("disabled");
						
						// Show our Text Input
						$("#tax-filter-wrapper, .tax-filter-text-wrapper").removeClass("hidden");
						
						// Attach the suggest to it
						cfct_module_attach_suggest(tmp_val);
					}
				});
				
				function cfct_module_attach_suggest(tax) {
					var e;
					e = $("#tax-filter-type-ahead");

					// unattach any other suggests for this box
					e.unbind();
					$(".ac_results").remove();

					// hook our new suggest on there
					e.suggest(
						"wp-admin/index.php?cf_action=cf_taxonomy_filter_autocomplete&tax="+encodeURI(tax), 
						{ 
							delay: 500, 
							minchars: 2, 
							multiple: false,
							onSelect: function() {
								$(this).attr("value", $(this).val());
							}
						}
					);
					$(".ac_results").css("zIndex", "10005");
				};
				
				// convenience wrapper for getting taxonomy field\'s value
				function cfct_module_get_taxonomy_value() {
					return $("#'.$this->get_field_id('taxonomy').'").val();
				}

				// Our initial load attachment suggest
				cfct_module_attach_suggest(cfct_module_get_taxonomy_value());

				function cfct_module_get_taxonomies_by_post_type(type) {
					$.get(
						"wp-admin/index.php",
						{
							cf_action : "cf_taxonomy_by_object_autocomplete",
							cf_object_type : type
						},
						function (r) {
							$(".cfct-mod-loop-tax-wrapper").slideUp("normal", function() {
								$(this).html(r.html);
								$(this).slideDown();
								cfct_module_attach_suggest(cfct_module_get_taxonomy_value());
							});
							return;
						},
						"json"
					);
				};
				// END taxonomy filtering....

				// Show/Hide for Pagination
				$("#'.$this->get_field_id('show_pagination').'").change(function() {
					_this = $(this);
					_wrapper = $("#pagination-wrapper");
					
					if (_this.is(":checked")) {
						_wrapper.show();
					}
					else {
						_wrapper.hide();
					}
				}).trigger("change");
			});

			');
		}
		
# Helpers
		
		/**
		 * Load required script
		 * 
		 * @return void
		 */
		protected function enqueue_scripts() {
			global $pagenow;
			if (is_admin() && in_array($pagenow, array('post.php', 'edit.php'))) {
				wp_enqueue_script('suggest');
			}
		}

		/**
		 * Prints HTML with meta information for the current post—date/time and author.  Thanks TwentyTen 1.0
		 */
		protected function posted_on() {
			printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'carrington-build' ),
				'meta-prep meta-prep-author',
				sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
					get_permalink(),
					esc_attr( get_the_time() ),
					get_the_date()
				),
				sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
					get_author_posts_url( get_the_author_meta( 'ID' ) ),
					sprintf( esc_attr__( 'View all posts by %s', 'carrington-build' ), get_the_author() ),
					get_the_author()
				)
			);
		}
		
		/**
		 * Generates a simple dropdown 
		 *
		 * @param string $field_name
		 * @param array $options
		 * @param int/string $value The current value of this field
		 * @param array $args Miscellaneous arguments
		 * @return string of <select> element's HTML
		 **/
		protected function dropdown($field_name, $options, $value = false, $args = '') {
			$defaults = array(
				'label' => '', // The text for the label element  
				'default' => null, // Add a default option ('all', 'none', etc.)
				'excludes' => array() // values to exclude from options
			);
			$args = array_merge($defaults, $args);
			extract($args);
			
			$options = (is_array($options)) ? $options : array();
			
			
			// Set a label if there is one
			$html = (!empty($label)) ? '<label for="'.$this->get_field_id($field_name).'">'.$label.': </label>' : '';
			
			// Start off the select element
			$html .= '
				<select class="'.$field_name.'-dropdown" name="'.$this->get_field_name($field_name).'" id="'.$this->get_field_id($field_name).'">
					';

			// Set a default option that's not in the list of options (i.e., all, none)
			if (is_array($default)) {
				$html .= '<option value="'.$default['value'].'"'.selected($default['value'], $value, false).'>'.esc_html($default['text']).'</option>';
			}
			
			// Loop through our options
			foreach ($options as $k => $v) {
				if (!in_array($k, $excludes)) {
					$html .= '<option value="'.$k.'"'.selected($k, $value, false).'>'.esc_html($v).'</option>';
				}
			}	
			
			// Close off our select element	
			$html .= '
				</select>
			';
			return $html;
		}
	}
	
}
cfct_build_register_module('cfct-module-loop','cfct_module_loop');
?>