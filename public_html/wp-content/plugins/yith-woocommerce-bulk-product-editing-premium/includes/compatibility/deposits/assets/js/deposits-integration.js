jQuery( function ($) {

	const depositsIntegration = {
		init             : () => {
			wp.hooks.addFilter( 'yithWcbepGetFloatingField', 'yithWcbepIntegration', depositsIntegration.getFloatingField );
			wp.hooks.addFilter( 'yithWcbepUpdateColumnValue', 'yithWcbepIntegration', depositsIntegration.updateColumnValue );
		},
		getFloatingField : ($field, columnOptions, value, template) => {
			switch ( columnOptions?.type ) {
				case 'enable-deposit':
				case 'force-deposit':
				case 'deposit-default':
				case 'create-balance-orders':
					value           = yithBulk.maybeParseJSON( value );
					let optionValue = Object.keys( value );
					if ( optionValue.length ) {
						$field.find( 'option[value="' + optionValue[ 0 ] + '"]' ).attr( 'selected', true );
					}
					break;
			}

			return $field;
		},
		updateColumnValue: ({value, display}, columnOptions, columnDisplayContainer) => {
			if ( yithBulk.inArray( columnOptions?.type, ['enable-deposit', 'force-deposit', 'deposit-default', 'create-balance-orders'] ) ) {
				if ( value.hasOwnProperty( 'text' ) && value.hasOwnProperty( 'value' ) ) {
					display                 = value.text;
					let newValue            = {};
					newValue[ value.value ] = value.text;
					value                   = JSON.stringify( newValue );
				} else if ( yithBulk.isJsonString( value ) ) {
					value   = yithBulk.maybeParseJSON( value );
					display = Object.values( value )[ 0 ];
					value   = JSON.stringify( value );
				}
			}
			return {value, display};
		},
	};

	depositsIntegration.init();
} );
