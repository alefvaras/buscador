(function($) {
    'use strict';
    
    const $body = $('body');
    let xhr = null;
    
    // ============================================
    // 1. QUANTITY HANDLERS
    // ============================================
    
    function initQuantityHandlers(parentSelector) {
        const $parent = $(parentSelector);
        
        // Prevent direct input clicks
        $parent.on('click', '.quantity input', () => false);
        
        // Update cart button data-quantity
        $parent.on('change input', '.quantity .qty', function() {
            const $addToCartBtn = $(this).closest('.product, .product-list').find('.add_to_cart_button');
            $addToCartBtn.attr('data-quantity', $(this).val());
        });
        
        // Handle Enter key
        $parent.on('keypress', '.quantity .qty', function(e) {
            if (e.which === 13 || e.keyCode === 13) {
                $(this).closest('.product, .product-list').find('.add_to_cart_button').trigger('click');
            }
        });
    }
    
    // ============================================
    // 2. TOOLTIPS
    // ============================================
    
    function initTooltips() {
        const selectors = [
            '.product-list .product-caption .woosw-btn:not(.tooltipstered)',
            '.product-list .product-caption .woosq-btn:not(.tooltipstered)',
            '.product-list .product-caption .woosc-btn:not(.tooltipstered)'
        ].join(', ');
        
        $body.on('mouseenter', selectors, function() {
            if (typeof $.fn.tooltipster === 'undefined') return;
            
            $(this).tooltipster({
                position: 'top',
                functionBefore: (instance) => instance.content(instance._$origin.text()),
                theme: 'opal-product-tooltipster',
                delay: 0,
                animation: 'grow'
            }).tooltipster('show');
        });
    }
    
    // ============================================
    // 3. PRODUCT HOVER IMAGE SWAP
    // ============================================
    
    function initProductImageSwap() {
        $body.on('click', '.product-block .product-color .item', function() {
            const imageData = $(this).data('image');
            if (!imageData) return;
            
            const $img = $(this).closest('.product-block').find('.product-image img');
            $img.attr({
                src: imageData.src,
                srcset: imageData.srcset,
                sizes: imageData.sizes
            });
            
            // Update active state
            if (!$(this).hasClass('active-swatch')) {
                $(this).siblings('.active-swatch').removeClass('active-swatch');
                $(this).addClass('active-swatch');
            }
        });
    }
    
    // ============================================
    // 4. WISHLIST COUNT UPDATE
    // ============================================
    
    function initWishlistCount() {
        const $counters = $('.header-wishlist .count, .footer-wishlist .count, .header-wishlist .wishlist-count-item');
        
        // YITH Wishlist
        $(document).on('added_to_wishlist removed_from_wishlist', function() {
            if (typeof yith_wcwl_l10n === 'undefined') return;
            
            $.ajax({
                url: yith_wcwl_l10n.ajax_url,
                data: { action: 'yith_wcwl_update_wishlist_count' },
                dataType: 'json',
                success: (data) => {
                    $counters.html(data.count);
                    $('.wishlist-count-text').html(data.text);
                }
            });
        });
        
        // WPC Smart Wishlist
        $body.on('woosw_change_count', function(event, count) {
            if (typeof woosw_vars === 'undefined') return;
            
            $counters.html(count);
            $.ajax({
                url: woosw_vars.ajax_url,
                data: { action: 'woosw_ajax_update_count' },
                dataType: 'json',
                success: (data) => $('.wishlist-count-text').html(data.text)
            });
        });
    }
    
    // ============================================
    // 5. PRODUCT CATEGORIES WIDGET
    // ============================================
    
    function initCategoriesWidget() {
        const $widget = $('.widget_product_categories');
        const $mainUl = $widget.find('ul');
        
        if (!$mainUl.length) return;
        
        $mainUl.find('li').each(function() {
            const $li = $(this);
            const $link = $li.find('> a');
            const $children = $li.find('> ul.children');
            
            if (!$children.length) return;
            
            // Set initial state
            const isOpened = $li.hasClass('opened') || !$li.hasClass('closed');
            const iconClass = isOpened ? 'icon-minus' : 'icon-plus';
            
            $link.before(`<i class="${iconClass}"></i>`);
            
            if (!isOpened) {
                $children.hide();
                $li.addClass('closed');
            } else {
                $li.addClass('opened');
            }
            
            // Toggle handler
            const toggle = (e) => {
                if ($(e.target).is('a')) return;
                
                $children.slideToggle('slow');
                const nowOpened = $li.hasClass('closed');
                
                $li.toggleClass('opened closed');
                $li.find('> i').toggleClass('icon-plus icon-minus');
                
                e.stopImmediatePropagation();
            };
            
            $li.find('> i').on('click', toggle);
            $li.on('click', toggle);
        });
    }
    
    // ============================================
    // 6. CROSS-SELLS CAROUSEL
    // ============================================
    
    function initCrossSellsCarousel() {
        const $carousel = $('body.woocommerce-cart .cross-sells ul.products');
        const itemCount = $carousel.find('li.product').length;
        
        if (itemCount <= 3) return;
        
        $carousel.slick({
            dots: true,
            arrows: false,
            infinite: false,
            speed: 300,
            slidesToShow: 3,
            autoplay: false,
            slidesToScroll: 1,
            lazyLoad: 'ondemand',
            responsive: [
                { breakpoint: 1024, settings: { slidesToShow: 3 } },
                { breakpoint: 768, settings: { slidesToShow: 1 } }
            ]
        });
    }
    
    // ============================================
    // 7. AJAX ADD TO CART
    // ============================================
    
    function initAjaxAddToCart() {
        let localXhr = null;
        
        $(document).on('submit', 'form.cart', function(e) {
            const $product = $(this).closest('.product');
            
            // Skip for external/special products
            if ($product.hasClass('product-type-external') || $product.hasClass('product-type-zakeke')) {
                return;
            }
            
            e.preventDefault();
            
            const $form = $(this);
            const $button = $form.find('.single_add_to_cart_button');
            
            $button.addClass('loading');
            
            // Prepare form data
            const formData = new FormData($form[0]);
            formData.append('add-to-cart', $form.find('[name=add-to-cart]').val());
            formData.delete('woosq-redirect');
            
            // Cancel previous request
            if (localXhr) {
                localXhr.abort();
            }
            
            // Execute AJAX
            localXhr = $.ajax({
                url: wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', 'bookory_add_to_cart'),
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                complete: function(response) {
                    $button.removeClass('loading');
                    
                    // Redirect if configured
                    if (wc_add_to_cart_params.cart_redirect_after_add === 'yes') {
                        window.location = wc_add_to_cart_params.cart_url;
                        return;
                    }
                    
                    const data = response.responseJSON;
                    if (!data) return;
                    
                    // Handle errors
                    if (data.error && data.product_url) {
                        window.location = data.product_url;
                        return;
                    }
                    
                    // Update notices
                    $('.woocommerce-error, .woocommerce-message, .woocommerce-info').remove();
                    
                    if (data.fragments && data.fragments.notices_html) {
                        if (data.fragments.notices_html.indexOf('woocommerce-error') > 0) {
                            $('.single-product .site-content .col-full > .woocommerce').append(data.fragments.notices_html);
                        } else {
                            $(document.body).trigger('added_to_cart', [data.fragments, data.cart_hash, $button]);
                        }
                    }
                    
                    $form.unblock();
                    localXhr = null;
                }
            });
        });
    }
    
    // ============================================
    // 8. AJAX PRODUCT FILTERING
    // ============================================
    
    function sendProductRequest(url) {
        if (xhr) {
            xhr.abort();
        }
        
        xhr = $.ajax({
            type: 'GET',
            url: url,
            beforeSend: () => $('ul.bookory-products').addClass('preloader'),
            success: (data) => {
                const $html = $(data);
                const selectors = ['ul.bookory-products', '.woocommerce-pagination', '.woocommerce-result-count'];
                
                selectors.forEach(selector => {
                    $(`#main ${selector}`).replaceWith($html.find(`#main ${selector}`));
                });
                
                window.history.pushState(null, null, url);
                xhr = null;
                $(document).trigger('bookory-products-loaded');
            }
        });
    }
    
    $body.on('change', '.bookory-products-per-page #per_page', function(e) {
        e.preventDefault();
        sendProductRequest(this.value);
    });
    
    // ============================================
    // 9. PRODUCT HOVER RECALCULATION
    // ============================================
    
    function recalculateProductHover() {
        $('.product-block').each(function() {
            const bottomHeight = $('.product-caption-bottom', this).outerHeight();
            $('.content-product-imagin', this).css({ marginBottom: -bottomHeight });
        });
    }
    
    // ============================================
    // INITIALIZATION
    // ============================================
    
    $(document).ready(function() {
        initQuantityHandlers('.products');
        initQuantityHandlers('.products-list');
        initProductImageSwap();
        initCategoriesWidget();
        initTooltips();
        initWishlistCount();
        recalculateProductHover();
        initAjaxAddToCart();
        initCrossSellsCarousel();
    });
    
}(jQuery));