<?php
use Elementor\Controls_Manager;

add_action('elementor/element/icon-box/section_style_content/before_section_end', function ($element, $args) {
	$element->add_control(
		'icon_box_title_hover',
		[
			'label'     => esc_html__('Color Title Hover', 'bookory'),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .elementor-icon-box-wrapper:hover .elementor-icon-box-content .elementor-icon-box-title' => 'color: {{VALUE}};',
			],
		]
	);
}, 10, 2);

add_action('elementor/element/icon-box/section_style_icon/before_section_end', function ($element, $args) {
    $element->add_control(
        'icon_box_border_1',
        [
            'label'     => esc_html__('Color Border', 'bookory'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => [
                '{{WRAPPER}}.elementor-view-framed .elementor-icon' => 'border-color: {{VALUE}};',
            ],
        ]
    );
}, 10, 2);
