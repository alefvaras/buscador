jQuery( function ( $ ) {
	const initNumericSelect2 = $select => {
			  if ( !$select.length || !$select.hasClass( 'wc-enhanced-select' ) ) {
				  return;
			  }
			  const prependSymbols = function ( state ) {
				  if ( !state.id ) {
					  return state.text;
				  }
				  const symbols = {
					  greater     : '>',
					  less        : '<',
					  equal       : '=',
					  'greater-eq': '≥',
					  'less-eq'   : '≤'
				  };
				  return $( `<span class="yith-wcbep-select2-option"><span class="yith-wcbep-select2-option-symbol">${symbols[ state.id ]}</span>${state.text}</span>` );
			  };
			  $select.select2( {
				  minimumResultsForSearch: -1,
				  templateResult         : prependSymbols,
				  templateSelection      : prependSymbols
			  } ).removeClass( 'wc-enhanced-select' );
		  },
		  filterProducts     = {
			  init                      : () => {
				  $( document ).on( 'click', '#yith-wcbep-filter-products', filterProducts.openModal );
				  $( document ).on( 'click', '#yith-wcbep-filter-products-modal-button-apply-filters', filterProducts.getProducts );
				  $( document ).on( 'click', '#yith-wcbep-filter-products-modal-button-reset-filters', filterProducts.resetFilters );
				  $( document ).on( 'change', '#yith-wcbep-save-as-table-view', filterProducts.toggleVisibilityToViewName );
				  $( document ).on( 'change', 'select#yith-wcbep-filter-products-product-type-field', filterProducts.handleProductTypeChange );
				  $( document ).on( 'change keyup', 'input#yith-wcbep-new-table-view-name', filterProducts.checkViewNameValidity );
			  },
			  getModalContent           : () => {
				  return filterProducts.hasOwnProperty( 'modal' ) && filterProducts.modal ? filterProducts.modal.elements.content : false;
			  },
			  openModal                 : () => {
				  if ( filterProducts?.modal ) {
					  return;
				  }

				  filterProducts.modal = yith.ui.modal( {
					  ...yithWcbep.modals.filterProducts,
					  onClose: filterProducts.handleModalClose
				  } );
				  filterProducts.modal.elements.content.find( 'select.yith-wcbep-numeric-comparator-select' ).toArray().map( $ ).map( initNumericSelect2 );
				  $( document ).trigger( 'yith_fields_init' );
				  filterProducts.maybeInitFilters();
			  },
			  handleModalClose          : () => {
				  filterProducts.modal = null;
			  },
			  maybeInitFilters          : () => {
				  $modal      = filterProducts.getModalContent();
				  let filters = filterProducts.hasOwnProperty( 'filters' ) ? filterProducts.filters : yithWcbep.customFilters;
				  if ( filters ) {
					  Object.keys( filters ).forEach( key => {
						  const $field  = $modal.find( '.yith-wcbep-filter-products-field-' + key ),
								options = filters[ key ];
						  switch ( key ) {
							  case 'category':
							  case 'tag':
							  case 'shipping-class':
								  $field.find( 'select.yith-wcbep-filter-products-compare' ).val( options.condition ).trigger( 'change' );
								  const $taxonomySelect = $field.find( 'select.yith-wcbep-filter-products-value' );
								  Object.keys( options.taxonomies ).forEach( ID => {
									  $taxonomySelect.append( `<option value="${ID}" selected>${options.taxonomies[ ID ]}</option>` );
								  } );
								  break;
							  case 'product-type':
								  if ( options.hasOwnProperty( 'include_variations' ) && 'yes' === options.include_variations ) {
									  $field.find( '#yith-wcbep-filter-products-product-type-field-include-variation' ).prop( 'checked', true ).trigger( 'change' );
								  }
							  default:
								  if ( options.hasOwnProperty( 'compare' ) ) {
									  $field.find( 'select.yith-wcbep-filter-products-compare' ).val( options.compare ).trigger( 'change' );
								  }
								  if ( options.hasOwnProperty( 'value' ) ) {
									  $field.find( '.yith-wcbep-filter-products-value' ).val( options.value ).trigger( 'change' );
								  }
								  break;
						  }
					  } );
				  }
			  },
			  toggleVisibilityToViewName: function () {
				  const $onOff = $( this ),
						modal  = filterProducts.getModalContent();
				  if ( modal ) {
					  modal.find( '.yith-wcbep-new-table-view-name' ).css( 'display', 'yes' === $onOff.val() ? 'flex' : 'none' );
				  }
			  },
			  handleProductTypeChange   : function () {
				  const productType = $( this ).val(),
						modal       = filterProducts.getModalContent();
				  if ( modal ) {
					  modal.find( '.yith-wcbep-product-type__include-variations' ).css( 'display', yithBulk.inArray( productType, ['any', 'variable'] ) ? 'flex' : 'none' );
				  }
			  },
			  checkViewNameValidity     : function () {
				  const $input    = $( this ),
						isInvalid = !$input.val()?.trim();
				  $input.closest( '.yith-wcbep-new-table-view-name' ).toggleClass( 'yith-wcbep-new-table-view-name--invalid', isInvalid );

				  if ( isInvalid ) {
					  $input.parent().find( '.yith-wcbep-new-table-view-name__invalid-message' ).slideDown();
				  } else {
					  $input.parent().find( '.yith-wcbep-new-table-view-name__invalid-message' ).slideUp();
				  }
			  },
			  getFilters                : () => {
				  let filters  = {};
				  const $modal = filterProducts.getModalContent();
				  if ( $modal ) {
					  $modal.find( '.yith-wcbep-filter-products-field-wrapper' ).toArray().map( $ ).forEach( $fieldWrapper => {
						  const type  = $fieldWrapper.data( 'filter-type' ),
								ID    = $fieldWrapper.data( 'filter-id' );
						  let options = {};
						  switch ( type ) {
							  case 'decimal':
							  case 'price':
							  case 'text': {
								  const value = $fieldWrapper.find( 'input.yith-wcbep-filter-products-value' ).val();
								  if ( value ) {
									  options = {
										  compare: $fieldWrapper.find( 'select.yith-wcbep-filter-products-compare' ).val(),
										  value
									  };
								  }
							  }
								  break;
							  case 'select': {
								  const value = $fieldWrapper.find( 'select.yith-wcbep-filter-products-value' ).val();
								  if ( value ) {
									  options = { value };
								  }
							  }
								  break;
							  case 'terms': {
								  const $options = $fieldWrapper.find( 'select.yith-wcbep-filter-products-value option:selected' );
								  if ( $options.length ) {
									  let value = {};

									  $options.toArray().map( $ ).forEach( $option => value[ $option.val() ] = $option.text() );

									  options = {
										  condition : $fieldWrapper.find( 'select.yith-wcbep-filter-products-compare' ).val(),
										  taxonomies: value
									  };
								  }
							  }
								  break;
							  case 'product-type': {
								  const value = $fieldWrapper.find( 'select.yith-wcbep-filter-products-value' ).val();
								  options     = {
									  value,
									  include_variations: yithBulk.inArray( value, ['any', 'variable'] ) ? ( $fieldWrapper.find( '#yith-wcbep-filter-products-product-type-field-include-variation' ).is( ':checked' ) ? 'yes' : 'no' ) : 'no'
								  };
							  }
								  break;
						  }
						  if ( Object.keys( options ).length ) {
							  filters[ ID ] = options;
						  }
					  } );
				  }

				  return filters;
			  },
			  getProducts               : function () {
				  const filters          = filterProducts.getFilters(),
						$tableViewSelect = $( '#yith-wcbep-table-view' ),
						$modal           = filterProducts.getModalContent();
				  let $option            = false,
					  data               = {
						  filters,
						  update_table_view: 'yes',
						  successCallback  : response => {
							  if ( response && response?.isSuccessCallback ) {
								  filterProducts.filters = filters;

								  if ( response && response?.newTableView ) {
									  const $option = $tableViewSelect.find( 'option[value="custom-filters"]' );
									  if ( $option.length > 1 ) {
										  $option.not( ':last' ).remove();
									  }
									  $option.attr( 'value', response.newTableView.viewKey );
									  $option.html( response.newTableView.viewName );
									  $tableViewSelect.select2( 'destroy' );
									  $tableViewSelect.removeClass( 'enhanced' );
									  $( document ).trigger( 'yith_fields_init' );

									  const $tableViewsModal = $( '<div>' + yithWcbep.modals.tableViews.content + '</div>' );
									  $tableViewsModal.find( '.yith-wcbep-table-view-wrapper.yith-wcbep-table-view-create' ).before( wp.template( 'yith-wcbep-table-view' )( response.newTableView ) );
									  yithWcbep.modals.tableViews.content = $tableViewsModal.html();
								  }
							  }
						  }
					  };

				  if ( 'yes' === $modal.find( 'input#yith-wcbep-save-as-table-view' ).val() ) {
					  const $viewNameInput = $modal.find( 'input#yith-wcbep-new-table-view-name' );
					  data.viewName        = $viewNameInput.val();
					  if ( !data.viewName?.trim() ) {
						  $viewNameInput.trigger( 'keyup' );
						  return;
					  }
					  data.createNewView = 'yes';
					  $tableViewSelect.find( 'option:nth-last-child(1)' ).before( '<option value="custom-filters" selected>' + data.viewName + '</option>' );
				  } else {
					  const $customFiltersOption = $tableViewSelect.find( 'option[value="custom-filters"]' );
					  if ( !$customFiltersOption.length ) {
						  $tableViewSelect.prepend( '<option value="custom-filters" selected>' + yithWcbep.i18n.customFilters + '</option>' );
					  } else {
						  $tableViewSelect.val( 'custom-filters' ).trigger( 'change', { updateTable: false } );
					  }
				  }

				  if ( filterProducts?.modal ) {
					  filterProducts.modal.close();
				  }

				  $( document ).trigger( 'yith-wcbep-load-table', data );
			  },
			  resetFilters              : () => {
				  const $modal = filterProducts.getModalContent();
				  $modal.find( 'select, input' ).each( ( index, input ) => {
					  const $input = $( input );

					  if ( $input.is( 'input[type="text"], input[type="number"]' ) ) {
						  $input.val( '' );
					  } else if ( $input.is( 'input[type="checkbox"]' ) ) {
						  $input.prop( 'checked', false );
						  if ( $input.hasClass( 'on_off' ) ) {
							  $input.val( 'no' );
							  $input.removeClass( 'onoffchecked' );
						  }
					  } else if ( $input.is( 'select' ) ) {
						  if ( $input.is( 'select[multiple]' ) ) {
							  $input.val( null );
						  } else {
							  $input.prop( 'selectedIndex', 0 );
						  }
					  }
					  $input.trigger( 'change' );
				  } );
			  }
		  };

	filterProducts.init();
} );