/* global yithWcbep, yithBulk */

jQuery( $ => {
	const block      = element => {
			  const blockArgs = {
				  message   : '',
				  overlayCSS: {backgroundColor: '#FFFFFF', opacity: 0.8, cursor: 'wait'},
			  };
			  element.block( blockArgs );
		  },
		  unblock    = element => element.unblock(),
		  addProduct = {
			  modal                        : false,
			  selectors                    : {
				  textEditors       : 'textarea.yith-wcbep-add-product__textarea-editor',
				  addProductButtons : 'button.yith-wcbep-products-table-add-product, button.yith-wcbep-empty-state-add-product',
				  cancelButton      : 'button.yith-wcbep-add-product-modal-button-cancel',
				  saveButton        : 'button.yith-wcbep-add-product-modal-button-save',
				  salePriceSchedule : 'span.yith-wcbep-add-product__sale-price-schedule',
				  productImageButton: '.yith-wcbep-add-product-field-image input.yith-plugin-fw-upload-img-url',
				  salePriceInput    : 'input#yith-wcbep-add-product-sale_price-field',
			  },
			  init                         : function () {
				  $( document ).on( 'click', addProduct.selectors.addProductButtons, addProduct.openModal );
				  $( document ).on( 'click', addProduct.selectors.cancelButton, addProduct.closeModal );
				  $( document ).on( 'click', addProduct.selectors.saveButton, addProduct.createProduct );
				  $( document ).on( 'click', addProduct.selectors.salePriceSchedule, addProduct.handleSchedulePriceVisibility );
				  $( document ).on( 'change', addProduct.selectors.productImageButton, addProduct.handleProductImageChange );
				  $( document ).on( 'blur', addProduct.selectors.salePriceInput, addProduct.handleSalePriceChange );
			  },
			  openModal                    : function () {
				  addProduct.modal = yith.ui.modal( {...yithWcbep.modals.addProduct, onClose: addProduct.handleModalClosing} );

				  addProduct.modal.elements.content.find( addProduct.selectors.textEditors ).each( function () {
					  const id = 'yith-wcbep-textarea-editor-' + Date.now();
					  $( this ).attr( 'id', id );
					  wp.editor.initialize( id, yithBulk.wpEditorDefaultOptions );
				  } );

				  const $uploadContainer = $( '.yith-wcbep-add-product-field' ),
						uploaderID       = $uploadContainer.find( 'input.yith-plugin-fw-upload-img-url' ).attr( 'id' );
				  $uploadContainer.find( '*[id^="' + uploaderID + '"]' ).each( (index, el) => {
					  const $el = $( el );
					  $el.attr( 'id', 'yith-plugin-fw-field__' + Date.now() + $el.attr( 'id' ).substring( 22 ) );
				  } );

				  $( document ).trigger( 'yith_fields_init' );
			  },
			  closeModal                   : () => {
				  if ( addProduct?.modal ) {
					  const modal      = addProduct.modal;
					  addProduct.modal = false;
					  modal.close();
				  }
			  },
			  handleModalClosing           : () => {
				  if ( addProduct?.modal ) {
					  if ( addProduct.modal.elements.main.find( '.blockUI' ).length ) {
						  block( $( '#yith-wcbep-products-wp-list' ) );
					  }
					  addProduct.modal = false;
				  }
			  },
			  handleProductImageChange     : function () {
				  const $input     = $( this ),
						$container = $input.closest( '.yith-plugin-fw-upload-container ' );
				  $container.toggleClass( 'yith-wcbep-add-product-image--uploaded', !! $input.val() );
			  },
			  handleSalePriceChange        : function () {
				  const $input        = $( this ),
						$regularPrice = $input.closest( '.yith-wcbep-add-product-modal-content-wrapper' ).find( 'input#yith-wcbep-add-product-regular_price-field' );
				  if ( 'function' === typeof priceStringToNumber && priceStringToNumber( $regularPrice.val() ) <= priceStringToNumber( $input.val() ) ) {
					  $input.val( '' );
				  }
			  },
			  handleSchedulePriceVisibility: function () {
				  $( '.yith-wcbep-add-product-field-schedule_from-wrapper, .yith-wcbep-add-product-field-schedule_to-wrapper' ).each( function () {
					  const $option = $( this );
					  if ( $option.is( ':visible' ) ) {
						  $option.fadeOut();
						  $option.find( 'input' ).val( '' );
					  } else {
						  $option.css( 'display', 'flex' ).hide().fadeIn();
					  }
				  } );
			  },
			  createProduct                : function () {
				  var $modalContainer = addProduct.modal.elements.main,
					  $modal          = addProduct.modal.elements.content,
					  product_options = {
						  name             : $modal.find( 'input#yith-wcbep-add-product-title-field' ).val(),
						  description      : wp.editor.getContent( $modal.find( '.yith-wcbep-add-product-field-description textarea' ).attr( 'id' ) ),
						  short_description: wp.editor.getContent( $modal.find( '.yith-wcbep-add-product-field-short_description textarea' ).attr( 'id' ) ),
						  regular_price    : $modal.find( '#yith-wcbep-add-product-regular_price-field' ).val(),
						  sale_price       : $modal.find( '#yith-wcbep-add-product-sale_price-field' ).val(),
						  image_url        : $modal.find( '.yith-wcbep-add-product-field-image input.yith-plugin-fw-upload-img-url' ).val(),
						  category_ids     : $modal.find( '#yith-wcbep-add-product-categories-field' ).val(),
						  status           : $modal.find( '#yith-wcbep-add-product-status-field' ).val(),
						  date_on_sale_from: $modal.find( '#yith-wcbep-add-product-schedule_from-field' ).val(),
						  date_on_sale_to  : $modal.find( '#yith-wcbep-add-product-schedule_to-field' ).val(),
					  },
					  success         = false;

				  block( $modalContainer );
				  $( document ).trigger( 'yith-wcbep-update-progress-bar', {progress: 50} );

				  $.ajax( {
					  data    : {
						  product_options,
						  action  : yithWcbep.actions.createProduct,
						  security: yithWcbep.security.createProduct,
					  },
					  type    : 'POST',
					  dataType: 'json',
					  url     : yithWcbep.ajaxurl,
					  success : function (response) {
						  if ( 'success' === response?.success && response?.productID ) {
							  success = true;
							  $( document ).trigger( 'yith-wcbep-load-table', {newProductID: response?.productID, tableData: response?.tableData} );
						  }
					  },
					  complete: function () {
						  unblock( $( '#yith-wcbep-products-wp-list' ) );
						  success || $( document ).trigger( 'yith-wcbep-update-progress-bar', {progress: -1} );

						  if ( $modalContainer.closest( 'body' ).length ) {
							  unblock( addProduct.modal.elements.main );
							  if ( success ) {
								  addProduct.modal.elements.footer.slideUp();
								  addProduct.modal.elements.content.slideUp();
								  const $successfull = $( yithWcbep.modals.addProduct.successMessage );
								  addProduct.modal.elements.title.after( $successfull );
								  $successfull.slideDown();
								  setTimeout( () => $modalContainer.closest( 'body' ).length && addProduct.closeModal(), 6000 );
							  }
						  }
					  },
				  } );
			  },
		  };

	addProduct.init();
} );
