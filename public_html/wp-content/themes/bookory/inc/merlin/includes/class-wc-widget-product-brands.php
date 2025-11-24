<?php
/**
 * Product Brands Widget
 *
 * @author   WPOpal
 * @category Widgets
 * @package  WooCommerce/Widgets
 * @version  2.3.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Product categories widget class.
 *
 * @extends WC_Widget
 */
class Bookory_Widget_Product_Brands extends WC_Widget {

	/**
	 * Category ancestors.
	 *
	 * @var array
	 */
	public $brand_ancestors;

	/**
	 * Current Category.
	 *
	 * @var bool
	 */
	public $current_brand;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_product_brands';
		$this->widget_description = esc_html__('A list or dropdown of product brands.', 'bookory');
		$this->widget_id          = 'woocommerce_product_brands';
		$this->widget_name        = esc_html__('Product Brands', 'bookory');
		$this->settings           = array(
			'title'              => array(
				'type'  => 'text',
				'std'   => esc_html__('Product Brands', 'bookory'),
				'label' => esc_html__('Title', 'bookory'),
			),
			'dropdown'           => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__('Show as dropdown', 'bookory'),
			),
			'show_logo'          => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__('Show logo brand', 'bookory'),
			),
			'count'              => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__('Show product counts', 'bookory'),
			),
			'hierarchical'       => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => esc_html__('Show hierarchy', 'bookory'),
			),
			'show_children_only' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__('Only show children of the current brand', 'bookory'),
			),
			'hide_empty'         => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__('Hide empty brands', 'bookory'),
			),
			'max_depth'          => array(
				'type'  => 'text',
				'std'   => '',
				'label' => esc_html__('Maximum depth', 'bookory'),
			),
		);

		parent::__construct();
	}

	/**
	 * Output widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Widget instance.
	 * @see WP_Widget
	 */
	public function widget($args, $instance) {
		global $wp_query, $post;

		$count              = isset($instance['count']) ? $instance['count'] : $this->settings['count']['std'];
		$hierarchical       = isset($instance['hierarchical']) ? $instance['hierarchical'] : $this->settings['hierarchical']['std'];
		$show_children_only = isset($instance['show_children_only']) ? $instance['show_children_only'] : $this->settings['show_children_only']['std'];
		$dropdown           = isset($instance['dropdown']) ? $instance['dropdown'] : $this->settings['dropdown']['std'];
		$hide_empty         = isset($instance['hide_empty']) ? $instance['hide_empty'] : $this->settings['hide_empty']['std'];
		$show_logo          = isset($instance['show_logo']) ? $instance['show_logo'] : $this->settings['show_logo']['std'];
		$dropdown_args      = array(
			'hide_empty' => $hide_empty,
		);
		$list_args          = array(
			'show_count'   => $count,
			'hierarchical' => $hierarchical,
			'taxonomy'     => 'product_brand',
			'hide_empty'   => $hide_empty,
		);
		$max_depth          = absint(isset($instance['max_depth']) ? $instance['max_depth'] : $this->settings['max_depth']['std']);

		$list_args['menu_order'] = false;
		$dropdown_args['depth']  = $max_depth;
		$list_args['depth']      = $max_depth;

		$this->current_brand   = false;
		$this->brand_ancestors = array();

		if (is_tax('product_brand')) {
			$this->current_brand   = $wp_query->queried_object;
			$this->brand_ancestors = get_ancestors($this->current_brand->term_id, 'product_brand');

		} elseif (is_singular('product')) {
			$product_category = wc_get_product_terms($post->ID, 'product_cat', apply_filters('woocommerce_product_brands_widget_product_terms_args', array(
				'orderby' => 'parent',
			)));

			if (!empty($product_category)) {
				$this->current_brand   = end($product_category);
				$this->brand_ancestors = get_ancestors($this->current_brand->term_id, 'product_cat');
			}
		}

		// Show Siblings and Children Only.
		if ($show_children_only && $this->current_brand) {
			if ($hierarchical) {
				$include = array_merge(
					$this->brand_ancestors,
					array($this->current_brand->term_id),
					get_terms(
						'product_brand',
						array(
							'fields'       => 'ids',
							'parent'       => 0,
							'hierarchical' => true,
							'hide_empty'   => false,
						)
					),
					get_terms(
						'product_brand',
						array(
							'fields'       => 'ids',
							'parent'       => $this->current_brand->term_id,
							'hierarchical' => true,
							'hide_empty'   => false,
						)
					)
				);
				// Gather siblings of ancestors.
				if ($this->brand_ancestors) {
					foreach ($this->brand_ancestors as $ancestor) {
						$include = array_merge($include, get_terms(
							'product_brand',
							array(
								'fields'       => 'ids',
								'parent'       => $ancestor,
								'hierarchical' => false,
								'hide_empty'   => false,
							)
						));
					}
				}
			} else {
				// Direct children.
				$include = get_terms(
					'product_brand',
					array(
						'fields'       => 'ids',
						'parent'       => $this->current_brand->term_id,
						'hierarchical' => true,
						'hide_empty'   => false,
					)
				);
			} // End if().

			$list_args['include']     = implode(',', $include);
			$dropdown_args['include'] = $list_args['include'];

			if (empty($include)) {
				return;
			}
		} elseif ($show_children_only) {
			$dropdown_args['depth']        = 1;
			$dropdown_args['child_of']     = 0;
			$dropdown_args['hierarchical'] = 1;
			$list_args['depth']            = 1;
			$list_args['child_of']         = 0;
			$list_args['hierarchical']     = 1;
		} // End if().

		$this->widget_start($args, $instance);

		if ($dropdown) {
			wc_product_dropdown_categories(apply_filters('woocommerce_product_brands_widget_dropdown_args', wp_parse_args($dropdown_args, array(
				'show_count'         => $count,
				'hierarchical'       => $hierarchical,
				'show_uncategorized' => 0,
				'selected'           => $this->current_brand ? $this->current_brand->slug : '',
				'taxonomy'           => 'product_brand',
				'name'               => 'product_brand',
				'class'              => 'dropdown_product_brand',
			))));
			wc_enqueue_js("
				jQuery( '.dropdown_product_brand' ).change( function() {
					if ( jQuery(this).val() != '' ) {
						var this_page = '';
						var home_url  = '" . esc_js(home_url('/')) . "';
						if ( home_url.indexOf( '?' ) > 0 ) {
							this_page = home_url + '&product_brand=' + jQuery(this).val();
						} else {
							this_page = home_url + '?product_brand=' + jQuery(this).val();
						}
						location.href = this_page;
					}
				});
			");
		} else {
			include_once(get_theme_file_path('inc/woocommerce/class-product-brand-list-walker.php'));

			$list_args['walker']                  = new Bookory_Product_Brand_List_Walker;
			$list_args['title_li']                = '';
			$list_args['pad_counts']              = 1;
			$list_args['show_option_none']        = esc_html__('No product brands exist.', 'bookory');
			$list_args['current_brand']           = ($this->current_brand) ? $this->current_brand->term_id : '';
			$list_args['current_brand_ancestors'] = $this->brand_ancestors;
			$list_args['max_depth']               = $max_depth;
			$list_args['show_logo']               = $show_logo;
			$id                                   = wp_generate_uuid4();

			echo '<ul class="product-brands" id="bookory-brands-' . $id . '">';
			wp_list_categories(apply_filters('woocommerce_product_brands_widget_args', $list_args));
			echo '</ul>';
		}

		$this->widget_end($args);
	}
}

add_action('widgets_init', function () {
	register_widget('Bookory_Widget_Product_Brands');
});

add_action('init', function () {
	$labels = array(
		'name'                       => esc_html__('Brands', 'bookory'),
		'singular_name'              => esc_html__('Brands', 'bookory'),
		'menu_name'                  => esc_html__('Brands', 'bookory'),
		'all_items'                  => esc_html__('All Brands', 'bookory'),
		'parent_item'                => esc_html__('Parent Brand', 'bookory'),
		'parent_item_colon'          => esc_html__('Parent Brand:', 'bookory'),
		'new_item_name'              => esc_html__('New Brand Name', 'bookory'),
		'add_new_item'               => esc_html__('Add New Brands', 'bookory'),
		'edit_item'                  => esc_html__('Edit Brand', 'bookory'),
		'update_item'                => esc_html__('Update Brand', 'bookory'),
		'separate_items_with_commas' => esc_html__('Separate Brand with commas', 'bookory'),
		'search_items'               => esc_html__('Search Brands', 'bookory'),
		'add_or_remove_items'        => esc_html__('Add or remove Brands', 'bookory'),
		'choose_from_most_used'      => esc_html__('Choose from the most used Brands', 'bookory'),
	);
	$args   = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => false,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'rewrite'           => array('slug' => 'product-brand')
	);
	register_taxonomy('product_brand', 'product', $args);
});

