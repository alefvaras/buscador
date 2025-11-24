<?php

namespace Elementor;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Bookory_Elementor_Tabs extends Widget_Base {

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
        return 'bookory-tabs';
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
        return esc_html__('bookory Tabs', 'bookory');
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
        return 'eicon-tabs';
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     *
     * @return array Widget keywords.
     * @since 2.1.0
     * @access public
     *
     */
    public function get_keywords() {
        return ['tabs', 'accordion', 'toggle'];
    }


    public function get_css_config() {
        return $this->get_widget_css_config( 'tabs' );
    }


    /**
     * Get HTML wrapper class.
     *
     * Retrieve the widget container class. Can be used to override the
     * container class for specific widgets.
     *
     * @since 2.0.9
     * @access protected
     */
    protected function get_html_wrapper_class() {
        return 'elementor-widget-' . $this->get_name() . ' elementor-widget-tabs';
    }

    public function get_script_depends() {
        return ['bookory-elementor-tabs'];
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

        $templates = Plugin::instance()->templates_manager->get_source('local')->get_items();

        $options = [
            '0' => '— ' . esc_html__('Select', 'bookory') . ' —',
        ];

        $types = [];

        foreach ($templates as $template) {
            $options[$template['template_id']] = $template['title'] . ' (' . $template['type'] . ')';
            $types[$template['template_id']]   = $template['type'];
        }

        $this->start_controls_section(
            'section_tabs',
            [
                'label' => esc_html__('Tabs', 'bookory'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'tab_title',
            [
                'label'       => esc_html__('Title', 'bookory'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Tab Title', 'bookory'),
                'placeholder' => esc_html__('Tab Title', 'bookory'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'tab_number',
            [
                'label'       => esc_html__('Number', 'bookory'),
                'type'        => Controls_Manager::TEXT,
            ]
        );

        $repeater->add_control(
            'source',
            [
                'label'   => esc_html__('Source', 'bookory'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'html',
                'options' => [
                    'html'     => esc_html__('HTML', 'bookory'),
                    'template' => esc_html__('Template', 'bookory')
                ]
            ]
        );

        $repeater->add_control(
            'tab_template',
            [
                'label'       => esc_html__('Choose Template', 'bookory'),
                'default'     => 0,
                'type'        => Controls_Manager::SELECT,
                'options'     => $options,
                'types'       => $types,
                'label_block' => 'true',
                'condition'   => [
                    'source' => 'template',
                ],
            ]
        );

        $repeater->add_control(
            'tab_content',
            [
                'label'       => esc_html__('Content', 'bookory'),
                'default'     => esc_html__('Tab Content', 'bookory'),
                'placeholder' => esc_html__('Tab Content', 'bookory'),
                'type'        => Controls_Manager::WYSIWYG,
                'show_label'  => false,
                'condition'   => [
                    'source' => 'html',
                ],
            ]
        );

        $this->add_control(
            'tabs',
            [
                'label'       => esc_html__('Tabs Items', 'bookory'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [
                        'tab_title'   => esc_html__('Tab #1', 'bookory'),
                        'tab_content' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'bookory'),
                    ],
                    [
                        'tab_title'   => esc_html__('Tab #2', 'bookory'),
                        'tab_content' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'bookory'),
                    ],
                ],
                'title_field' => '{{{ tab_title }}}',
            ]
        );

        $this->add_control(
            'view',
            [
                'label'   => esc_html__('View', 'bookory'),
                'type'    => Controls_Manager::HIDDEN,
                'default' => 'traditional',
            ]
        );

        $this->add_control(
            'type',
            [
                'label'        => esc_html__('Type', 'bookory'),
                'type'         => Controls_Manager::SELECT,
                'default'      => 'horizontal',
                'options'      => [
                    'horizontal' => esc_html__('Horizontal', 'bookory'),
                    'vertical'   => esc_html__('Vertical', 'bookory'),
                ],
                'prefix_class' => 'elementor-tabs-view-',
                'separator'    => 'before',
            ]
        );

        $this->add_control(
            'tabs_align_horizontal',
            [
                'label' => esc_html__( 'Alignment', 'bookory' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    '' => [
                        'title' => esc_html__( 'Start', 'bookory' ),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'bookory' ),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__( 'End', 'bookory' ),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'stretch' => [
                        'title' => esc_html__( 'Justified', 'bookory' ),
                        'icon' => 'eicon-h-align-stretch',
                    ],
                ],
                'prefix_class' => 'elementor-tabs-alignment-',
                'condition' => [
                    'type' => 'horizontal',
                ],
            ]
        );

        $this->add_control(
            'tabs_align_vertical',
            [
                'label' => esc_html__( 'Alignment', 'bookory' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    '' => [
                        'title' => esc_html__( 'Start', 'bookory' ),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'bookory' ),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'end' => [
                        'title' => esc_html__( 'End', 'bookory' ),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                    'stretch' => [
                        'title' => esc_html__( 'Justified', 'bookory' ),
                        'icon' => 'eicon-v-align-stretch',
                    ],
                ],
                'prefix_class' => 'elementor-tabs-alignment-',
                'condition' => [
                    'type' => 'vertical',
                ],
            ]
        );

        $this->add_responsive_control(
            'navigation_width',
            [
                'label' => esc_html__('Width', 'bookory'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => ['%', 'px', 'vw'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-tabs-wrapper' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'type' => 'vertical',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_tab_button',
            [
                'label' => esc_html__('Button', 'bookory'),
            ]
        );

        $this->add_control(
            'tab_button_text',
            [
                'label' => esc_html__('Button Text', 'bookory'),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'tab_button_number',
            [
                'label' => esc_html__('Button Number', 'bookory'),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'tab_button_link',
            [
                'label' => esc_html__('Button Link', 'bookory'),
                'placeholder' => esc_html__('https://your-link.com', 'bookory'),
                'type' => Controls_Manager::URL,
            ]
        );

        $this->add_responsive_control(
            'tab_button_padding',
            [
                'label' => esc_html__('Padding', 'bookory'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tab-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->add_responsive_control(
            'tab_button_margin',
            [
                'label' => esc_html__('Margin', 'bookory'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tab-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );


        $this->end_controls_section();

        $this->start_controls_section(
            'section_title_style',
            [
                'label' => esc_html__('Title', 'bookory'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'title_width',
            [
                'label' => esc_html__('Width', 'bookory'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => ['%', 'px', 'vw'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-tab-title' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'type' => 'vertical',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'tab_typography',
                'selector' => '{{WRAPPER}}.elementor-widget-tabs .elementor-tab-title',
            ]
        );

        $this->start_controls_tabs('tabs_carousel_dots_style');

        $this->start_controls_tab(
            'tab_title_color_normal',
            [
                'label' => esc_html__('Normal', 'bookory'),
            ]
        );

        $this->add_control(
            'tab_color',
            [
                'label'     => esc_html__('Color', 'bookory'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bookory-tabs.elementor-widget-tabs .elementor-tab-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_title_background_color',
            [
                'label'     => esc_html__('Background Color', 'bookory'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bookory-tabs.elementor-widget-tabs .elementor-tab-title' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_title_color_active',
            [
                'label' => esc_html__('Active', 'bookory'),
            ]
        );

        $this->add_control(
            'tab_active_color',
            [
                'label'     => esc_html__('Color', 'bookory'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bookory-tabs.elementor-widget-tabs .elementor-tab-title.elementor-active' => 'color: {{VALUE}};'
                ],
            ]
        );

        $this->add_control(
            'tab_title_background_active_color',
            [
                'label'     => esc_html__('Background Color', 'bookory'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-bookory-tabs.elementor-widget-tabs .elementor-tab-title.elementor-active' => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'tab_border_color_active',
            [
                'label'     => esc_html__('Border Color', 'bookory'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-tab-title' => 'border-color: {{VALUE}};'
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'divider_height',
            [
                'label' => esc_html__('Divider Height', 'bookory'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                    ],
                ],
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-tab-title:after' => 'height: calc( {{SIZE}}{{UNIT}}/2 );',
                    '{{WRAPPER}} .elementor-tab-title:before' => 'height: calc( {{SIZE}}{{UNIT}}/2 );',
                ],
            ]
        );

        $this->add_control(
            'hide_search',
            [
                'label'        => esc_html__('Sub Divider', 'bookory'),
                'type'         => Controls_Manager::SWITCHER,
                'prefix_class' => 'sub-divider-tab-bookory-',
            ]
        );


        $this->add_responsive_control('tab_title_padding',
            [
                'label'      => esc_html__('Padding', 'bookory'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bookory-tabs.elementor-widget-tabs .elementor-tab-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->add_responsive_control('tab_title_margin',
            [
                'label'      => esc_html__('Margin', 'bookory'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-bookory-tabs.elementor-widget-tabs .elementor-tab-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => 'tab_border',
                'selector'  => '{{WRAPPER}}.elementor-widget-bookory-tabs.elementor-widget-tabs.elementor-tabs-view-horizontal .elementor-tab-title',
            ]
        );


        $this->add_control(
            'tab_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'bookory'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-tab-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_style',
            [
                'label' => esc_html__('Content', 'bookory'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label'     => esc_html__('Color', 'bookory'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-tab-content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'content_typography',
                'selector' => '{{WRAPPER}} .elementor-tab-content',
            ]
        );


        $this->add_responsive_control('tab_content_padding',
            [
                'label'      => esc_html__('Padding', 'bookory'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-tab-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->add_responsive_control('tab_content_margin',
            [
                'label'      => esc_html__('Margin', 'bookory'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-tab-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => 'tab_content_border',
                'selector'  => '{{WRAPPER}} .elementor-tabs-content-wrapper',
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
        $tabs = $this->get_settings_for_display('tabs');
        $setting = $this->get_settings_for_display();

        $id_int = substr($this->get_id_int(), 0, 3);
        ?>
        <div class="elementor-tabs" role="tablist">
            <div class="elementor-tabs-wrapper">
                <?php
                foreach ($tabs as $index => $item) :
                    $tab_count = $index + 1;
                    $class_item = 'elementor-repeater-item-' . $item['_id'];
                    $class_content = ($index == 0) ? 'elementor-active' : '';
                    $tab_title_setting_key = $this->get_repeater_setting_key('tab_title', 'tabs', $index);

                    $this->add_render_attribute($tab_title_setting_key, [
                        'id'            => 'elementor-tab-title-' . $id_int . $tab_count,
                        'class'         => [
                            'elementor-tab-title',
                            $class_content,
                            $class_item
                        ],
                        'data-tab'      => $tab_count,
                        'role'          => 'tab',
                        'aria-controls' => 'elementor-tab-content-' . $id_int . $tab_count,
                    ]);
                    ?>
                    <div <?php echo bookory_elementor_get_render_attribute_string($tab_title_setting_key, $this); ?>>
                        <span class="title"><?php echo esc_html($item['tab_title']); ?></span>
                        <?php if ( ! empty($item['tab_number'])): ?>
                            <span class="number"><?php echo esc_html($item['tab_number']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php
                if ($setting['tab_button_text'] != '') {
                    $button_html = '<span class="text">' . $setting['tab_button_text'] . '</span><span class="number">'. $setting['tab_button_number'] .'</span><i class="bookory-icon-right-arrow"></i>';
                    $this->add_render_attribute('link', 'class', 'tab-button');
                    if (!empty($setting['tab_button_link']['url'])) {
                        $this->add_link_attributes('link', $setting['tab_button_link']);
                    }

                    echo '<a ' . $this->get_render_attribute_string('link') . '>' . $button_html . '</a>';
                }
                ?>

            </div>
            <div class="elementor-tabs-content-wrapper">
                <?php
                foreach ($tabs as $index => $item) :
                    $tab_count = $index + 1;
                    $class_item = 'elementor-repeater-item-' . $item['_id'];
                    $class_content = ($index == 0) ? 'elementor-active' : '';
                    $tab_content_setting_key = $this->get_repeater_setting_key('tab_content', 'tabs', $index);

                    $this->add_render_attribute($tab_content_setting_key, [
                        'id'              => 'elementor-tab-content-' . $id_int . $tab_count,
                        'class'           => [
                            'elementor-tab-content',
                            'elementor-clearfix',
                            $class_content,
                            $class_item
                        ],
                        'data-tab'        => $tab_count,
                        'role'            => 'tabpanel',
                        'aria-labelledby' => 'elementor-tab-title-' . $id_int . $tab_count,
                    ]);

                    $this->add_inline_editing_attributes($tab_content_setting_key, 'advanced'); ?>
                    <div <?php echo bookory_elementor_get_render_attribute_string($tab_content_setting_key, $this); // WPCS: XSS ok.?>>
                        <?php if ('html' === $item['source']): ?>
                            <?php echo bookory_elementor_parse_text_editor($item['tab_content'], $this); // WPCS: XSS ok. ?>
                        <?php else: ?>
                            <?php echo Plugin::instance()->frontend->get_builder_content_for_display($item['tab_template']); ?>
                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

$widgets_manager->register(new Bookory_Elementor_Tabs());
