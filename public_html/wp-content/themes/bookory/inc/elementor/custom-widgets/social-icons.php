<?php
use Elementor\Controls_Manager;

add_action( 'elementor/element/social-icons/section_social_style/before_section_end', function ($element, $args ) {

	$element->add_control(
		'icon_social_margin',
		[
			'label' => esc_html__( 'Margin', 'bookory' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'range' => '',
			'size_units' => [ 'px', 'em', '%' ],
			'selectors' => [
				'{{WRAPPER}} .elementor-grid-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]
	);

}, 10, 2 );
