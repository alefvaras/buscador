<?php
use Elementor\Controls_Manager;

add_action( 'elementor/element/icon/section_style_icon/after_section_end', function ($element, $args ) {

    $element->update_control(
        'icon_padding',
        [
            'type'       => Controls_Manager::DIMENSIONS,
            'range' => '',
            'size_units' => [ 'px', 'em', '%' ],
            'selectors' => [
                '{{WRAPPER}} .elementor-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

}, 10, 2 );

add_action( 'elementor/element/icon/section_style_icon/before_section_end', function ( $element, $args ) {
	$element->add_control(
		'icon_height',
		[
			'label' => esc_html__( 'Line Height', 'bookory' ),
			'type' => Controls_Manager::SLIDER,
			'selectors' => [
				'{{WRAPPER}} .elementor-icon-wrapper' => 'line-height: {{SIZE}}{{UNIT}};',
			],
		]
	);
}, 10, 2 );
