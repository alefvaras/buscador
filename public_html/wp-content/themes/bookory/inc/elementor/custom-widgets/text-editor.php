<?php
// Text editor
use Elementor\Controls_Manager;
use Elementor\Group_Control_Text_Shadow;

add_action( 'elementor/element/text-editor/section_style/before_section_end', function ($element, $args ) {
    /** @var \Elementor\Element_Base $element */
    $element->add_group_control(
        Group_Control_Text_Shadow::get_type(),
        [
            'name' => 'texteditor_shadow',
            'selector' => '{{WRAPPER}}',
        ]
    );

}, 10, 2 );

add_action('elementor/element/text-editor/section_style/before_section_end', function ($element, $args) {
    /** @var \Elementor\Element_Base $element */
    $element->add_control(
        'text_color_link',
        [
            'label'     => esc_html__('Text Color Link', 'bookory'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => [
                '{{WRAPPER}} a:not(:hover)' => 'color: {{VALUE}};',
            ],
        ]
    );

}, 10, 2);
