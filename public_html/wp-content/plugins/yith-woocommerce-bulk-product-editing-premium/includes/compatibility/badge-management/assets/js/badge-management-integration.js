jQuery( function ($) {
	const badgeManagement = {
		init                     : () => {
			wp.hooks.addFilter( 'yithWcbepGetTableViewConditionsCondition', 'yithWcbepIntegration', badgeManagement.getTableViewCondition );
			wp.hooks.addFilter( 'yithWcbepTableViewGetConditionOptionsFields', 'yithWcbepIntegration', badgeManagement.getConditionOptionsFields );
		},
		getTableViewCondition    : (condition, conditionType, conditionContainer) => {
			if ( 'badge' === conditionType ) {
				condition.options = {
					condition: conditionContainer.find( 'select.yith-wcbep-table-view-badge-condition__compare' ).val(),
				};

			}

			return condition;
		},
		getConditionOptionsFields: (conditionOptions, condition, conditionType) => {
			if ( 'badge' === conditionType ) {
				conditionOptions.find( 'select.yith-wcbep-table-view-badge-condition__compare option[value="' + condition.options.condition + '"]' ).attr( 'selected', true );
			}

			return conditionOptions;
		},
	};

	badgeManagement.init();
} );
