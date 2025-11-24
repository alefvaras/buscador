/* global yithWcbep */

jQuery( function ( $ ) {
	var initSelect2        = () => {
			initNumericSelect2( $( 'select.yith-wcbep-table-view-number-condition__operator.wc-enhanced-select, select.yith-wcbep-table-view-price-condition__operator.wc-enhanced-select' ) );
			$( document.body ).trigger( 'yith-framework-enhanced-select-init' );
			$( document.body ).trigger( 'wc-enhanced-select-init' );
		},
		initNumericSelect2 = $select => {
			if ( !$select.length ) {
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
		block              = element => {
			const blockArgs = {
				message   : '',
				overlayCSS: { backgroundColor: '#ffffff', opacity: 0.8, cursor: 'wait' }
			};
			element.block( blockArgs );
		},
		unblock            = element => element.unblock(),
		slideUpThenRemove  = $el => $el.slideUp( 'normal', () => $el.remove() ),
		tableViews         = {
			modal                             : null,
			templates                         : {
				editTableView: wp.template( 'yith-wcbep-edit-table-view' ),
				tableView    : wp.template( 'yith-wcbep-table-view' ),
				conditions   : {
					condition        : wp.template( 'yith-wcbep-edit-table-view-condition' ),
					text             : wp.template( 'yith-wcbep-edit-table-view-text' ),
					price            : wp.template( 'yith-wcbep-edit-table-view-price' ),
					number           : wp.template( 'yith-wcbep-edit-table-view-number' ),
					status           : wp.template( 'yith-wcbep-edit-table-view-status' ),
					productVisibility: wp.template( 'yith-wcbep-edit-table-view-product-visibility' ),
					stockStatus      : wp.template( 'yith-wcbep-edit-table-view-stock-status' ),
					featured         : wp.template( 'yith-wcbep-edit-table-view-featured' ),
					backorder        : wp.template( 'yith-wcbep-edit-table-view-backorder' ),
					shippingClass    : wp.template( 'yith-wcbep-edit-table-view-shipping-class' ),
					productType      : wp.template( 'yith-wcbep-edit-table-view-product-type' ),
					category         : wp.template( 'yith-wcbep-edit-table-view-category' ),
					tag              : wp.template( 'yith-wcbep-edit-table-view-tag' ),
					attribute        : wp.template( 'yith-wcbep-edit-table-view-attribute' )
				}
			},
			init                              : function () {
				$( document ).on( 'click', 'ul#select2-yith-wcbep-table-view-results li:nth-last-child(1), button.yith-wcbep-empty-state-manage-views', tableViews.openTableViewsModal );
				$( document ).on( 'change', 'select#yith-wcbep-table-view', tableViews.handleTableViewChange );

				$( document ).on( 'change', 'select.yith-wcbep-table-view-attribute-condition', tableViews.handleAttributeConditionTypeChange );
				$( document ).on( 'change', 'select.yith-wcbep-table-view-product-type-condition__value', tableViews.handleProductTypeChange );
			},
			selectView                        : function ( e ) {
				if ( !$( e.target ).is( 'button' ) ) {
					$( this ).find( 'input.yith-wcbep-table-view-input' ).prop( 'checked', true ).trigger( 'change' );
				}
			},
			getModalContentElement            : function () {
				if ( this.modal && this.modal?.elements && this.modal.elements?.content ) {
					return $( this.modal.elements.content );
				}
				return false;
			},
			openTableViewsModal               : function () {
				const $select = $( '#yith-wcbep-table-view' );
				$select.select2( 'close' );
				tableViews.modal = yith.ui.modal( { ...yithWcbep.modals.tableViews, onClose: tableViews.handleTableViewsModalClosing } );
				// Mark as checked the selected table view.
				tableViews.getModalContentElement().find( 'input#yith-wcbep-table-view-' + $select.val() ).prop( 'checked', true );

				const $modal       = tableViews.getModalContentElement(),
					  $modalFooter = tableViews.modal.elements.footer;
				if ( $modal ) {
					initSelect2();
					$modal.on( 'click', '.yith-wcbep-table-view-wrapper:not(.yith-wcbep-table-view-wrapper--editing) .yith-wcbep-table-view-action-edit', tableViews.editView );
					$modal.on( 'click', '.yith-wcbep-table-view-create:not(.yith-wcbep-table-view-create--creating)', tableViews.createTableViewOptions );
					$modal.on( 'change', 'select.yith-wcbep-edit-table-view-condition-type-select', tableViews.handleConditionTypeChange );
					$modal.on( 'click', '.yith-wcbep-edit-table-view-add-condition', tableViews.addCondition );
					$modal.on( 'change keyup', 'input#yith-wcbep-edit-table-view-name', tableViews.updateViewName );
					$modal.on( 'click', '.yith-wcbep-edit-table-view-remove-condition', tableViews.deleteCondition );
					$modal.on( 'click', '.yith-wcbep-table-view-wrapper:not(.yith-wcbep-table-view-create) .yith-wcbep-edit-table-view-cancel-button', tableViews.cancelViewEditing );
					$modal.on( 'click', '.yith-wcbep-table-view-create .yith-wcbep-edit-table-view-cancel-button', tableViews.cancelViewCreating );
					$modal.on( 'click', '.yith-wcbep-table-view-create .yith-wcbep-edit-table-view-save-button', tableViews.createTableView );
					$modal.on( 'click', '.yith-wcbep-table-view-actions .yith-wcbep-table-view-action-delete', tableViews.showDeleteConfimation );
					$modal.on( 'click', '.yith-wcbep-table-view-delete-confirmation__confirm', tableViews.deleteTableView );
					$modal.on( 'click', '.yith-wcbep-table-view-delete-confirmation__not-confirm', tableViews.hideDeleteConfimation );
					$modal.on( 'click', '.yith-wcbep-table-view-actions .yith-wcbep-table-view-action-clone', tableViews.cloneTableView );
					$modal.on( 'click', '.yith-wcbep-edit-table-view-save-button', tableViews.updateTableView );
					$modal.on( 'click', '.yith-wcbep-table-view-wrapper:not(.yith-wcbep-table-view-wrapper--editing)', tableViews.selectView );
					$modal.on( 'click', '.yith-wcbep-table-view-wrapper:not(.yith-wcbep-table-view-wrapper--editing)', tableViews.selectView );

					$modalFooter.on( 'click', '.yith-wcbep-table-view-modal-button-save', tableViews.saveModal );
					$modalFooter.on( 'click', '.yith-wcbep-table-view-modal-button-cancel', tableViews.modal.close );
				}
			},
			saveModal                         : () => {
				if ( tableViews?.modal ) {
					tableViews.handleTableViewsModalClosing.save = true;
					tableViews.modal.close();
				}
			},
			handleTableViewsModalClosing      : function () {
				var modal = tableViews.getModalContentElement();
				if ( modal ) {
					var input             = modal.find( 'input.yith-wcbep-table-view-input:checked' ),
						$select           = $( '#yith-wcbep-table-view' ),
						manageViews       = $select.find( 'option:nth-last-child(1)' ),
						selectedValue     = $select.val(),
						modalSelectedView = input.val();
					$select.find( 'option' ).not( manageViews ).remove();
					if ( 'custom-filters' === selectedValue ) {
						manageViews.before( `<option value="custom-filters">${yithWcbep.i18n.customFilters}</option>` );
					}
					modal.find( '.yith-wcbep-table-view-wrapper:not(.yith-wcbep-table-view-create)' ).each( function () {
						var view           = $( this ).closest( '.yith-wcbep-table-view-container' ),
							viewProperties = view.data( 'view-properties' ),
							viewKey        = view.data( 'view-key' ),
							viewName       = viewProperties.name;
						manageViews.before( `<option value="${viewKey}">${viewName}</option>` );
					} );


					if ( !tableViews.handleTableViewsModalClosing?.save ) {
						modalSelectedView = selectedValue;
					} else {
						tableViews.handleTableViewsModalClosing.save = false;
					}

					$select.find( 'option[value="' + $.escapeSelector( modalSelectedView ) + '"]' ).attr( 'selected', true );

					if ( tableViews.handleTableViewsModalClosing?.forceUpdate || selectedValue !== modalSelectedView ) {
						$select.trigger( 'change' );
						tableViews.handleTableViewsModalClosing.forceUpdate = false;
					}

					modal.find( '.yith-wcbep-edit-table-view-form' ).remove();
					modal.find( '.yith-wcbep-table-view-wrapper.yith-wcbep-table-view-wrapper--editing' ).removeClass( 'yith-wcbep-table-view-wrapper--editing' );
					tableViews.cancelViewCreating();
					yithWcbep.modals.tableViews.content = modal.html();
				}
			},
			editView                          : function () {
				const $viewContainer = $( this ).closest( '.yith-wcbep-table-view-wrapper' );
				if ( !$viewContainer.hasClass( 'yith-wcbep-table-view-wrapper--editing' ) ) {
					$viewContainer.addClass( 'yith-wcbep-table-view-wrapper--editing' );
					const viewProperties = JSON.parse( $viewContainer.closest( '.yith-wcbep-table-view-container' ).attr( 'data-view-properties' ) ),
						  $editingFields = $( tableViews.templates.editTableView( { viewName: viewProperties.name } ) );
					$viewContainer.append( $editingFields );

					let conditionsContainer = false;
					if ( viewProperties?.conditions ) {
						conditionsContainer = $editingFields.find( '.yith-wcbep-edit-table-view-form-conditions' );
						conditionsContainer.append( tableViews.getConditionsFields( viewProperties.conditions ) );
					}

					initSelect2();
					conditionsContainer && conditionsContainer.find( 'select.yith-wcbep-table-view-product-type-condition__value' ).trigger( 'change' );

					$editingFields.slideDown( {
						start: function () {
							$( this ).css( 'display', 'flex' );
						}
					} );
				}
			},
			addDynamicConditionsToSelect      : select => {
				const $select = $( select );
				if ( $select.length ) {
					Object.keys( yithWcbep.columnList ).filter( key => key.indexOf( 'yith_wcbep_tf_' ) === 0 || key.indexOf( 'yith_wcbep_cf_' ) === 0 ).forEach( value => $select.append( `<option value="${value}">${yithWcbep.columnList[ value ]}</option>` ) );
				}
			},
			checkMandatoryCondition           : $view => {
				if ( $view.length ) {
					const conditions                 = tableViews.getViewConditions( $view ),
						  $mandatoryConditionWrapper = $view.find( '.yith-wcbep-edit-table-view-mandatory-condition-message' ),
						  $conditionsWrapper         = $view.find( '.yith-wcbep-edit-table-view-form-conditions' );
					if ( conditions.length ) {
						$conditionsWrapper.removeClass( 'yith-wcbep-edit-table-view-form-conditions--invalid' );
						$mandatoryConditionWrapper.slideUp();
					} else {
						$mandatoryConditionWrapper.slideDown();
						$conditionsWrapper.addClass( 'yith-wcbep-edit-table-view-form-conditions--invalid' );
					}
				}
			},
			updateViewName                    : function () {
				const $input            = $( this ),
					  $nameFieldWrapper = $input.closest( '.yith-wcbep-edit-table-view-name-wrapper' ),
					  $tableViewWrapper = $input.closest( '.yith-wcbep-table-view-wrapper' ),
					  $name             = $tableViewWrapper.find( 'label.yith-wcbep-table-view-name' ),
					  name              = $input.val()?.trim(),
					  $invalidMessage   = $tableViewWrapper.find( '.yith-wcbep-edit-table-view-mandatory-name-message' );
				if ( !name ) {
					$nameFieldWrapper.addClass( 'yith-wcbep-edit-table-view-name--invalid' );
					$invalidMessage.slideDown();
					const modalInfo = tableViews.modal.elements.content[ 0 ].getBoundingClientRect(),
						  inputInfo = $input[ 0 ].getBoundingClientRect();
					if ( modalInfo.y > inputInfo.y || modalInfo.y + modalInfo.height < inputInfo.y ) {
						tableViews.modal.elements.content[ 0 ].scrollTop -= modalInfo.y - inputInfo.y + 70;
					}
				} else {
					$nameFieldWrapper.removeClass( 'yith-wcbep-edit-table-view-name--invalid' );
					$invalidMessage.slideUp();
				}

				$name.length && $name.html( $input.val() );
			},
			cancelViewEditing                 : function () {
				var $editingContainer = $( this ).closest( '.yith-wcbep-edit-table-view-form' ),
					$tableView        = $editingContainer.closest( '.yith-wcbep-table-view-container' ),
					tableViewProps    = $tableView.data( 'view-properties' );
				$editingContainer.closest( '.yith-wcbep-table-view-wrapper' ).removeClass( 'yith-wcbep-table-view-wrapper--editing' );
				$editingContainer.slideUp( 'normal', function () {
					$editingContainer.remove();
				} );
				if ( tableViewProps && tableViewProps.hasOwnProperty( 'name' ) ) {
					$tableView.find( '.yith-wcbep-table-view-name' ).html( tableViewProps.name );
				}
			},
			cancelViewCreating                : function () {
				var $container = tableViews.getModalContentElement().find( '.yith-wcbep-table-view-wrapper.yith-wcbep-table-view-create' );
				slideUpThenRemove( $container.find( '.yith-wcbep-table-view-create-form' ) );
				$container.find( '.yith-wcbep-table-view-create-label' ).slideDown();
				$container.removeClass( 'yith-wcbep-table-view-create--creating' );
			},
			addCondition                      : function ( conditionContainer, animate ) {
				animate            = undefined === animate ? true : !!animate;
				conditionContainer = conditionContainer && conditionContainer?.length ? conditionContainer : $( this ).closest( '.yith-wcbep-edit-table-view-form' ).find( '.yith-wcbep-edit-table-view-form-conditions' );
				var $condition     = $( tableViews.templates.conditions.condition( {} ) );
				$condition.hide();
				conditionContainer.append( $condition );
				tableViews.addDynamicConditionsToSelect( $condition.find( 'select' ) );
				initSelect2();
				animate ? $condition.slideDown() : $condition.show();
			},
			deleteCondition                   : function () {
				$( this ).closest( '.yith-wcbep-edit-table-view-condition' ).remove();
			},
			handleConditionTypeChange         : function () {
				var $select                    = $( this ),
					$conditionOptionsContainer = $select.closest( '.yith-wcbep-edit-table-view-condition-options' ).find( '.yith-wcbep-edit-table-view-condition-option' ),
					conditionType              = $select.val(),
					$conditionOptions          = tableViews.getConditionOptionsFields( { type: conditionType } );
				if ( $conditionOptions ) {
					$conditionOptionsContainer.html( $conditionOptions );
					const type = tableViews.getConditionType( conditionType );
					if ( yithBulk.inArray( type, ['number', 'price'] ) ) {
						initNumericSelect2( $conditionOptionsContainer.find( 'select.yith-wcbep-table-view-' + type + '-condition__operator' ) );
					} else {
						initSelect2();
					}
				} else {
					$conditionOptionsContainer.html( '' );
				}
				tableViews.checkMandatoryCondition( $select.closest( '.yith-wcbep-edit-table-view-form' ) );

			},
			handleProductTypeChange           : function () {
				const $select           = $( this ),
					  $includeVariation = $select.closest( '.yith-wcbep-table-view-product-type-condition' ).find( '.yith-wcbep-table-view-product-type-include-variation' );
				$includeVariation.css( 'display', ['any', 'variable'].indexOf( $select.val() ) !== -1 ? 'block' : 'none' );
			},
			handleAttributeConditionTypeChange: function () {
				const $select          = $( this ),
					  $attributeSelect = $select.parent().find( '.yith-plugin-fw-select2-wrapper' );
				$attributeSelect.css( 'display', ['has', 'has-not'].indexOf( $select.val() ) !== -1 ? 'block' : 'none' );
			},
			getConditionsFields               : function ( conditions ) {
				var $container = $( '<div></div>' );
				$.each( conditions, function ( index, condition ) {
					if ( condition.hasOwnProperty( 'type' ) ) {
						const $conditionContainer    = $( tableViews.templates.conditions.condition( { condition: condition.type } ) ),
							  conditionOptionsFields = tableViews.getConditionOptionsFields( condition ),
							  $select                = $conditionContainer.find( 'select.yith-wcbep-edit-table-view-condition-type-select' );
						tableViews.addDynamicConditionsToSelect( $select );
						$select.find( 'option[value="' + $.escapeSelector( condition.type ) + '"]' ).attr( 'selected', true );
						if ( conditionOptionsFields ) {
							$conditionContainer.find( '.yith-wcbep-edit-table-view-condition-option' ).html( conditionOptionsFields );
						}
						$container.append( $conditionContainer );
					}
				} );
				return $container.html();
			},
			getConditionType                  : type => {
				const conditionTypeTemplates = {
					'shortdesc'          : 'text',
					'desc'          : 'text',
					'title'         : 'text',
					'sku'           : 'text',
					'stock-quantity': 'number',
					'weight'        : 'number',
					'height'        : 'number',
					'width'         : 'number',
					'length'        : 'number',
					'regular-price' : 'price',
					'sale-price'    : 'price'
				};
				if ( type.indexOf( 'yith_wcbep_tf_' ) === 0 ) {
					type = 'custom-taxonomy';
				} else {
					if ( type.indexOf( 'yith_wcbep_cf_' ) === 0 && yithWcbep.customFields.hasOwnProperty( type ) ) {
						type = yithWcbep.customFields[ type ].type;
					}
					type = conditionTypeTemplates.hasOwnProperty( type ) ? conditionTypeTemplates[ type ] : type;
				}
				return type;
			},
			getConditionTemplate              : type => {
				type = tableViews.getConditionType( type );

				return $( 'script#tmpl-yith-wcbep-edit-table-view-' + type ).length ? wp.template( 'yith-wcbep-edit-table-view-' + type ) : false;
			},
			getConditionOptionsFields         : function ( condition ) {
				var conditionOptions = false;

				if ( condition.hasOwnProperty( 'type' ) ) {

					if ( !condition.hasOwnProperty( 'options' ) ) {
						condition.options = {};
					}

					const template = tableViews.getConditionTemplate( condition.type );
					if ( template ) {
						conditionOptions    = $( template( condition.options ) );
						const conditionType = condition.type.indexOf( 'yith_wcbep_cf_' ) === 0 || condition.type.indexOf( 'yith_wcbep_tf_' ) === 0 ? tableViews.getConditionType( condition.type ) : condition.type;
						switch ( conditionType ) {
							case 'text':
							case 'desc':
							case 'shortdesc':
							case 'title':
							case 'sku':
								if ( condition.options.hasOwnProperty( 'compare' ) ) {
									conditionOptions.find( 'select option[value="' + $.escapeSelector( condition.options.compare ) + '"]' ).attr( 'selected', true );
								}
								break;
							case 'sale-price':
							case 'regular-price':
							case 'price':
								if ( condition.options.hasOwnProperty( 'operator' ) ) {
									conditionOptions.find( 'select.yith-wcbep-table-view-price-condition__operator option[value="' + $.escapeSelector( condition.options.operator ) + '"]' ).attr( 'selected', true );
								}
								break;
							case 'stock-quantity':
							case 'weight':
							case 'height':
							case 'width':
							case 'length':
								if ( condition.options.hasOwnProperty( 'operator' ) ) {
									conditionOptions.find( 'select.yith-wcbep-table-view-number-condition__operator option[value="' + $.escapeSelector( condition.options.operator ) + '"]' ).attr( 'selected', true );
								}
								break;
							case 'product-type':
								if ( condition.options.hasOwnProperty( 'value' ) ) {
									conditionOptions.find( 'select.yith-wcbep-table-view-' + condition.type + '-condition__value option[value="' + $.escapeSelector( condition.options.value ) + '"]' ).attr( 'selected', true );
									if ( 'yes' === condition.options?.include_variations ) {
										conditionOptions.find( 'input.yith-wcbep-table-view-product-type-include-variation-input' ).attr( 'checked', true );
									}
								}
								break;
							case 'custom-taxonomy':
								conditionOptions = $( template( { taxonomyID: condition.type.substr( 14 ) } ) );
							case 'shipping-class':
							case 'category':
							case 'tag':
								const $condition = conditionOptions.find( `select.yith-wcbep-table-view-${conditionType}-condition__compare` ),
									  $select    = conditionOptions.find( `select.yith-wcbep-table-view-${conditionType}-condition__value` );
								$condition.find( `option[value="${condition.options.condition}"]` ).attr( 'selected', true );
								Object.keys( condition.options?.taxonomies ?? {} ).forEach( taxonomyID => {
									$select.append( $( `<option value="${taxonomyID}" selected>${condition.options.taxonomies[ taxonomyID ]}</option>` ) );
								} );
								break;
							case 'attribute':
								if ( Array.isArray( condition.options ) ) {
									condition.options.forEach( options => {
										options.terms             = options.terms ?? {};
										const $attributeContainer = conditionOptions.find( `.yith-wcbep-table-view-attribute__${options?.attribute_id ?? ''}` );
										if ( $attributeContainer.length ) {
											const $attributeCondition = $attributeContainer.find( 'select.yith-wcbep-table-view-attribute-condition' );
											$attributeCondition.find( `option[value="${options.condition}"]` ).attr( 'selected', true );
											if ( ['has', 'has-not'].indexOf( options.condition ) !== -1 ) {
												const $attributeSelect = $attributeContainer.find( 'select.yith-wcbep-table-view-attribute-condition__value' );
												$attributeSelect.parent().css( 'display', 'block' );
												Object.keys( options.terms ).forEach( termID => {
													$attributeSelect.append( $( `<option value="${termID}" selected>${options.terms[ termID ]}</option>` ) );
												} );
											}
										}
									} );
								}
								break;
							default:
								if ( condition.options.hasOwnProperty( 'value' ) ) {
									conditionOptions.find( 'select.yith-wcbep-table-view-' + condition.type + '-condition__value option[value="' + $.escapeSelector( condition.options.value ) + '"]' ).attr( 'selected', true );
								}
								break;
						}
						conditionOptions = wp.hooks.applyFilters( 'yithWcbepTableViewGetConditionOptionsFields', conditionOptions, condition, conditionType );
					}
				}
				return conditionOptions;
			},
			createTableViewOptions            : function () {
				var $container = $( this );
				if ( !$container.hasClass( 'yith-wcbep-table-view-create--creating' ) ) {
					$container.addClass( 'yith-wcbep-table-view-create--creating' );

					var $label = $container.find( '.yith-wcbep-table-view-create-label' ),
						$form  = $( '<div class="yith-wcbep-table-view-create-form"></div>' );

					$form.html( $( tableViews.templates.editTableView( { viewName: '' } ) ) );
					$container.append( $form );
					tableViews.addCondition( $form.find( '.yith-wcbep-edit-table-view-form-conditions' ), false );

					$label.slideUp();
					$form.find( '.yith-wcbep-edit-table-view-form' ).slideDown( {
						start: function () {
							$( this ).css( 'display', 'flex' );
						}
					} );

					if ( $container.hasClass( 'yith-wcbep-table-view-create--creating' ) ) {
						var $modal = tableViews.getModalContentElement();
						if ( $modal ) {
							$modal.animate( { scrollTop: $modal.height() + 400 }, 400 );
						}
					}
				}
			},
			createTableView                   : function () {
				var $container   = $( this ).closest( '.yith-wcbep-table-view-wrapper' ),
					$nameInput   = $container.find( 'input#yith-wcbep-edit-table-view-name' ),
					view_options = {
						name      : $nameInput.val().trim(),
						conditions: tableViews.getViewConditions( $container )
					};
				$nameInput.trigger( 'change' );
				tableViews.checkMandatoryCondition( $container );
				if ( view_options.name && view_options.conditions.length ) {
					block( $container );
					$.ajax( {
						data    : {
							view_options,
							view_action: 'create',
							action     : yithWcbep.actions.tableViewAction,
							security   : yithWcbep.security.tableViewAction
						},
						type    : 'POST',
						dataType: 'json',
						url     : yithWcbep.ajaxurl,
						success : function ( response ) {
							if ( response.hasOwnProperty( 'success' ) && response.hasOwnProperty( 'view' ) && 'success' === response.success ) {
								tableViews.getModalContentElement().find( '.yith-wcbep-table-view-wrapper.yith-wcbep-table-view-create' ).before( tableViews.templates.tableView( response.view ) );
								tableViews.cancelViewCreating();
							}
						},
						complete: function () {
							unblock( $container );
						}
					} );
				}
			},
			showDeleteConfimation             : function () {
				$( this ).closest( '.yith-wcbep-table-view-container' ).addClass( 'yith-wcbep-table-view-show-delete-confirmation' );
			},
			hideDeleteConfimation             : function () {
				$( this ).closest( '.yith-wcbep-table-view-container' ).removeClass( 'yith-wcbep-table-view-show-delete-confirmation' );
			},
			deleteTableView                   : function () {
				var $container      = $( this ).closest( '.yith-wcbep-table-view-container' ),
					$tableViewsList = $container.closest( '.yith-wcbep-table-views-list' ),
					view_key        = $container.data( 'view-key' );
				block( $tableViewsList );
				$.ajax( {
					data    : {
						view_key,
						view_action: 'delete',
						action     : yithWcbep.actions.tableViewAction,
						security   : yithWcbep.security.tableViewAction
					},
					type    : 'POST',
					dataType: 'json',
					url     : yithWcbep.ajaxurl,
					success : function ( response ) {
						if ( 'success' === response?.success ) {
							if ( $container.find( 'input.yith-wcbep-table-view-input' ).is( ':checked' ) ) {
								$container.closest( '.yith-wcbep-table-views-list' ).find( 'input#yith-wcbep-table-view-table-view-all' ).prop( 'checked', true ).trigger( 'change' );
								tableViews.handleTableViewsModalClosing.forceUpdate = true;
							}
							$container.remove();
						}
					},
					complete: function () {
						unblock( $tableViewsList );
					}
				} );
			},
			cloneTableView                    : function () {
				var $container      = $( this ).closest( '.yith-wcbep-table-view-container' ),
					$tableViewsList = $container.closest( '.yith-wcbep-table-views-list' ),
					view_key        = $container.data( 'view-key' );
				block( $tableViewsList );
				$.ajax( {
					data    : {
						view_key,
						view_action: 'clone',
						action     : yithWcbep.actions.tableViewAction,
						security   : yithWcbep.security.tableViewAction
					},
					type    : 'POST',
					dataType: 'json',
					url     : yithWcbep.ajaxurl,
					success : function ( response ) {
						if ( 'success' === response?.success && response?.view ) {
							$container.after( tableViews.templates.tableView( response.view ) );
						}
					},
					complete: function () {
						unblock( $tableViewsList );
					}
				} );
			},
			updateTableView                   : function () {
				var $view           = $( this ).closest( '.yith-wcbep-table-view-container' ),
					$nameInput      = $view.find( 'input#yith-wcbep-edit-table-view-name' ),
					view_properties = {
						name      : $nameInput.val()?.trim(),
						conditions: tableViews.getViewConditions( $view )
					},
					view_key        = $view.data( 'view-key' );

				$nameInput.trigger( 'change' );
				tableViews.checkMandatoryCondition( $view );

				if ( view_properties.name && view_properties.conditions.length ) {
					block( $view );
					$.ajax( {
						data    : {
							view_key,
							view_properties,
							view_action: 'update',
							action     : yithWcbep.actions.tableViewAction,
							security   : yithWcbep.security.tableViewAction
						},
						type    : 'POST',
						dataType: 'json',
						url     : yithWcbep.ajaxurl,
						success : function ( response ) {
							if ( response && 'success' === response?.success && response?.viewProperties ) {
								slideUpThenRemove( $view.find( '.yith-wcbep-edit-table-view-form' ) );
								$view.find( '.yith-wcbep-table-view-wrapper' ).removeClass( 'yith-wcbep-table-view-wrapper--editing' );
								$view.attr( 'data-view-properties', JSON.stringify( response.viewProperties ) );
								$view.find( '.yith-wcbep-table-view-name' ).html( response.viewProperties.name );
								if ( tableViews.getSelectedViewFromSelect() === view_key ) {
									tableViews.handleTableViewsModalClosing.forceUpdate = 1;
								}
							}
						},
						complete: function () {
							unblock( $view );
						}
					} );
				}
			},
			getSelectedViewFromSelect         : () => {
				return $( '#yith-wcbep-table-view' ).val();
			},
			getViewConditions                 : function ( $view ) {
				let viewConditions = [];
				$view.find( '.yith-wcbep-edit-table-view-condition-type select.yith-wcbep-edit-table-view-condition-type-select' ).each( function () {
					const $condition         = $( this ),
						  conditionContainer = $condition.closest( '.yith-wcbep-edit-table-view-condition-type' ).next();
					let conditionType        = $condition.val();
					if ( conditionType && conditionContainer.hasClass( 'yith-wcbep-edit-table-view-condition-option' ) || 'include-variations' === conditionType ) {
						let condition = {
							type: conditionType
						};
						if ( conditionType.indexOf( 'yith_wcbep_cf_' ) === 0 || conditionType.indexOf( 'yith_wcbep_tf_' ) === 0 ) {
							conditionType = tableViews.getConditionType( conditionType );
						}
						switch ( conditionType ) {
							case 'title':
							case 'desc':
							case 'shortdesc':
							case 'sku':
							case 'text':
								condition.options = {
									compare: conditionContainer.find( 'select.yith-wcbep-table-view-text-condition__compare' ).val(),
									value  : conditionContainer.find( 'input.yith-wcbep-table-view-text-condition__value' ).val()
								};
								break;
							case 'sale-price':
							case 'regular-price':
							case 'price':
								condition.options = {
									operator: conditionContainer.find( 'select.yith-wcbep-table-view-price-condition__operator' ).val(),
									value   : conditionContainer.find( 'input.yith-wcbep-table-view-price-condition__value' ).val()
								};
								break;
							case 'stock-quantity':
							case 'weight':
							case 'height':
							case 'width':
							case 'length':
								condition.options = {
									operator: conditionContainer.find( 'select.yith-wcbep-table-view-number-condition__operator' ).val(),
									value   : conditionContainer.find( 'input.yith-wcbep-table-view-number-condition__value' ).val()
								};
								break;
							case 'attribute':
								condition.options = [];
								conditionContainer.find( '.yith-wcbep-table-view-attribute' ).each( ( index, attributeContainer ) => {
									const $attributeContainer = $( attributeContainer ),
										  attributeID         = $attributeContainer.data( 'attribute-id' ),
										  selectedCondition   = $attributeContainer.find( 'select.yith-wcbep-table-view-attribute-condition' ).val();
									let attributeCondition    = {
										attribute_id: attributeID,
										condition   : selectedCondition
									};
									if ( ['has', 'has-not'].indexOf( selectedCondition ) !== -1 ) {
										const $attributeSelect      = $attributeContainer.find( 'select.yith-wcbep-table-view-attribute-condition__value' );
										attributeCondition.terms    = $attributeSelect.val();
										attributeCondition.taxonomy = $attributeSelect.data( 'taxonomy' );
									}
									condition.options.push( attributeCondition );
								} );
								break;
							case 'custom-taxonomy':
							case 'category':
							case 'tag':
							case 'shipping-class':
								condition.options = {
									condition : conditionContainer.find( `select.yith-wcbep-table-view-${conditionType}-condition__compare` ).val(),
									taxonomies: conditionContainer.find( `select.yith-wcbep-table-view-${conditionType}-condition__value` ).val()
								};
								break;
							case 'product-type':
								condition.options = {
									value             : conditionContainer.find( 'select.yith-wcbep-table-view-' + conditionType + '-condition__value' ).val(),
									include_variations: conditionContainer.find( 'input.yith-wcbep-table-view-product-type-include-variation-input' ).prop( 'checked' ) ? 'yes' : 'no'
								};
								break;
							case 'include-variations':
								condition.options = {
									value: 'yes'
								};
								break;
							default:
								condition.options = {
									value: conditionContainer.find( 'select.yith-wcbep-table-view-' + conditionType + '-condition__value' ).val()
								};
								break;
						}
						condition = wp.hooks.applyFilters( 'yithWcbepGetTableViewConditionsCondition', condition, conditionType, conditionContainer );

						if ( condition?.options ) {
							viewConditions.push( condition );
						}
					}
				} );
				return viewConditions;
			},
			handleTableViewChange             : function ( e, data ) {
				if ( !data || !data.hasOwnProperty( 'updateTable' ) || data.updateTable ) {
					$( document ).trigger( 'yith-wcbep-load-table', { update_table_view: 'yes', paged: 1 } );
				}
			}
		};

	tableViews.init();
} );
