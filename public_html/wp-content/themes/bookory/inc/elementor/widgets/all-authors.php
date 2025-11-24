<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!bookory_is_mas_woocommerce_brands_activated()) {
    return;
}

use Elementor\Controls_Manager;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Bookory_Elementor_All_Author extends Elementor\Widget_Base {

    public function get_categories() {
        return array('bookory-addons');
    }

    /**
     * Get widget name.
     *
     * Retrieve tabs widget name.
     *
     * @return string Widget name.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_name() {
        return 'bookory-all-author';
    }

    /**
     * Get widget title.
     *
     * Retrieve tabs widget title.
     *
     * @return string Widget title.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_title() {
        return esc_html__('All Author', 'bookory');
    }

    /**
     * Get widget icon.
     *
     * Retrieve tabs widget icon.
     *
     * @return string Widget icon.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_icon() {
        return 'eicon-person';
    }

    /**
     * Register tabs widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {

        $this->start_controls_section(
            'section_author',
            [
                'label' => esc_html__('Author', 'bookory'),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => esc_html__('Posts Per Page', 'bookory'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
            ]
        );

        $this->add_control(
            'include',
            [
                'label'       => esc_html__('Include', 'bookory'),
                'type'        => Controls_Manager::SELECT2,
                'label_block' => true,
                'options'     => $this->get_product_brands(),
                'multiple'    => true,
            ]
        );

        $this->add_control(
            'exclude',
            [
                'label'       => esc_html__('Exclude', 'bookory'),
                'type'        => Controls_Manager::SELECT2,
                'label_block' => true,
                'options'     => $this->get_product_brands(),
                'multiple'    => true,
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => esc_html__('Order', 'bookory'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'desc',
                'options' => [
                    'asc'  => esc_html__('ASC', 'bookory'),
                    'desc' => esc_html__('DESC', 'bookory'),
                ],
            ]
        );

        $this->add_responsive_control(
            'column',
            [
                'label'   => esc_html__('Columns', 'bookory'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 3,
                'options' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
            ]
        );

        $this->add_control(
            'style',
            [
                'label'        => esc_html__('Style', 'bookory'),
                'type'         => \Elementor\Controls_Manager::SELECT,
                'options'      => [
                    '1' => esc_html__('Style 1', 'bookory'),
                    '2' => esc_html__('Style 2', 'bookory'),
                    '3' => esc_html__('Style 3', 'bookory'),
                ],
                'default'      => '1',
                'prefix_class' => 'style-'
            ]
        );

        $this->end_controls_section();

    }

    /**
     * Render tabs widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();

        $this->add_render_attribute('wrapper', 'class', 'elementor-author-wrapper');

        $this->add_render_attribute('row', 'class', 'row');

        if (!empty($settings['column_widescreen'])) {
            $this->add_render_attribute('row', 'data-elementor-columns-widescreen', $settings['column_widescreen']);
        }

        if (!empty($settings['column'])) {
            $this->add_render_attribute('row', 'data-elementor-columns', $settings['column']);
        } else {
            $this->add_render_attribute('row', 'data-elementor-columns', 5);
        }

        if (!empty($settings['column_laptop'])) {
            $this->add_render_attribute('row', 'data-elementor-columns-laptop', $settings['column_laptop']);
        }

        if (!empty($settings['column_tablet_extra'])) {
            $this->add_render_attribute('row', 'data-elementor-columns-tablet-extra', $settings['column_tablet_extra']);
        }

        if (!empty($settings['column_tablet'])) {
            $this->add_render_attribute('row', 'data-elementor-columns-tablet', $settings['column_tablet']);
        } else {
            $this->add_render_attribute('row', 'data-elementor-columns-tablet', 2);
        }

        if (!empty($settings['column_mobile_extra'])) {
            $this->add_render_attribute('row', 'data-elementor-columns-mobile-extra', $settings['column_mobile_extra']);
        }

        if (!empty($settings['column_mobile'])) {
            $this->add_render_attribute('row', 'data-elementor-columns-mobile', $settings['column_mobile']);
        } else {
            $this->add_render_attribute('row', 'data-elementor-columns-mobile', 1);
        }


        $brand_taxonomy = Mas_WC_Brands()->get_brand_taxonomy();

        $first_letter = (get_query_var('first_letter')) ? get_query_var('first_letter') : '';
        $page         = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $exclude = !empty($settings['exclude']) ? $settings['exclude'] : array();
        $include = !empty($settings['include']) ? $settings['include'] : array();

        $offset        = ($page - 1) * $settings['posts_per_page'];
        $taxonomy_args = array(
            'taxonomy'     => $brand_taxonomy,
            'hide_empty'   => true,
            'orderby'      => 'name',
            'slug'         => '',
            'include'      => $include,
            'exclude'      => $exclude,
            'number'       => $settings['posts_per_page'],
            'order'        => $settings['order'],
            'offset'       => $offset,
            'first_letter' => $first_letter
        );

        $total_terms_args = $taxonomy_args;
        unset($total_terms_args['offset']);
        $total_terms = wp_count_terms($brand_taxonomy, $total_terms_args);
        $pages       = ceil($total_terms / $settings['posts_per_page']);

        $brands = get_terms($taxonomy_args);

        $current_link = get_permalink();
        $index        = array_merge( apply_filters('bookory_data_author_filter', range('A', 'Z')), array('0-9'));

        ?>
        <div class="authors_a_z">
            <div class="all-authors">
                <ul class="author-index-pagination">
                    <?php

                    if (empty($first_letter) || !in_array($first_letter, $index)) {
                        echo '<li class="active"><a href="' . esc_url(remove_query_arg(array('first_letter', 'paged'), $current_link)) . '">' . esc_html__('ALL', 'bookory') . '</a></li>';
                    } else {
                        echo '<li><a href="' . esc_url(remove_query_arg(array('first_letter', 'paged'), $current_link)) . '">' . esc_html__('ALL', 'bookory') . '</a></li>';
                    }

                    foreach ($index as $i) {
                        $link = add_query_arg(array(
                            'first_letter' => $i,
                        ), $current_link);
                        if ($first_letter == $i) {
                            echo '<li class="active"><a href="' . esc_url($link) . '">' . esc_html($i) . '</a></li>';
                        } else {
                            echo '<li><a href="' . esc_url($link) . '">' . esc_html($i) . '</a></li>';
                        }
                    }
                    ?>
                </ul>

                <div class="author-content">
                    <?php if (!$brands || !is_array($brands)) : ?>
                        <span class="text-center"><?php echo esc_html__('No authors available.', 'bookory'); ?></span>
                    <?php else : ?>
                        <div <?php echo bookory_elementor_get_render_attribute_string('wrapper', $this); ?>>
                            <div <?php echo bookory_elementor_get_render_attribute_string('row', $this); ?>>
                                <?php

                                foreach ($brands as $index => $brand) :
                                    ?>
                                    <div class="column-item">
                                        <div class="author-block">
                                            <a class="thumbnail" href="<?php echo esc_url(get_term_link($brand->slug, $brand_taxonomy)); ?>" title="<?php echo esc_attr($brand->name); ?>">
                                                <?php echo mas_wcbr_get_brand_thumbnail_image($brand, 'brand-thumb'); ?>
                                            </a>
                                            <a class="name" href="<?php echo esc_url(get_term_link($brand->slug, $brand_taxonomy)); ?>" title="<?php echo esc_attr($brand->name); ?>">
                                                <?php echo esc_attr($brand->name); ?>
                                            </a>
                                            <div class="count"><?php echo esc_attr($brand->count) . esc_html__(' published books', 'bookory'); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($pages > 1) : ?>
                    <div class="author-pagination pagination">
                        <ul class="page-numbers">
                            <?php
                            for ($pagecount = 1; $pagecount <= $pages; $pagecount++) {
                                $link = add_query_arg(array(
                                    'paged'        => $pagecount,
                                    'first_letter' => $first_letter,
                                ), $current_link);
                                if ($page == $pagecount) {
                                    echo '<li><a class="page-numbers current" href="' . esc_url($link) . '">' . esc_html($pagecount) . '</a></li>';
                                } else {
                                    echo '<li><a class="page-numbers" href="' . esc_url($link) . '">' . esc_html($pagecount) . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <?php

    }

    protected function get_product_brands() {
        $brand_taxonomy = Mas_WC_Brands()->get_brand_taxonomy();
        $brands         = get_terms(array(
                'taxonomy'   => $brand_taxonomy,
                'hide_empty' => false,
            )
        );
        $results        = array();
        if (!is_wp_error($brands)) {
            foreach ($brands as $brand) {
                $results[$brand->term_id] = $brand->name;
            }
        }
        return $results;
    }


}

$widgets_manager->register(new Bookory_Elementor_All_Author());
