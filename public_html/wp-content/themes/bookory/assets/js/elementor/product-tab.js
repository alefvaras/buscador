(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        elementorFrontend.hooks.addAction('frontend/element_ready/bookory-products-tabs.default', ($scope) => {

            let $tabs = $scope.find('.elementor-tabs');
            let $contents = $scope.find('.elementor-tabs-content-wrapper');
            $contents.find('.elementor-tab-content').hide();
            // Active tab
            $contents.find('.elementor-active').show();
            let $carousel = $('.woocommerce-carousel ul', $scope);
            let $carousel_setting = $('.elementor-tabs-content-wrapper', $scope);
            let data = $carousel_setting.data('settings');

            $tabs.find('.elementor-tab-title').on('click', function () {
                $tabs.find('.elementor-tab-title').removeClass('elementor-active');
                $contents.find('.elementor-tab-content').removeClass('elementor-active').hide();
                $(this).addClass('elementor-active');
                let id = $(this).attr('aria-controls');
                $contents.find('#' + id).addClass('elementor-active').show();
                $carousel.slick('refresh');
            });


            if (typeof data === 'undefined') {
                return;
            }

            $carousel.slick(
                {
                    dots: data.navigation === 'both' || data.navigation === 'dots' ? true : false,
                    arrows: data.navigation === 'both' || data.navigation === 'arrows' ? true : false,
                    infinite: data.loop,
                    speed: 300,
                    slidesToShow: parseInt(data.items),
                    autoplay: data.autoplay,
                    autoplaySpeed: parseInt(data.autoplayTimeout),
                    slidesToScroll: 1,
                    lazyLoad: 'ondemand',
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
        });
    });
})(jQuery);
