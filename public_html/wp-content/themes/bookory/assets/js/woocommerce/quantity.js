(function($) {
    'use strict';
    
    function bookoryQuantity() {
        const $quantityBoxes = $('.quantity:not(.buttons_added):not(.hidden)').find('.qty');
        
        if (!$quantityBoxes.length || $quantityBoxes.prop('type') === 'date') {
            return;
        }
        
        // Add plus/minus buttons
        $quantityBoxes
            .parent()
            .addClass('buttons_added')
            .prepend('<button type="button" class="minus"><i class="bookory-icon-minus"></i></button>')
            .find('.qty')
            .addClass('input-text')
            .after('<button type="button" class="plus"><i class="bookory-icon-plus"></i></button>');
        
        // Validate minimum quantity on non-product pages
        $('input.qty:not(.product-quantity input.qty)').each(function() {
            const $min = parseFloat($(this).attr('min'));
            if ($min && $min > 0 && parseFloat($(this).val()) < $min) {
                $(this).val($min);
            }
        });
        
        // Handle button clicks
        $('.quantity').off('click', '.plus, .minus').on('click', '.plus, .minus', function() {
            const $button = $(this);
            const $quantityBox = $button.parent().find('.qty');
            
            // Get current values
            let currentQuantity = parseFloat($quantityBox.val()) || 0;
            const maxQuantity = parseFloat($quantityBox.attr('max')) || Infinity;
            const minQuantity = parseFloat($quantityBox.attr('min')) || 0;
            const step = parseFloat($quantityBox.attr('step')) || 1;
            
            // Calculate new quantity
            let newQuantity;
            if ($button.hasClass('plus')) {
                newQuantity = Math.min(currentQuantity + step, maxQuantity);
            } else {
                newQuantity = Math.max(currentQuantity - step, minQuantity);
            }
            
            // Update value if changed
            if (newQuantity !== currentQuantity) {
                $quantityBox.val(newQuantity).trigger('change');
            }
        });
    }
    
    // Initialize on document ready
    $(document).ready(bookoryQuantity);
    
    // Re-initialize on dynamic content
    $(document).on('woosq_loaded qv_loader_stop updated_wc_div bookory-products-loaded', bookoryQuantity);
    
}(jQuery));