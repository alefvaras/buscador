jQuery(function ($) {
	"use strict";

	var $body = $('body'),
		apply_points = function ($form) {
			block($('#yith-par-message-reward-cart'));
			var data = {
				'ywpar_points_max': $form.find('input[name=ywpar_points_max]').val(),
				'ywpar_max_discount': $form.find('input[name=ywpar_max_discount]').val(),
				'ywpar_rate_method': $form.find('input[name=ywpar_rate_method]').val(),
				'ywpar_input_points_nonce': $form.find('#ywpar_input_points_nonce').val(),
				'ywpar_input_points': $form.find('#ywpar-points-max').val(),
				'ywpar_input_points_check': $form.find('#ywpar_input_points_check').val()
			}

			$.ajax({
				type: 'POST',
				url: yith_ywpar_general.wc_ajax_url.toString().replace('%%endpoint%%', 'ywpar_apply_points'),
				data: data,
				dataType: 'html',
				success: function (response) {
					console.log('ywpar_applied');
				},
				complete: function () {

					if ('cart' === yith_ywpar_general.cart_or_checkout) {
						$('.woocommerce-cart-form button[name=update_cart]').removeAttr("disabled");
						$('.woocommerce-cart-form button[name=update_cart]').click();
					} else {
						$('body').trigger('update_checkout');
					}

				}
			});
		},
		is_blocked = function ($node) {
			return $node.is('.processing') || $node.parents('.processing').length;
		},
		block = function ($node) {
			if (!is_blocked($node)) {
				$node.addClass('processing').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
			}
		},
		unblock = function ($node) {
			$node.removeClass('processing').unblock();
		}

	$(document).on('click', '.ywpar-button-message', function (e) {
		e.preventDefault();
		var $t = $(this);
		if ($t.hasClass('ywpar-button-percentage-discount')) {
			$t.next().find('form').submit();
		} else {
			$('.ywpar_apply_discounts_container').slideToggle();
		}

	});


	/* apply points */
	$(document).on('click', '#ywpar_apply_discounts', function (e) {
		e.preventDefault();
		var $t = $(this),
			maxPoint = $('#ywpar-points-max'),
			minVal = maxPoint.attr('min'),
			maxVal = maxPoint.val(),
			errorBox = $('.ywpar_min_reedem_value_error'),
			form = $t.parents('form');

		if (parseFloat(maxVal) < parseFloat(minVal)) {
			errorBox.show();
			return false;
		} else {
			errorBox.css('opacity', '0');
			$('#ywpar_input_points_check').val(1);

			if (yith_ywpar_general.redeem_uses_ajax) {
				apply_points(form);
			} else {
				form.submit();
			}
		}
	});

	/**
	 * Manage the points message for variations
	 *
	 * @param $form
	 */
	$.fn.yith_ywpar_variations = function ($form) {
		var $form = $('.variations_form:not(.in_loop )'),
			$point_message = $form.closest('.product').find('.yith-par-message'),
			$point_message_variation = $form.closest('.product').find('.yith-par-message-variation');

		if (!$point_message.length) {
			$point_message = $('.product').find('.yith-par-message');
			$point_message_variation = $('.product').find('.yith-par-message-variation');
		}

		if (!$point_message.length) {
			$point_message = $('.yith-par-message');
			$point_message_variation = $('.yith-par-message-variation');
		}

		var $points = $point_message_variation.find('.product_point'),
			$points_conversion = $point_message_variation.find('.product-point-conversion'); //variation_price_discount_fixed_conversion
			
		$form.on('found_variation', function (event, variation) {
			let qty = $('.single-product form.cart .qty').val();
			$point_message.addClass('hide');
			$point_message_variation.removeClass('hide');

			if (variation.variation_points == 0) {
				$point_message_variation.addClass('hide');
			}
			if (variation.variation_points) {
				$points.text(Math.floor(variation.variation_points) * qty);
				$points.data('basepoints', Math.floor(variation.variation_points) );
			}

			if (variation.variation_price_discount_fixed_conversion) {
				$points_conversion.html(variation.variation_price_discount_fixed_conversion);
				$points_conversion.data('baseworthprice', variation.display_price);
			}

			update_product_message( qty );
		});

		$form.on('reset_data', function () {
			$point_message_variation.addClass('hide');
			$point_message.removeClass('hide');
			update_product_message( $('.single-product form.cart .qty').val() );
		});

		$form.find('select').first().trigger('change');
	};

	if ($body.hasClass('single-product')) {
		$.fn.yith_ywpar_variations();
	}

	$(document).on('qv_loader_stop', function () {
		//if( $body.hasClass('single-product') ){
		$.fn.yith_ywpar_variations();
		//}
	});

	/**
	 * Refresh Points Messages after cart & checkout update
	 */
	$(document.body).on('updated_cart_totals added_to_cart updated_checkout update_shipping_method', function (e) {

		// cart messages
		if ('updated_checkout' === e.type || 'updated_cart_totals' === e.type ) {
			var $message_container = $('#yith-par-message-cart');
			if ($message_container.length > 0) {
				$.ajax({
					url: yith_ywpar_general.wc_ajax_url.toString().replace('%%endpoint%%', 'ywpar_update_cart_messages') + '&cart_or_checkout=' + yith_ywpar_general.cart_or_checkout,
					type: 'POST',
					success: function (res) {
						if ('' !== res) {
							$message_container.show().html(res);
						} else {
							$message_container.hide();
						}
					}
				});
			}
		} else {
			$('.woocommerce > #yith-par-message-cart').remove();
			var more = $('.woocommerce-notices-wrapper > #yith-par-message-cart');
			if (more.length > 1) {
				$('.woocommerce-notices-wrapper > #yith-par-message-cart:last-child').remove();
			}
		}

		if ('updated_checkout' === e.type || 'updated_cart_totals' === e.type ) {
			// cart rewards messages
			var $message_reward_container = $('#yith-par-message-reward-cart');

			if ($message_reward_container.length === 0) {
				var $coupon_form = $( yith_ywpar_general.default_container );
				$coupon_form.after('<div id="yith-par-message-reward-cart" style="display:none" class="woocommerce-cart-notice woocommerce-cart-notice-minimum-amount woocommerce-info"></div>');
			}

			if ($(document).find('#yith-par-message-reward-cart').length > 0) {
				$.ajax({
					url: yith_ywpar_general.wc_ajax_url.toString().replace('%%endpoint%%', 'ywpar_update_cart_rewards_messages'),
					type: 'POST',
					beforeSend: function () {
					},
					success: function (res) {
						let rm = $(document).find('#yith-par-message-reward-cart');
						rm.addClass(yith_ywpar_general.reward_message_type);
						if ('' !== res.trim()) {
							rm.show().html(res);
							ywpar_calc_discount_value();
						} else {
							rm.hide();
						}
					}
				});

			}
		} else {
			$('.woocommerce > #yith-par-message-reward-cart').remove();
			var more = $('.woocommerce-notices-wrapper > #yith-par-message-reward-cart');
			if (more.length > 1) {
				$('.woocommerce-notices-wrapper > #yith-par-message-reward-cart:last-child').remove();
			}
		}
	});

/** Calculate discount amount **/
	const ywpar_calc_discount_value = function () {
		var timer, delay = 500;
		$(document).on('keyup', '#ywpar-points-max', function (e) {
			clearTimeout(timer);
			var _this = $(this);
			timer = setTimeout( function() {
				let v = _this.val();

				if (v === '' || !$.isNumeric(v)) {
					v = 0;
				}

				let max_points = $('#yith-par-message-reward-cart input[name=ywpar_points_max], .wp-block-yith-par-message-reward-cart.default-layout input[name=ywpar_points_max]').val(),
					method = $('#yith-par-message-reward-cart input[name=ywpar_rate_method], .wp-block-yith-par-message-reward-cart.default-layout input[name=ywpar_rate_method]').val()
				var data = {
					'input_points': v,
					'max_points': max_points,
					'method': method,
					'security': yith_ywpar_general.discount_value_nonce
				}

				$.ajax({
					url: yith_ywpar_general.wc_ajax_url.toString().replace('%%endpoint%%', 'ywpar_calc_discount_value'),
					type: 'POST',
					data: data,
					dataType: 'json',
					beforeSend: function () {
						block($('#yith-par-message-reward-cart.default-layout .woocommerce-Price-amount, .wp-block-yith-par-message-reward-cart.default-layout .woocommerce-Price-amount'));
					},
					success: function (res) {
						if ('' !== res.to_redeem) {
							$('#yith-par-message-reward-cart .woocommerce-Price-amount, .wp-block-yith-par-message-reward-cart .woocommerce-Price-amount').html(res.to_redeem);
						}

					},
					complete: function () {
						unblock($('#yith-par-message-reward-cart.default-layout .woocommerce-Price-amount, .wp-block-yith-par-message-reward-cart'));
					}
				});

			}, delay );

		});

	};

	$(document).on('wc_fragments_refreshed', function () {
		ywpar_calc_discount_value();
	});

	ywpar_calc_discount_value();

	/* datepicker for birthday extra point */
	function start_date_picker() {
		let dformat = yith_ywpar_general.birthday_date_format.replace('yy', 'yyyy');
		var instance = new dtsel.DTS('input[name="yith_birthday"]', {
			dateFormat: dformat,
			localization:yith_ywpar_general.datepicker
		});
	}

	if ($('input[name="yith_birthday"]').length > 0) {
		start_date_picker();
	}


	/* ywpar tabs */
	function openTab(evt, obj, tab) {
		obj.find('.ywpar_tabcontent').each( function() { $(this).hide(); });
		obj.find('.ywpar_tabs_links').each( function() { $(this).removeClass('active'); });
		obj.find('#' + tab).fadeIn('slow');
		evt.currentTarget.className += " active";
	}


	$(document).on('click', '.ywpar_tabs_links', function(e){
		var selected = $(this).closest('#ywpar_tabs');
		openTab(e, selected, $(this).data('target') );
	});

	$(document).find( '.ywpar_tabs_links.active').each( function(){
		$(this).click();
	});

	$(document).on('change', '.single-product form.cart .qty', function(){
		update_product_message( $(this).val() );
	});
	$(document).on('ywdpd_handle_quantity_changed', function( e, qty ) {
		update_product_message( qty );
	})

	function update_product_message( qty ) {
		if ( 'no' === yith_ywpar_general.update_product_message ) { return; }
		var product_type       = $('.single-product .yith-par-message-variation').length ? 'variable' : 'simple',
		points_selector        = $('.single-product .yith-par-message-product .product_point'),
		base_points            = points_selector.data('basepoints'),
		worth_price_selector   = $('.single-product .yith-par-message-product .product-point-conversion'),
		base_worth_price       = worth_price_selector.data('baseworthprice'),
		price_selector         = worth_price_selector.find('.amount bdi'),
		currency               = price_selector.first().find( '.woocommerce-Price-currencySymbol').text(),
		currency_position	   = yith_ywpar_general.wc_currency_position,
		qty                    = Number(qty);
		
		//points
		if ( base_points ) {
			if ( base_points.toString().indexOf('-')>0 ) {
				//points
				let values = base_points.split('-');
				values.forEach((v, i)=> {
					values[i] = Number(v) * qty;
				});
				let new_values = values.join('-');
				points_selector.html( new_values );
			} else {
				points_selector.text( Number(base_points) * qty );
			}
		}
		//price
		if ( currency && base_worth_price ) {
			if ( base_worth_price.toString().indexOf('-')>0 ) {
				//range price
				let values = base_worth_price.split('-');
	
				values.forEach((v, i)=> {
					if ( currency_position === 'left' ) { 
						values[i] = '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currency + '</span>' + Number(v) * qty + '</bdi></span>';
					} else {
						values[i] = '<span class="woocommerce-Price-amount amount"><bdi>' + Number(v) * qty + '<span class="woocommerce-Price-currencySymbol">' + currency + '</span></bdi></span>';
					}
					
				});
				let new_values = values.join('-');
				worth_price_selector.html( new_values );
			} else {
				let new_worth = (Number(base_worth_price) * qty).toFixed(2);
				if ( currency_position === 'left' ) {
					price_selector.html( '<span class="woocommerce-Price-currencySymbol">' + currency + '</span>' + new_worth );
				} else {
					price_selector.html( new_worth + '<span class="woocommerce-Price-currencySymbol">' + currency + '</span>');
				}
				
			}

		}
		
		if ( 'variable' === product_type ) {

			//update single variation message
			let variation_message = $('.single-product .yith-par-message-variation'),
			var_points_selector   = variation_message.find('.product_point'),
			var_base_points       = var_points_selector.data('basepoints'),
			var_worth_price_selector  = $('.single-product .yith-par-message-variation .product-point-conversion'),
			var_base_worth_price      = var_worth_price_selector.data('baseworthprice'),
			var_price_selector        = var_worth_price_selector.find('.amount bdi');

			//main message with range
			if ( var_base_points.toString().indexOf('-')>0 ) {
				//points
				let values = base_points.split('-');
				values.forEach((v, i)=> {
					values[i] = Number(v) * qty;
				});
				let new_values = values.join('-');
				var_points_selector.html( new_values );
			} else {
				if ( var_base_points ) {
					//points_selector.text( Number(base_points) * qty );
					var_points_selector.text( Number(var_base_points) * qty);
				}
			}

			//price
			if ( var_base_worth_price !== undefined && var_base_worth_price.toString().indexOf('-')>0 ) {
				//range price
				let values = var_base_worth_price.split('-');
				values.forEach((v, i)=> {
					if ( currency_position === 'left' ) { 
						tmpprice = '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' + currency + '</span>' + Number(v) * qty + '</bdi></span>'
					} else {
						tmpprice = '<span class="woocommerce-Price-amount amount"><bdi>' + Number(v) * qty + '<span class="woocommerce-Price-currencySymbol">' + currency + '</span></bdi></span>'
					}
					values[i] = tmpprice;
				});
				let new_values = values.join('-');
				var_worth_price_selector.html( new_values );
			} else {
				//normal price
				if ( currency && var_base_worth_price ) {
					let new_worth = Number(var_base_worth_price) * qty;
					if ( currency_position === 'left' ) { 
						var_price_selector.html( '<span class="woocommerce-Price-currencySymbol">' + currency + '</span>' + new_worth );
					} else {
						var_price_selector.html( new_worth + '<span class="woocommerce-Price-currencySymbol">' + currency + '</span>' );
					}
				}
			}
		}
	}

});
