(function ($) {
    'use strict';
    var $body = $('body');

    function singleProductGalleryImages() {
        var rtl = $body.hasClass('rtl') ? true : false;

        var lightbox = $('.single-product .woocommerce-product-gallery__image > a');
        var thumb_signle = $('.flex-control-thumbs img', '.woocommerce-product-gallery');

        if (lightbox.length) {
            lightbox.attr("data-elementor-open-lightbox", "no");
        }

        if ($('.flex-control-thumbs', '.woocommerce-product-gallery').children().length > 4) {
            $('.woocommerce-product-gallery.woocommerce-product-gallery-horizontal .flex-control-thumbs').css({
                display: "block",
                "padding-right": 30,
            }).slick({
                rtl: rtl,
                infinite: false,
                slidesToShow: 4,
            });
            $('.woocommerce-product-gallery.woocommerce-product-gallery-vertical .flex-control-thumbs').slick({
                rtl: rtl,
                infinite: false,
                slidesToShow: 4,
                vertical: true,
                verticalSwiping: true,
            });

        }
        var $width = $('.woocommerce-product-gallery.woocommerce-product-gallery-vertical .flex-control-thumbs').outerWidth();
        if($width){
            $('.product-video-360').css('left',$width + 40);
        }
        $('.woocommerce-product-gallery .flex-control-thumbs li').each(function () {
            $(this).has('.flex-active').addClass('active');
        });
        thumb_signle.click(function () {
            thumb_signle.parent().removeClass('active');
            $(this).parent().addClass('active');
        });


        var $gallery_style = $('.woocommerce-product-gallery-gallery .woocommerce-product-gallery__wrapper');
        if ($gallery_style.length) {
            $gallery_style.slick({
                rtl: rtl,
                infinite: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
                dots: true,
                adaptiveHeight: true,
            })
        }


    }

    function popup_video() {
        $('a.btn-video').magnificPopup({
            type: 'iframe',
            disableOn: 700,
            removalDelay: 160,
            midClick: true,
            closeBtnInside: true,
            preloader: false,
            fixedContentPos: false
        });

        $('a.btn-360').magnificPopup({
            type: 'inline',

            fixedContentPos: false,
            fixedBgPos: true,

            overflowY: 'auto',

            closeBtnInside: true,
            preloader: false,

            midClick: true,
            removalDelay: 300,
            mainClass: 'my-mfp-zoom-in',
            callbacks: {
                open: function() {
                    var spin = $('#rotateimages');
                    var images = spin.data('images');
                    var imagesarray = images.split(",");
                    var api;
                    spin.spritespin({
                        source: imagesarray,
                        width: 800,
                        height: 800,
                        sense: -1,
                        responsive: true,
                        animate: false,
                        onComplete: function () {
                            $(this).removeClass('opal-loading');
                        }
                    });

                    api = spin.spritespin("api");

                    $('.view-360-prev').click(function(){
                        api.stopAnimation();
                        api.prevFrame();
                    });

                    $('.view-360-next').click(function(){
                        api.stopAnimation();
                        api.nextFrame();
                    });

                }
            }
        });
    }

    function sizechart_popup() {

        $('.sizechart-button').on('click', function (e) {
            e.preventDefault();
            $('.sizechart-popup').toggleClass('active');
        });

        $('.sizechart-close,.sizechart-overlay').on('click', function (e) {
            e.preventDefault();
            $('.sizechart-popup').removeClass('active');
        });
    }

    $('.woocommerce-product-gallery').on('wc-product-gallery-after-init', function () {
        singleProductGalleryImages();
    });

    function onsale_gallery_vertical() {
        $('.woocommerce-product-gallery.woocommerce-product-gallery-vertical:not(:has(.flex-control-thumbs))').css('max-width', '690px').next().css('left', '10px');
    }

    function output_accordion(){
        $('.js-card-body.active').slideDown();
        /*   Produc Accordion   */
        $('.js-btn-accordion').on('click', function(){
            if( !$(this).hasClass('active') ){
                $('.js-btn-accordion').removeClass('active');
                $('.js-card-body').removeClass('active').slideUp();
            }
            $(this).toggleClass('active');
            var card_toggle = $(this).parent().find('.js-card-body');
            card_toggle.slideToggle();
            card_toggle.toggleClass('active');

            setTimeout(function () {
                $('.product-sticky-layout').trigger('sticky_kit:recalc');
            }, 1000);
        });
    }

    function _makeStickyKit() {
        var top_sticky = 20,
            $adminBar = $('#wpadminbar');

        if ($adminBar.length > 0) {
            top_sticky += $adminBar.height();
        }

        if (window.innerWidth < 992) {
            $('.product-sticky-layout').trigger('sticky_kit:detach');
        } else {
            $('.product-sticky-layout').stick_in_parent({
                offset_top: top_sticky
            });

        }
    }

    function productTogerther() {

        var $fbtProducts = $('.bookory-frequently-bought');

        if ($fbtProducts.length <= 0) {
            return;
        }
        var priceAt = $fbtProducts.find('.bookory-total-price .woocommerce-Price-amount'),
            $button = $fbtProducts.find('.bookory_add_to_cart_button'),
            totalPrice = parseFloat($fbtProducts.find('#bookory-data_price').data('price')),
            currency = $fbtProducts.data('currency'),
            thousand = $fbtProducts.data('thousand'),
            decimal = $fbtProducts.data('decimal'),
            price_decimals = $fbtProducts.data('price_decimals'),
            currency_pos = $fbtProducts.data('currency_pos');

        let formatNumber = function(number){
            let n = number;
            if (parseInt(price_decimals) > 0) {
                number = number.toFixed(price_decimals) + '';
                var x = number.split('.');
                var x1 = x[0],
                    x2 = x.length > 1 ? decimal + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + thousand + '$2');
                }

                n = x1 + x2
            }
            switch (currency_pos) {
                case 'left' :
                    return currency + n;
                    break;
                case 'right' :
                    return n + currency;
                    break;
                case 'left_space' :
                    return currency + ' ' + n;
                    break;
                case 'right_space' :
                    return n + ' ' + currency;
                    break;
            }
        }

        $fbtProducts.find('input[type=checkbox]').on('change', function () {
            let id = $(this).val();
            $(this).closest('label').toggleClass('uncheck');
            let currentPrice = parseFloat($(this).closest('label').data('price'));
            if ($(this).closest('label').hasClass('uncheck')) {
                $fbtProducts.find('#fbt-product-' + id).addClass('un-active');
                totalPrice -= currentPrice;

            } else {
                $fbtProducts.find('#fbt-product-' + id).removeClass('un-active');
                totalPrice += currentPrice;
            }

            let $product_ids = $fbtProducts.data('current-id');
            $fbtProducts.find('label.select-item').each(function () {
                if (!$(this).hasClass('uncheck')) {
                    $product_ids += ',' + $(this).find('input[type=checkbox]').val();
                }
            });

            $button.attr('value', $product_ids);

            priceAt.html(formatNumber(totalPrice));
        });

        // Add to cart ajax
        $fbtProducts.on('click', '.bookory_add_to_cart_button.ajax_add_to_cart', function () {
            var $singleBtn = $(this);
            $singleBtn.addClass('loading');

            var currentURL = window.location.href;

            $.ajax({
                url: bookoryAjax.ajaxurl,
                dataType: 'json',
                method: 'post',
                data: {
                    action: 'bookory_woocommerce_fbt_add_to_cart',
                    product_ids: $singleBtn.attr('value')
                },
                error: function () {
                    window.location = currentURL;
                },
                success: function (response) {
                    if (typeof wc_add_to_cart_params !== 'undefined') {
                        if (wc_add_to_cart_params.cart_redirect_after_add === 'yes') {
                            window.location = wc_add_to_cart_params.cart_url;
                            return;
                        }
                    }

                    $(document.body).trigger('updated_wc_div');
                    $(document.body).on('wc_fragments_refreshed', function () {
                        $singleBtn.removeClass('loading');
                    });
                    $('body').trigger('added_to_cart');

                }
            });

        });

    }

    $body.on('click', '.wc-tabs li a, ul.tabs li a', function (e) {
        e.preventDefault();
        var $tab = $(this);
        var $tabs_wrapper = $tab.closest('.wc-tabs-wrapper, .woocommerce-tabs');
        var $control = $tab.closest('li').attr('aria-controls');
        $tabs_wrapper.find('.resp-accordion').removeClass('active');
        $('.' + $control).addClass('active');

    }).on('click', 'h2.resp-accordion', function (e) {
        e.preventDefault();
        var $tab = $(this);
        var $tabs_wrapper = $tab.closest('.wc-tabs-wrapper, .woocommerce-tabs');
        var $tabs = $tabs_wrapper.find('.wc-tabs, ul.tabs');

        if ($tab.hasClass('active')) {
            return;
        }
        $tabs_wrapper.find('.resp-accordion').removeClass('active');
        $tab.addClass('active');
        $tabs.find('li').removeClass('active');
        $tabs.find($tab.data('control')).addClass('active');
        $tabs_wrapper.find('.wc-tab, .panel:not(.panel .panel)').hide(300);
        $tabs_wrapper.find($tab.attr('aria-controls')).show(300);

    });

    $(document).ready(function () {
        sizechart_popup();
        onsale_gallery_vertical();
        popup_video();
        output_accordion();
        productTogerther();

        if ($('.product-sticky-layout').length > 0) {
            _makeStickyKit();
            $(window).resize(function(){
                setTimeout(function () {
                    _makeStickyKit();
                }, 100);
            });
        }
    });

})(jQuery);
