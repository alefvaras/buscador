(function($) {
    'use strict';

    class AdvancedSearch {
        constructor() {
            this.config = {
                minLength: 2,
                debounceDelay: 300,
                cacheTime: 300000,
                maxResults: 10
            };
            
            this.cache = new Map();
            this.currentRequest = null;
            this.debounceTimer = null;
            
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.createResultsContainer();
        }
        
        bindEvents() {
            $(document).on('input', '.search-field, input[type="search"]', (e) => {
                this.handleInput(e);
            });
            
            $(document).on('focus', '.search-field, input[type="search"]', (e) => {
                this.showResults($(e.target));
            });
            
            $(document).on('click', (e) => {
                if (!$(e.target).closest('.search-container, .search-results').length) {
                    this.hideAllResults();
                }
            });
            
            $(document).on('keydown', '.search-field, input[type="search"]', (e) => {
                this.handleKeyboard(e);
            });
        }
        
        createResultsContainer() {
            $('.search-field, input[type="search"]').each(function() {
                const $input = $(this);
                if (!$input.siblings('.search-results').length) {
                    $input.after('<div class="search-results" style="display:none;"></div>');
                }
            });
        }
        
        handleInput(e) {
            const $input = $(e.target);
            const query = $input.val().trim();
            
            clearTimeout(this.debounceTimer);
            
            if (query.length < this.config.minLength) {
                this.hideResults($input);
                return;
            }
            
            this.showLoader($input);
            
            this.debounceTimer = setTimeout(() => {
                this.performSearch(query, $input);
            }, this.config.debounceDelay);
        }
        
        performSearch(query, $input) {
            const cacheKey = this.getCacheKey(query);
            const cached = this.getFromCache(cacheKey);
            
            if (cached) {
                this.displayResults(cached, $input);
                return;
            }
            
            if (this.currentRequest) {
                this.currentRequest.abort();
            }
            
            const category = $input.closest('form').find('select[name="product_cat"]').val() || '';
            const postType = $input.data('post-type') || 'product';
            
            this.currentRequest = $.ajax({
                url: bookorySearch.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'bookory_advanced_search',
                    nonce: bookorySearch.nonce,
                    query: query,
                    category: category,
                    post_type: postType
                },
                success: (response) => {
                    if (response.success) {
                        this.saveToCache(cacheKey, response.data);
                        this.displayResults(response.data, $input);
                    } else {
                        this.showError($input, response.data.message);
                    }
                },
                error: (xhr, status) => {
                    if (status !== 'abort') {
                        this.showError($input, 'Error en la búsqueda');
                    }
                },
                complete: () => {
                    this.hideLoader($input);
                    this.currentRequest = null;
                }
            });
        }
        
        displayResults(data, $input) {
            const $results = $input.siblings('.search-results');
            $results.empty();
            
            if (!data.results || data.results.length === 0) {
                this.showNoResults($results, data.suggestions || []);
                return;
            }
            
            const $list = $('<ul class="search-results-list"></ul>');
            
            data.results.forEach((result, index) => {
                const $item = this.createResultItem(result, index);
                $list.append($item);
            });
            
            $results.append($list);
            
            if (data.total > data.results.length) {
                $results.append(
                    `<div class="search-results-footer">
                        <a href="${bookorySearch.homeurl}/?s=${encodeURIComponent($input.val())}">
                            Ver todos los ${data.total} resultados
                        </a>
                    </div>`
                );
            }
            
            this.showResults($input);
        }
        
        createResultItem(result, index) {
            const $item = $('<li class="search-result-item"></li>');
            $item.attr('data-index', index);
            
            let html = '<a href="' + result.url + '" class="search-result-link">';
            
            if (result.image) {
                html += '<div class="search-result-image">';
                html += '<img src="' + result.image + '" alt="' + this.escapeHtml(result.title) + '" loading="lazy">';
                html += '</div>';
            }
            
            html += '<div class="search-result-content">';
            html += '<h4 class="search-result-title">' + this.highlightQuery(result.title, result.query) + '</h4>';
            
            if (result.type === 'product') {
                if (result.price) {
                    html += '<div class="search-result-price">' + result.price + '</div>';
                }
                if (result.sku) {
                    html += '<div class="search-result-sku">SKU: ' + result.sku + '</div>';
                }
                if (result.stock === false) {
                    html += '<span class="search-result-stock out-of-stock">Agotado</span>';
                }
                if (result.rating && result.rating > 0) {
                    html += '<div class="search-result-rating">' + this.createStars(result.rating) + '</div>';
                }
            } else if (result.excerpt) {
                html += '<div class="search-result-excerpt">' + result.excerpt + '</div>';
            }
            
            html += '</div>';
            html += '</a>';
            
            $item.html(html);
            return $item;
        }
        
        showNoResults($results, suggestions) {
            let html = '<div class="search-no-results">';
            html += '<p>No se encontraron resultados</p>';
            
            if (suggestions.length > 0) {
                html += '<div class="search-suggestions">';
                html += '<p>¿Quisiste decir?</p>';
                html += '<ul>';
                suggestions.forEach(suggestion => {
                    html += '<li><a href="' + suggestion.url + '">' + suggestion.term + '</a></li>';
                });
                html += '</ul>';
                html += '</div>';
            }
            
            html += '</div>';
            $results.html(html).show();
        }
        
        handleKeyboard(e) {
            const $input = $(e.target);
            const $results = $input.siblings('.search-results');
            const $items = $results.find('.search-result-item');
            
            if ($items.length === 0) return;
            
            const $current = $items.filter('.active');
            let $next;
            
            switch(e.keyCode) {
                case 40: // Down
                    e.preventDefault();
                    if ($current.length === 0) {
                        $next = $items.first();
                    } else {
                        $next = $current.next();
                        if ($next.length === 0) $next = $items.first();
                    }
                    break;
                    
                case 38: // Up
                    e.preventDefault();
                    if ($current.length === 0) {
                        $next = $items.last();
                    } else {
                        $next = $current.prev();
                        if ($next.length === 0) $next = $items.last();
                    }
                    break;
                    
                case 13: // Enter
                    e.preventDefault();
                    if ($current.length > 0) {
                        $current.find('a')[0].click();
                    }
                    return;
                    
                case 27: // Escape
                    this.hideResults($input);
                    return;
            }
            
            if ($next) {
                $items.removeClass('active');
                $next.addClass('active');
                this.scrollToItem($next, $results);
            }
        }
        
        scrollToItem($item, $container) {
            const itemTop = $item.position().top;
            const itemBottom = itemTop + $item.outerHeight();
            const containerHeight = $container.height();
            const scrollTop = $container.scrollTop();
            
            if (itemBottom > containerHeight) {
                $container.scrollTop(scrollTop + itemBottom - containerHeight);
            } else if (itemTop < 0) {
                $container.scrollTop(scrollTop + itemTop);
            }
        }
        
        highlightQuery(text, query) {
            if (!query) return this.escapeHtml(text);
            
            const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
            return this.escapeHtml(text).replace(regex, '<mark>$1</mark>');
        }
        
        createStars(rating) {
            const fullStars = Math.floor(rating);
            const hasHalf = rating % 1 >= 0.5;
            let html = '';
            
            for (let i = 0; i < fullStars; i++) {
                html += '★';
            }
            if (hasHalf) {
                html += '⯨';
            }
            
            return html + ' (' + rating.toFixed(1) + ')';
        }
        
        showLoader($input) {
            const $results = $input.siblings('.search-results');
            $results.html('<div class="search-loading">Buscando...</div>').show();
        }
        
        hideLoader($input) {
            const $results = $input.siblings('.search-results');
            $results.find('.search-loading').remove();
        }
        
        showError($input, message) {
            const $results = $input.siblings('.search-results');
            $results.html('<div class="search-error">' + message + '</div>').show();
        }
        
        showResults($input) {
            const $results = $input.siblings('.search-results');
            if ($results.children().length > 0) {
                $results.show();
            }
        }
        
        hideResults($input) {
            $input.siblings('.search-results').hide();
        }
        
        hideAllResults() {
            $('.search-results').hide();
        }
        
        getCacheKey(query, category = '', postType = 'product') {
            return `${query}|${category}|${postType}`;
        }
        
        saveToCache(key, data) {
            this.cache.set(key, {
                data: data,
                timestamp: Date.now()
            });
            
            if (this.cache.size > 50) {
                const firstKey = this.cache.keys().next().value;
                this.cache.delete(firstKey);
            }
        }
        
        getFromCache(key) {
            const cached = this.cache.get(key);
            if (!cached) return null;
            
            if (Date.now() - cached.timestamp > this.config.cacheTime) {
                this.cache.delete(key);
                return null;
            }
            
            return cached.data;
        }
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    $(document).ready(function() {
        new AdvancedSearch();
    });

})(jQuery);