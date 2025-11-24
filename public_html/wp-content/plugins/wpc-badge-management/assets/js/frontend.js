(function($) {
  'use strict';

  $('.woocommerce-product-gallery').
      on('wc-product-gallery-after-init', function() {
        if ($('.wpcbm-wrapper-single-image .wpcbm-badges').length &&
            !$('.woocommerce-product-gallery .wpcbm-wrapper-single-image').length) {
          $('.wpcbm-wrapper-single-image').
              appendTo('.woocommerce-product-gallery');
          $('.woocommerce-product-gallery > *:not(.wpcbm-wrapper-single-image)').
              appendTo('.wpcbm-wrapper-single-image');
        }
      });
})(jQuery);
