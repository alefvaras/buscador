<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Group_Control_Box_Shadow;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

class Bookory_Elementor_Team_Box extends Elementor\Widget_Base
{

    /**
     * Get widget name.
     *
     * Retrieve testimonial widget name.
     *
     * @return string Widget name.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_name()
    {
        return 'bookory-team-box';
    }

    /**
     * Get widget title.
     *
     * Retrieve testimonial widget title.
     *
     * @return string Widget title.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_title()
    {
        return esc_html__('Team Box', 'bookory');
    }

    /**
     * Get widget icon.
     *
     * Retrieve testimonial widget icon.
     *
     * @return string Widget icon.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_icon()
    {
        return 'eicon-person';
    }

    public function get_categories()
    {
        return array('bookory-addons');
    }

    /**
     * Register testimonial widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'section_team',
            [
                'label' => esc_html__('Team', 'bookory'),
            ]
        );

        $this->add_control(
            'view',
            [
                'label' => esc_html__('View', 'bookory'),
                'type' => Controls_Manager::HIDDEN,
                'default' => 'traditional',
            ]
        );

        $this->add_control(
            'teams',
            [
                'label' => esc_html__('Team Item', 'bookory'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'team_layout',
            [
                'label' => esc_html__('Layout', 'bookory'),
                'type' => Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => esc_html__('Layout 1', 'bookory'),
                    '2' => esc_html__('Layout 2', 'bookory'),
                ]
            ]
        );

        $this->add_control(
            'not_hover',
            [
                'label' => esc_html__('Not Hover', 'bookory'),
                'type'  => Controls_Manager::SWITCHER,
                'prefix_class' => 'team-box-not-hover-',
                'condition' => [
                    'team_layout' => '1',
                ],
            ]
        );

        $this->add_control(
            'image',
            [
                'label' => esc_html__('Choose Image', 'bookory'),
                'default' => [
                    'url' => Elementor\Utils::get_placeholder_image_src(),
                ],
                'type' => Controls_Manager::MEDIA,
                'show_label' => false,
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
                'default' => 'full',
                'separator' => 'none',
            ]
        );

        $this->add_control(
            'job',
            [
                'label' => esc_html__('Job', 'bookory'),
                'default' => 'Designer',
                'type' => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'name',
            [
                'label' => esc_html__('Name', 'bookory'),
                'default' => 'John Doe',
                'type' => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'phone',
            [
                'label' => esc_html__('Phone', 'bookory'),
                'default' => '+(84)4 1800 33555',
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'team_layout' => '2',
                ],

            ]
        );

        $this->add_control(
            'email',
            [
                'label' => esc_html__('Email', 'bookory'),
                'default' => 'contact@example.com',
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'team_layout' => '2',
                ],
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => esc_html__('Link to', 'bookory'),
                'placeholder' => esc_html__('https://your-link.com', 'bookory'),
                'type' => Controls_Manager::URL,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'social_icon',
            [
                'label' => esc_html__('Icon', 'bookory'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'default' => [
                    'value' => 'fab fa-facebook',
                    'library' => 'fa-brands',
                ],
            ]
        );

        $repeater->add_control(
            'social_link',
            [
                'label' => esc_html__('Social Link', 'bookory'),
                'placeholder' => esc_html__('https://www.facebook.com', 'bookory'),
                'default' => 'https://www.facebook.com',
                'type' => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'socials',
            [
                'label' => esc_html__('Socials', 'bookory'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'social_icon' => [
                            'value' => 'fab fa-facebook',
                            'library' => 'fa-brands',
                        ],
                        'social_link' => 'https://www.facebook.com',
                    ]
                ],
                'title_field' => '{{{ social_link }}}',

            ]
        );

        $this->end_controls_section();


        // Wrapper.
        $this->start_controls_section(
            'section_style_team_wrapper',
            [
                'label' => esc_html__('Wrapper', 'bookory'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'wrapper_padding_inner',
            [
                'label' => esc_html__('Padding', 'bookory'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-teams-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'wrapper_margin',
            [
                'label' => esc_html__('Margin', 'bookory'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-teams-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        // Image.
        $this->start_controls_section(
            'section_style_team_image',
            [
                'label' => esc_html__('Image', 'bookory'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );


        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'selector' => '{{WRAPPER}} .team-image img',
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => esc_html__('Border Radius', 'bookory'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .team-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .team-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_padding',
            [
                'label' => esc_html__('Padding', 'bookory'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .team-image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'image_margin',
            [
                'label' => esc_html__('Margin', 'bookory'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .team-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Name.
        $this->start_controls_section(
            'section_style_team_name',
            [
                'label' => esc_html__('Name', 'bookory'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'name_text_color',
            [
                'label' => esc_html__('Color', 'bookory'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-teams-wrapper .team-name a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-teams-wrapper .team-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'name_text_color_hover',
            [
                'label' => esc_html__('Hover Color', 'bookory'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-teams-wrapper .team-name a:hover' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .elementor-teams-wrapper .team-name:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'name_typography',
                'selector' => '{{WRAPPER}} .elementor-teams-wrapper .team-name',
            ]
        );

        $this->add_responsive_control(
            'name_space',
            [
                'label' => esc_html__('Spacing', 'bookory'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-teams-wrapper .team-name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Job.
        $this->start_controls_section(
            'section_style_team_job',
            [
                'label' => esc_html__('Job', 'bookory'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'job_text_color',
            [
                'label' => esc_html__('Text Color', 'bookory'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-teams-wrapper .team-job' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'job_typography',
                'selector' => '{{WRAPPER}} .elementor-teams-wrapper .team-job',
            ]
        );

        $this->add_responsive_control(
            'job_space',
            [
                'label' => esc_html__('Spacing', 'bookory'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-teams-wrapper .team-job' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Icon Social.
        $this->start_controls_section(
            'section_style_icon_social',
            [
                'label' => esc_html__('Icon Social', 'bookory'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('tabs_icon_social_style');

        $this->start_controls_tab(
            'tab_icon_social_normal',
            [
                'label' => esc_html__('Normal', 'bookory'),
            ]
        );

        $this->add_control(
            'color_icon_social',
            [
                'label' => esc_html__('Color', 'bookory'),
                'type' => Controls_Manager::COLOR,
                'default' => '',

                'selectors' => [
                    '{{WRAPPER}} .elementor-teams-wrapper .team-icon-socials li.social a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-teams-wrapper .team-icon-socials a' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_icon_social_hover',
            [
                'label' => esc_html__('Hover', 'bookory'),
            ]
        );

        $this->add_control(
            'color_icon_social_hover',
            [
                'label' => esc_html__('Color', 'bookory'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-teams-wrapper .team-icon-socials li.social a:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-teams-wrapper .team-icon-socials a:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->end_controls_section();

    }

    /**
     * Render testimonial widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $this->add_render_attribute('wrapper', 'class', 'elementor-teams-wrapper');
        $this->add_render_attribute('wrapper', 'class', 'layout-' . esc_attr($settings['team_layout']));

        $team_name_html = $settings['name'];
        if (!empty($settings['link']['url'])) :
            $team_name_html = '<a href="' . esc_url($settings['link']['url']) . '">' . $team_name_html . '</a>';
        endif; ?>

        <div <?php echo bookory_elementor_get_render_attribute_string('wrapper', $this); ?>>

            <?php if ($settings['team_layout'] == '1'): ?>

                <?php if (!empty($settings['image']['url'])) : ?>
                    <div class="team-image">
                        <?php echo Group_Control_Image_Size::get_attachment_image_html($settings, 'thumbnail', 'image'); ?>
                    </div>
                <?php endif; ?>
                <?php $this->render_social($settings); ?>
                <div class="team-caption">
                    <div class="team-name"><?php printf('%s', $team_name_html) ?></div>
                    <div class="team-job"><?php echo esc_html($settings['job']); ?></div>
                </div>

            <?php endif; ?>

            <?php if ($settings['team_layout'] == '2'): ?>

                <div class="team-header">
                    <?php if (!empty($settings['image']['url'])) : ?>
                        <div class="team-image">
                            <?php echo Group_Control_Image_Size::get_attachment_image_html($settings, 'thumbnail', 'image'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="team-caption">
                        <div class="team-phone"><?php echo esc_html($settings['phone']); ?></div>
                        <div class="team-email"><?php echo esc_html($settings['email']); ?></div>
                        <?php $this->render_social($settings); ?>
                    </div>

                </div>
                <div class="team-name"><?php printf('%s', $team_name_html) ?></div>
                <div class="team-job"><?php echo esc_html($settings['job']); ?></div>


            <?php endif; ?>
        </div>
        <?php

    }

    protected function render_social($settings)
    {
        ?>
        <div class="team-icon-socials">
            <ul>
                <?php
                if (!empty($settings['socials']) && is_array($settings['socials'])) {
                    foreach ($settings['socials'] as $social): ?>
                        <li class="social">
                            <a href="<?php echo esc_url($social['social_link']) ?>">
                                <?php
                                \Elementor\Icons_Manager::render_icon($social['social_icon'], ['aria-hidden' => 'true']);
                                ?>
                            </a>
                        </li>
                    <?php endforeach;
                } ?>
            </ul>
        </div>

        <?php

    }

}

$widgets_manager->register(new Bookory_Elementor_Team_Box());
