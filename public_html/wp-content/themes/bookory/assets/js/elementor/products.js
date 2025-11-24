(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        elementorFrontend.hooks.addAction('frontend/element_ready/bookory-products.default', ($scope) => {
            let $carousel = $('.woocommerce-carousel', $scope);
            if ($carousel.length > 0) {
                let data = $carousel.data('settings'),
                    rtl = $('body').hasClass('rtl') ? true : false;
                if(data['carousel_center'] === true){
                    $('ul.products', $carousel).slick(
                        {
                            rtl: rtl,
                            dots: data.navigation == 'both' || data.navigation == 'dots' ? true : false,
                            arrows: data.navigation == 'both' || data.navigation == 'arrows' ? true : false,
                            infinite: data.loop,
                            speed: 300,
                            slidesToShow: parseInt(data.items),
                            autoplay: data.autoplay,
                            autoplaySpeed: parseInt(data.autoplayTimeout),
                            slidesToScroll: 1,
                            lazyLoad: 'ondemand',
                            centerMode: true,
                            centerPadding: '0px',
                            swipeToSlide: true,
                            initialSlide: parseInt(data.initialSlide),
                            touchThreshold:99,
                            responsive: [
                                {
                                    breakpoint: parseInt(data.breakpoint_laptop),
                                    settings: {
                                        slidesToShow: parseInt(data.items_laptop),
                                    }
                                },
                                {
                                    breakpoint: parseInt(data.breakpoint_tablet_extra),
                                    settings: {
                                        slidesToShow: parseInt(data.items_tablet_extra),
                                    }
                                },
                                {
                                    breakpoint: parseInt(data.breakpoint_tablet),
                                    settings: {
                                        slidesToShow: parseInt(data.items_tablet),
                                    }
                                },
                                {
                                    breakpoint: parseInt(data.breakpoint_mobile_extra),
                                    settings: {
                                        slidesToShow: parseInt(data.items_mobile_extra),
                                    }
                                },
                                {
                                    breakpoint: parseInt(data.breakpoint_mobile),
                                    settings: {
                                        slidesToShow: parseInt(data.items_mobile),
                                    }
                                }
                            ]
                        }
                    );
                }

                else if (data['carousel_center'] === false){

                    $('ul.products', $carousel).slick(
                        {
                            rtl: rtl,
                            dots: data.navigation == 'both' || data.navigation == 'dots' ? true : false,
                            arrows: data.navigation == 'both' || data.navigation == 'arrows' ? true : false,
                            infinite: data.loop,
                            speed: 300,
                            slidesToShow: parseInt(data.items),
                            autoplay: data.autoplay,
                            autoplaySpeed: parseInt(data.autoplayTimeout),
                            slidesToScroll: 1,
                            lazyLoad: 'ondemand',
                            swipeToSlide: true,
                            initialSlide: parseInt(data.initialSlide),
                            touchThreshold:99,
                            responsive: [
                                {
                                    breakpoint: parseInt(data.breakpoint_laptop),
                                    settings: {
                                        slidesToShow: parseInt(data.items_laptop),
                                    }
                                },
                                {
                                    breakpoint: parseInt(data.breakpoint_tablet_extra),
                                    settings: {
                                        slidesToShow: parseInt(data.items_tablet_extra),
                                    }
                                },
                                {
                                    breakpoint: parseInt(data.breakpoint_tablet),
                                    settings: {
                                        slidesToShow: parseInt(data.items_tablet),
                                    }
                                },
                                {
                                    breakpoint: parseInt(data.breakpoint_mobile_extra),
                                    settings: {
                                        slidesToShow: parseInt(data.items_mobile_extra),
                                    }
                                },
                                {
                                    breakpoint: parseInt(data.breakpoint_mobile),
                                    settings: {
                                        slidesToShow: parseInt(data.items_mobile),
                                    }
                                }
                            ]
                        }
                    );

                }
            }
        });
    });

})(jQuery);
