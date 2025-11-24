(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        elementorFrontend.hooks.addAction('frontend/element_ready/bookory-image-gallery.default', ($scope) => {
            let $iso = $scope.find('.isotope-grid');
            if ($iso) {
                let currentIsotope = $iso.isotope({filter: '*'});
                $scope.find('.elementor-galerry__filters li').on('click', function () {
                    $(this).parents('ul.elementor-galerry__filters').find('li.elementor-galerry__filter').removeClass('elementor-active');
                    $(this).addClass('elementor-active');
                    let selector = $(this).attr('data-filter');
                    currentIsotope.isotope({filter: selector});
                });
            }

            let $carousel = $('.bookory-carousel', $scope);
            if ($carousel.length > 0) {
                let data = $carousel.data('settings');
                $carousel.slick(
                    {
                        dots: data.navigation == 'both' || data.navigation == 'dots' ? true : false,
                        arrows: data.navigation == 'both' || data.navigation == 'arrows' ? true : false,
                        infinite: data.loop,
                        speed: 300,
                        slidesToShow: parseInt(data.items),
                        autoplay: data.autoplay,
                        autoplaySpeed: data.autoplaySpeed,
                        slidesToScroll: 1,
                        lazyLoad: 'ondemand',
                        rtl: data.rtl,
                        centerMode: true,
                        centerPadding: '0px',
                        variableWidth: true,
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

        });
    });

})(jQuery);
