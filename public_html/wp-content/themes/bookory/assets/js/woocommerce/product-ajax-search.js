(function($) {
    'use strict';
    
    function ajax_live_search() {
        const selectors = {
            parent: $('.woocommerce-product-search'),
            inputSearch: $('.ajax-search .woocommerce-product-search .search-field'),
            result: $('.ajax-search-result'),
            dropdown: $('.input-dropdown-inner'),
            dropdownLink: $('.input-dropdown-inner > a'),
            dropdownList: $('.input-dropdown-inner > .list-wrapper'),
            dropdownSelect: $('.input-dropdown-inner > select')
        };
        
        const template = wp.template('ajax-live-search-template');
        const ANIMATION_SPEED = 100;
        const MIN_SEARCH_LENGTH = 2;
        const DEBOUNCE_DELAY = 300;
        
        let searchTimeout = null;
        let currentAjaxRequest = null;
        
        if (!selectors.inputSearch.length) return;
        
        function closeAllDropdowns() {
            selectors.result.hide();
            selectors.dropdownList.slideUp(ANIMATION_SPEED);
            selectors.dropdown.removeClass('dd-shown');
        }
        
        function updateCategorySelection(value, label) {
            selectors.dropdownList.find('.current-item').removeClass('current-item');
            selectors.dropdown.find(`[data-val="${value}"]`).parent().addClass('current-item');
            selectors.dropdownLink.find('span').text(label);
            selectors.dropdownSelect.val(value).trigger('cat_selected');
        }
        
        function updateFirstItemVisibility(value) {
            selectors.dropdownList.find('ul:not(.children) > li:first-child').toggle(value != 0);
        }
        
        function performSearch(query) {
            const productCat = selectors.parent.find('select[name="product_cat"]').val() || '';
            
            currentAjaxRequest = $.ajax({
                url: bookoryAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookory_ajax_search_products',
                    query: query,
                    product_cat: productCat,
                    nonce: bookoryAjax.nonce || ''
                },
                beforeSend: () => selectors.parent.addClass('loading'),
                success: function(response) {
                    try {
                        const data = typeof response === 'string' ? $.parseJSON(response) : response;
                        selectors.result.empty();
                        
                        if (data && data.length > 0) {
                            $.each(data, (i, item) => {
                                if (item && item.url && item.value) {
                                    const sanitizedItem = {
                                        url: escapeHtml(item.url),
                                        title: escapeHtml(item.value),
                                        img: escapeHtml(item.img || ''),
                                        price: item.price || ''
                                    };
                                    selectors.result.append(template(sanitizedItem));
                                }
                            });
                            selectors.result.show();
                        } else {
                            selectors.result.hide();
                        }
                    } catch (error) {
                        console.error('Error parsing search results:', error);
                        selectors.result.hide();
                    }
                },
                error: (xhr, status, error) => status !== 'abort' && console.error('Search error:', error),
                complete: () => {
                    selectors.parent.removeClass('loading');
                    currentAjaxRequest = null;
                }
            });
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
            return text.toString().replace(/[&<>"']/g, m => map[m]);
        }
        
        // Event Handlers
        $('body').on('click', closeAllDropdowns);
        
        selectors.dropdownLink.on('click', function(e) {
            e.preventDefault();
            const isShown = selectors.dropdown.hasClass('dd-shown');
            selectors.dropdown.toggleClass('dd-shown', !isShown);
            selectors.dropdownList.slideToggle(ANIMATION_SPEED);
            selectors.result.hide();
            return false;
        });
        
        selectors.dropdownList.on('click', 'a', function(e) {
            e.preventDefault();
            const value = $(this).data('val');
            const label = $(this).text();
            updateCategorySelection(value, label);
            updateFirstItemVisibility(value);
            selectors.dropdownList.slideUp(ANIMATION_SPEED);
            selectors.dropdown.removeClass('dd-shown');
        });
        
        selectors.dropdownSelect.on('change', function() {
            const value = $(this).val();
            const label = $(this).find('option:selected').text();
            updateCategorySelection(value, label);
            updateFirstItemVisibility(value);
        });
        
        selectors.inputSearch
            .on('keyup', function() {
                const searchQuery = this.value.trim();
                
                if (searchTimeout) clearTimeout(searchTimeout);
                if (currentAjaxRequest) {
                    currentAjaxRequest.abort();
                    currentAjaxRequest = null;
                }
                
                if (searchQuery.length > MIN_SEARCH_LENGTH) {
                    searchTimeout = setTimeout(() => performSearch(searchQuery), DEBOUNCE_DELAY);
                } else {
                    selectors.result.hide().empty();
                    selectors.parent.removeClass('loading');
                }
            })
            .on('click', e => e.stopPropagation())
            .on('focus', function() {
                selectors.dropdownList.slideUp(ANIMATION_SPEED);
                selectors.dropdown.removeClass('dd-shown');
                const searchQuery = this.value.trim();
                if (searchQuery.length > MIN_SEARCH_LENGTH) {
                    selectors.result.show();
                }
            });
    }
    
    $(document).ready(ajax_live_search);
}(jQuery));