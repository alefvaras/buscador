jQuery( function ($) {
	const formatPrice              = (price, currency) => {
			  currency  = yithWcbepMultiCurrency.currencies[ currency ];
			  let int   = parseInt( price ),
				  float = price - int;
			  return int.toString().replace( /\B(?=(\d{3})+(?!\d))/g, currency.thousandSeparator ) + parseFloat( float ).toFixed( currency.decimals ).toString().replace( '.', currency.decimalSeparator ).substring( 1 );
		  },
		  multiCurrencyIntegration = {
			  init                              : () => {
				  wp.hooks.addFilter( 'yithWcbepGetFloatingField', 'yithWcbepIntegration', multiCurrencyIntegration.getFloatingField );
				  wp.hooks.addFilter( 'yithWcbepUpdateColumnValue', 'yithWcbepIntegration', multiCurrencyIntegration.updateColumnValue );
				  wp.hooks.addFilter( 'yithWcbepGetFloatingFieldValue', 'yithWcbepIntegration', multiCurrencyIntegration.getFloatingFieldValue );
				  wp.hooks.addFilter( 'yithWcbepBulkEditingFieldActionChangeOptions', 'yithWcbepIntegration', multiCurrencyIntegration.bulkEditingHandleFieldActionChange );
				  wp.hooks.addFilter( 'yithWcbepBulkEditingSaveChangesOptions', 'yithWcbepIntegration', multiCurrencyIntegration.bulkEditingSaveChangesOptions );
				  wp.hooks.addFilter( 'yithWcbepBulkEditingEditProductsValue', 'yithWcbepIntegration', multiCurrencyIntegration.bulkEditingEditProductsValue );

			  },
			  getFloatingField                  : ($field, options, value, template) => {
				  switch ( options.type ) {
					  case 'multi_currency_price':
						  $field = template( value );
						  break;
				  }
				  return $field;
			  },
			  updateColumnValue                 : ({value, display}, options, displayContainer) => {
				  if ( 'multi_currency_price' === options?.type ) {
					  value   = yithBulk.maybeParseJSON( value );
					  display = $( '<div></div>' ).html( displayContainer.html() );
					  display.find( '> div' ).hide();
					  Object.keys( value ).forEach( currency => {
						  const currencyContainer = display.find( '.yith-wcbep-multi-currency-price-' + currency );
						  currencyContainer.show().removeClass( 'hidden' );
						  currencyContainer.find( '.yith-wcbep-multi-currency-price-amount' ).html( formatPrice( priceStringToNumber( value[ currency ] ), currency ) );
					  } );
					  display = display.html();
					  value   = JSON.stringify( value );
				  }
				  return {value, display};
			  },
			  getFloatingFieldValue             : (value, editingInput, type) => {
				  if ( 'multi_currency_price' === type ) {
					  value = {};
					  editingInput.each( (index, input) => {
						  const $input     = $( input ),
								inputValue = $input.val();
						  if ( inputValue ) {
							  const currendyID    = $input.data( 'currency-id' );
							  value[ currendyID ] = inputValue;
						  }
					  } );
				  }
				  return value;
			  },
			  bulkEditingHandleFieldActionChange: (options, fieldType, action) => {
				  if ( 'multi_currency_price' === fieldType ) {
					  options[ yithBulk.inArray( action, ['new', 'increase-by-value', 'decrease-by-value', 'increase-by-percentage', 'decrease-by-percentage'] ) ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-price-container' );
				  }
				  return options;
			  },
			  bulkEditingSaveChangesOptions     : (options, fieldType, action, $field) => {
				  if ( 'multi_currency_price' === fieldType ) {
					  options.action = $field.find( '.yith-wcbep-bulk-editing-price-container input' ).val();
					  options.value  = $field.find( '.yith-wcbep-bulk-editing-price-container input' ).val();
				  }
				  return options;
			  },
			  bulkEditingEditProductsValue      : (value, field, action, options, fieldValue, $productRow) => {
				  switch ( field ) {
					  case 'sale-price':
						  let regularPrice = yithBulk.maybeParseJSON( $productRow.find( 'td.column-regular_price input.yith-wcbep-column-value' ).val() );
						  regularPrice     = regularPrice.hasOwnProperty( yithWcbepMultiCurrency.defaultCurrency ) ? regularPrice[ yithWcbepMultiCurrency.defaultCurrency ] : 0;
					  case 'multi_currency_price':
						  value     = yithBulk.maybeParseJSON( fieldValue );
						  let price = value.hasOwnProperty( yithWcbepMultiCurrency.defaultCurrency ) ? value[ yithWcbepMultiCurrency.defaultCurrency ] : 0;
						  switch ( action ) {
							  case 'new':
								  price = options.value;
								  break;
							  case 'increase-by-value':
								  price = +price + priceStringToNumber( options?.value ?? 0 );
								  break;
							  case 'decrease-by-value':
								  price -= priceStringToNumber( options?.value ?? 0 );
								  break;
							  case 'increase-by-percentage':
								  price *= (1 + (priceStringToNumber( options?.value ?? 0 ) / 100));
								  break;
							  case 'decrease-by-percentage':
								  price *= (1 - (priceStringToNumber( options?.value ?? 0 ) / 100));
								  break;
							  case 'decrease-by-value-from-regular':
								  price = regularPrice - priceStringToNumber( options?.value ?? 0 );
								  break;
							  case 'decrease-by-percentage-from-regular':
								  price = regularPrice * (1 - (priceStringToNumber( options?.value ?? 0 ) / 100));
								  break;
						  }
						  value[ yithWcbepMultiCurrency.defaultCurrency ] = price;
						  break;
				  }
				  return value;
			  },
		  };

	multiCurrencyIntegration.init();
} );