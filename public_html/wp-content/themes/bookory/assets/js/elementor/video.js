(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        elementorFrontend.hooks.addAction('frontend/element_ready/bookory-video-popup.default', ($scope) => {
            $scope.find('.bookory-video-popup a.elementor-video-popup').magnificPopup({
                type: 'iframe',
                removalDelay: 500,
                midClick: true,
                closeBtnInside: true,
                callbacks: {
                    beforeOpen: function () {
                        this.st.mainClass = this.st.el.attr('data-effect');
                    }
                },
            });
        });
    });
})(jQuery);
