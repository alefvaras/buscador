/* global yithWcbep */
jQuery( function ($) {
	class Field {
		constructor(element) {
			const $element = $( element );
			this.$element  = $element.hasClass( 'yith-wcbep-enable-fields__field' ) ? $element : $element.closest( '.yith-wcbep-enable-fields__field' );
			if ( this.$element.length ) {
				this.data = this.$element.data( 'field-options' );
			}

			this.deletingClass      = 'yith-wcbep-enable-fields__field--deleting';
			this.deletingErrorClass = 'yith-wcbep-enable-fields__field--deleting--error';
		}

		getKey() {
			return this.getProp( 'key' );
		}

		getKind() {
			return this.getProp( 'kind' );
		}

		getProp(prop) {
			return this.data.hasOwnProperty( prop ) ? this.data[ prop ] : false;
		}

		delete() {}

		getChanges() { return {};}

		updateData() {
			this.data = this.$element.data( 'field-options' );
		}
	}

	class CustomField extends Field {
		constructor(element) {
			super( element );
			if ( this.$element.hasClass( 'yith-wcbep-enable-fields__field-clone' ) ) {
				const fieldKey = this.getKey();
				if ( fieldKey ) {
					const $field = $( '#' + fieldKey + '.yith-wcbep-enable-fields__field:not(.yith-wcbep-enable-fields__field-clone)' );
					if ( $field.length ) {
						this.$element = $field;
					}
				}
			}
		}

		delete() {
			const field = this;
			let success = false;
			field.$element.addClass( field.deletingClass );

			$.ajax( {
				data    : {
					kind    : this.getKind(),
					field_id: this.getId(),
					action  : yithWcbep.actions.deleteCustomFieldColumn,
					security: yithWcbep.security.deleteCustomFieldColumn,
				},
				type    : 'POST',
				dataType: 'json',
				url     : yithWcbep.ajaxurl,
				success : response => {
					if ( response && 'success' === response?.success ) {
						success = true;

						delete yithWcbep.columnList[ field.getKey() ];
						delete yithWcbep.customFields[ field.getKey() ];
						yithWcbep.enabledColumns = yithWcbep.enabledColumns.filter( key => key !== field.getKey() );
						field.$element.remove();
						enableFields.handleSectionTitlesVisibility();
						enableFields.searchField();
					}
				},
				complete: () => {
					if ( ! success ) {
						field.$element.removeClass( field.deletingClass );
						field.$element.addClass( field.deletingErrorClass );
						setTimeout( () => field.$element.removeClass( field.deletingErrorClass ), 1000 );
					} else {
						enableFields.handleToggleAllFieldsCheckboxStatus();
					}
				},
			} );
		}

		update(data) {
			const field      = this,
				  fieldClone = $( '#' + field.$element.prop( 'id' ) + '.yith-wcbep-enable-fields__field-clone' );
			let changes      = {};

			if ( data.newType !== this.getType() ) {
				changes.type = data.newType;
			}
			if ( data.newId !== this.getId() ) {
				changes.id = data.newId;
			}
			if ( data.newLabel !== this.getLabel() ) {
				changes.label = data.newLabel;
			}


			if ( Object.keys( changes ).length ) {
				block( fieldClone.find( '.yith-wcbep-enable-fields__edit-field' ) );
				$.ajax( {
					data    : {
						changes,
						kind    : field.getKind(),
						field_id: field.getId(),
						action  : yithWcbep.actions.updateCustomFieldColumn,
						security: yithWcbep.security.updateCustomFieldColumn,
					},
					type    : 'POST',
					dataType: 'json',
					url     : yithWcbep.ajaxurl,
					success : response => {
						if ( 'success' === response?.success ) {
							response.data.fieldClasses = 'yith-wcbep-enable-fields__custom-field';
							fieldClone.remove();
							const enabled     = field.$element.find( 'input.yith-wcbep-enable-fields__field-input' ).prop( 'checked' ),
								  $newElement = $( enableFields.templates.field.customField( response.data ) );
							field.$element.replaceWith( $newElement );
							field.$element = $newElement;
							field.$element.find( 'input.yith-wcbep-enable-fields__field-input' ).prop( 'checked', enabled );

							delete yithWcbep.customFields[ field.getKey() ];
							delete yithWcbep.columnList[ field.getKey() ];
							field.updateData();
							yithWcbep.columnList[ field.getKey() ]   = field.getLabel();
							yithWcbep.customFields[ field.getKey() ] = {
								id   : field.getId(),
								type : field.getType(),
								label: field.getLabel(),
							};
							enableFields.searchField();
						}
						// TODO: error showing.
					},
					complete: function () {
						unblock( fieldClone.find( '.yith-wcbep-enable-fields__edit-field' ) );
					},
				} );
			}
		}

		/* =================
		 *  G E T T E R S
		 */

		getId() {
			return this.getProp( 'id' );
		}

		getType() {
			return this.getProp( 'type' );
		}

		getLabel() {
			return this.getProp( 'label' );
		}

		getChanges() {
			const field      = this,
				  fieldClone = $( '#' + field.$element.prop( 'id' ) + '.yith-wcbep-enable-fields__field-clone' );
			let changes      = {};
			if ( fieldClone.length ) {
				const ID    = fieldClone.find( '#yith-wcbep-edit-field-custom-field-id' ),
					  type  = fieldClone.find( '#yith-wcbep-edit-field-custom-field-type' ),
					  label = fieldClone.find( '#yith-wcbep-edit-field-custom-field-label' );
				if ( ID !== field.getId() ) {
					changes.id = ID;
				}
				if ( type !== field.getType() ) {
					changes.type = type;
				}
				if ( label !== field.getLabel() ) {
					changes.label = label;
				}
			}

			return changes;
		}
	}

	class TaxonomyField extends Field {
		constructor(element) {
			super( element );
			if ( this.$element.hasClass( 'yith-wcbep-enable-fields__field-clone' ) ) {
				const fieldKey = this.getProp( 'key' );
				if ( fieldKey ) {
					const $field = $( '#' + fieldKey + ':not(.yith-wcbep-enable-fields__field-clone)' );
					if ( $field.length ) {
						this.$element = $field;
					}
				}
			}
		}

		delete() {
			const field = this;
			let success = false;

			field.$element.addClass( field.deletingClass );

			$.ajax( {
				data    : {
					kind       : this.getKind(),
					taxonomy_id: this.getTaxonomy(),
					action     : yithWcbep.actions.deleteTaxonomyFieldColumn,
					security   : yithWcbep.security.deleteTaxonomyFieldColumn,
				},
				type    : 'POST',
				dataType: 'json',
				url     : yithWcbep.ajaxurl,
				success : function (response) {
					if ( response && 'success' === response?.success ) {
						delete yithWcbep.columnList[ field.getKey() ];
						enableFields.initTaxonomiesSelects();
						yithWcbep.enabledColumns = yithWcbep.enabledColumns.filter( key => key != field.getKey() );
						field.$element.remove();
						enableFields.handleSectionTitlesVisibility();
						success = true;
						enableFields.searchField();
					}
				},
				complete: function () {
					if ( ! success ) {
						field.$element.removeClass( field.deletingClass );
						field.$element.addClass( field.deletingErrorClass );
						setTimeout( () => field.$element.removeClass( field.deletingErrorClass ), 1000 );
					} else {
						enableFields.handleToggleAllFieldsCheckboxStatus();
					}
				},
			} );
		}

		update(data) {
			const field      = this,
				  fieldClone = $( '#' + field.$element.prop( 'id' ) + '.yith-wcbep-enable-fields__field-clone' );

			if ( data?.taxonomy && data.taxonomy !== this.getTaxonomy() ) {
				block( fieldClone.find( '.yith-wcbep-enable-fields__edit-field' ) );
				$.ajax( {
					data    : {
						taxonomy : data.taxonomy,
						field_key: this.getKey(),
						action   : yithWcbep.actions.updateTaxonomyFieldColumn,
						security : yithWcbep.security.updateTaxonomyFieldColumn,
					},
					type    : 'POST',
					dataType: 'json',
					url     : yithWcbep.ajaxurl,
					success : function (response) {
						if ( response && 'success' === response?.success ) {
							fieldClone.remove();
							field.$element.replaceWith( enableFields.templates.field.taxonomy( response.data ) );
							field.updateData();
							delete yithWcbep.columnList[ response.oldKey ];
							yithWcbep.columnList[ response.newKey ] = response.data.fieldLabel;
							enableFields.initTaxonomiesSelects();
							const index = yithWcbep.enabledColumns.indexOf( response.oldKey );
							if ( -1 !== index ) {
								yithWcbep.enabledColumns.splice( index, 1 );
							}
							yithWcbep.enabledColumns.push( response.newKey );
							enableFields.searchField();
						}
						// TODO: error showing.
					},
					complete: function () {
						unblock( fieldClone.find( '.yith-wcbep-enable-fields__edit-field' ) );
					},
				} );
			}
		}

		/* =================
		 *  G E T T E R S
		 */

		getTaxonomy() {
			return this.getProp( 'taxonomy' );
		}

		getLabel() {
			return this.getProp( 'label' );
		}

		getChanges() {
			const field      = this,
				  fieldClone = $( '#' + field.$element.prop( 'id' ) + '.yith-wcbep-enable-fields__field-clone' );
			let changes      = {};
			if ( fieldClone.length ) {
				const taxonomy = fieldClone.find( '#yith-wcbep-edit-field-taxonomy' );
				if ( taxonomy !== field.getTaxonomy() ) {
					changes.taxonomy = taxonomy;
				}
			}

			return changes;
		}
	}

	const block        = element => {
			  const blockArgs = {
				  message   : '',
				  overlayCSS: {backgroundColor: '#FFFFFF', opacity: 0.8, cursor: 'wait'},
			  };
			  element.block( blockArgs );
		  },
		  unblock      = element => element.unblock(),
		  getField     = element => {
			  let field = new Field( element );
			  if ( field.$element.length ) {
				  switch ( field.getKind() ) {
					  case 'custom_field':
						  field = new CustomField( element );
						  break;
					  case 'taxonomy':
						  field = new TaxonomyField( element );
						  break;
				  }
			  }
			  return field.$element.length ? field : false;
		  },
		  enableFields = {
			  modal                                       : null,
			  templates                                   : {
				  field    : {
					  customField: wp.template( 'yith-wcbep-enable-fields-custom-field' ),
					  taxonomy   : wp.template( 'yith-wcbep-enable-fields-taxonomy-field' ),
				  },
				  editField: wp.template( 'yith-wcbep-enable-fields-edit-field' ),
			  },
			  selectors                                   : {
				  enableFieldsButton    : '#yith-wcbep-enable-fields, #yith-wcbep-empty-state-due-disabled-fields .yith-wcbep-empty-state-enable-fields',
				  notDeletingField      : '.yith-wcbep-enable-fields__field:not(.yith-wcbep-enable-fields__field--deleting)',
				  toggleAllFields       : '#yith-wcbep-enable-fields-toggle-all',
				  searchField           : '#yith-wcbep-enable-fields-search-column',
				  emptySearch           : '.yith-wcbep-enable-fields__search-column__icon',
				  fieldsList            : '.yith-wcbep-enable-fields__list',
				  fieldsSectionTitle    : {
					  customFields: '.yith-wcbep-enable-fields__kind-custom_field',
					  taxonomies  : '.yith-wcbep-enable-fields__kind-taxonomy',
				  },
				  allFieldsCheckboxes   : '.yith-wcbep-enable-fields__list .yith-wcbep-enable-fields__field:visible input[type="checkbox"]',
				  editFieldButton       : '.yith-wcbep-enable-fields__field-action--edit',
				  deleteFieldButton     : '.yith-wcbep-enable-fields__field-action--delete',
				  addCustomColumnSection: {
					  form                  : 'form.yith-wcbep-add-custom-fields-container',
					  type                  : '#yith-wcbep-custom-field-type',
					  customFieldID         : '#yith-wcbep-custom-field-id',
					  label                 : '#yith-wcbep-custom-field-label',
					  fieldsTable           : '.yith-wcbep-add-custom-fields__options',
					  customColumnKindSelect: '#yith-wcbep-custom-column-kind',
					  addFieldButton        : '#yith-wcbep-add-field-button',
					  taxonomy              : '#yith-wcbep-taxonomy-field',
				  },
				  fieldCloneInputs      : '.yith-wcbep-enable-fields__field-clone input, .yith-wcbep-enable-fields__field-clone select',
				  fieldCloneCheckbox    : '.yith-wcbep-enable-fields__field-clone input[type="checkbox"].yith-wcbep-enable-fields__field-input',
				  cancelModalButton     : '.yith-wcbep-enable-fields-modal-button-cancel',
				  saveModalButton       : '.yith-wcbep-enable-fields-modal-button-save',
			  },
			  fields                                      : {
				  enableFieldsButton: $( '#yith-wcbep-enable-fields' ),
			  },
			  init                                        : function () {
				  $( document ).on( 'click', enableFields.selectors.enableFieldsButton, enableFields.openModal );
				  $( document ).on( 'change', enableFields.selectors.fieldCloneInputs, enableFields.handleFieldsCloneChanges );
				  $( document ).on( 'change', enableFields.selectors.fieldCloneCheckbox, enableFields.handleFieldsCloneEnablingChanges );
				  $( document ).on( 'change', enableFields.selectors.addCustomColumnSection.customColumnKindSelect, enableFields.handleFieldsVisibilityOnAddCustomColumnModal );
				  $( document ).on( 'change', enableFields.selectors.toggleAllFields, enableFields.toggleAllFields );
				  $( document ).on( 'change', enableFields.selectors.allFieldsCheckboxes, enableFields.handleToggleAllFieldsCheckboxStatus );
				  $( document ).on( 'change keyup', enableFields.selectors.searchField, enableFields.searchField );
				  $( document ).on( 'click', enableFields.selectors.emptySearch, enableFields.emptySearch );
			  },
			  initTaxonomiesSelects                       : () => {
				  if ( enableFields?.modal ) {
					  const $modal           = enableFields.modal.elements.content,
							$editFieldselect = $modal.find( '.yith-wcbep-enable-fields__edit-field #yith-wcbep-edit-field-taxonomy' );
					  if ( $editFieldselect.length ) {
						  field = getField( $editFieldselect );
						  $editFieldselect.find( 'option' ).toArray().forEach( option => $( option ).prop( 'disabled', option.value !== field.getTaxonomy() && yithWcbep.columnList.hasOwnProperty( 'yith_wcbep_tf_' + option.value ) ) );
					  }
					  const $addFieldSelect = $modal.find( 'form.yith-wcbep-add-custom-fields-container select#yith-wcbep-taxonomy-field' );
					  if ( $addFieldSelect.length ) {
						  $addFieldSelect.find( 'option' ).toArray().forEach( option => $( option ).prop( 'disabled', yithWcbep.columnList.hasOwnProperty( 'yith_wcbep_tf_' + option.value ) ) );
						  if ( $addFieldSelect.is( '.enhanced' ) ) {
							  $addFieldSelect.select2( 'destroy' ).removeClass( 'enhanced' );
							  enableFields.initSelect2();
						  }
					  }
				  }
			  },
			  initSelect2                                 : () => {
				  $( document.body ).trigger( 'wc-enhanced-select-init' );
			  },
			  openModal                                   : function () {
				  const modalOptions = {...yithWcbep.modals.enableFields, onClose: enableFields.resetModal};
				  enableFields.modal = yith.ui.modal( modalOptions );
				  const $modal       = $( enableFields.modal.elements.main ),
						$fields      = $modal.find( '.yith-wcbep-enable-fields__field' );
				  enableFields.closeEditFieldOption();
				  enableFields.initTaxonomiesSelects();
				  enableFields.emptySearch();
				  $fields.toArray().map( field => {
					  const $input = $( field ).find( 'input' );
					  $input.attr( 'checked', yithWcbep.enabledColumns.indexOf( $input.attr( 'id' ).substring( 23 ) ) !== -1 );
				  } );
				  $( $fields.toArray().filter( field => $( field ).hasClass( 'yith-wcbep-enable-fields__field--new' ) ) ).removeClass( 'yith-wcbep-enable-fields__field--new' );

				  enableFields.handleToggleAllFieldsCheckboxStatus();
				  enableFields.handleSectionTitlesVisibility();

				  $( enableFields.selectors.addCustomColumnSection.form ).on( 'submit', enableFields.addField );
				  $( enableFields.selectors.addCustomColumnSection.customFieldID ).on( 'change keyup invalid', enableFields.handleInvalidForm );

				  $( enableFields.selectors.addCustomColumnSection.customColumnKindSelect ).trigger( 'change' );

				  enableFields.initSelect2();

				  $modal.on( 'click', enableFields.selectors.cancelModalButton, enableFields.closeModal );
				  $modal.on( 'click', enableFields.selectors.saveModalButton, enableFields.saveModal );
				  $modal.on( 'click', enableFields.selectors.editFieldButton, enableFields.openEditFieldOptions );
				  $modal.on( 'click', enableFields.selectors.notDeletingField + ' ' + enableFields.selectors.deleteFieldButton, enableFields.deleteField );
				  $modal.find( enableFields.selectors.fieldsList ).on( 'scroll', enableFields.handleFieldListScroll );
			  },
			  updateModalContent                          : function () {
				  if ( enableFields.modal ) {
					  const $currentModalContent = $( enableFields.modal.elements.content ),
							$storedModalContent  = $( '<div>' + yithWcbep.modals.enableFields.content + '</div>' );
					  $currentModalContent.find( 'input[type="checkbox"]' ).map( (index, el) => $( el ).attr( 'checked', $( el ).prop( 'checked' ) ) );
					  $storedModalContent.find( '.yith-wcbep-enable-fields__list-container' ).html( $currentModalContent.find( '.yith-wcbep-enable-fields__list-container' ).html() );
					  yithWcbep.modals.enableFields.content = $storedModalContent.html();
				  }
			  },
			  resetModal                                  : () => {
				  enableFields.updateModalContent();
				  enableFields.modal = null;
			  },
			  closeModal                                  : update => {
				  if ( enableFields.modal ) {
					  enableFields.modal.close();
				  }
				  enableFields.resetModal();
			  },
			  saveModal                                   : () => {
				  var enabledFieldIDs = $( enableFields.modal.elements.content ).find( 'input.yith-wcbep-enable-fields__field-input:checked' ).map( (index, el) => el.id.replace( 'yith-wcbep-show-column-', '' ) ).toArray(),
					  success         = false;
				  block( $( enableFields.modal.elements.main ) );
				  $.ajax( {
					  data    : {
						  enabled_field_ids: enabledFieldIDs.length ? enabledFieldIDs : false,
						  action           : yithWcbep.actions.saveEnabledFields,
						  security         : yithWcbep.security.saveEnabledFields,
					  },
					  type    : 'POST',
					  dataType: 'json',
					  url     : yithWcbep.ajaxurl,
					  success : function (response) {
						  if ( response && 'success' === response?.success && enableFields?.modal ) {
							  unblock( $( enableFields.modal.elements.main ) );
							  yithWcbep.enabledColumns = enableFields.modal.elements.content.find( 'input.yith-wcbep-enable-fields__field-input:checked' ).map( (i, item) => item.value ).toArray();

							  const $columnVisibilityButton = $( 'button.yith-wcbep-products-table-column-visibility' );
							  $columnVisibilityButton.toggleClass( 'yith-wcbep-button--disabled', ! yithWcbep.enabledColumns.length );

							  enableFields.closeModal( true );
							  $( document ).trigger( 'yith-wcbep-load-table' );
							  success = true;
						  }
					  },
					  complete: function () {
						  if ( ! success ) {
							  unblock( $( enableFields.modal.elements.main ) );
						  }
					  },
				  } );
			  },
			  openEditFieldOptions                        : function () {
				  enableFields.closeEditFieldOption();
				  const $field           = $( this ).closest( '.yith-wcbep-enable-fields__field' ),
						$fieldClone      = $field.clone().addClass( 'yith-wcbep-enable-fields__field-clone' ).removeClass( 'yith-wcbep-enable-fields__field--new' ),
						$editField       = $( enableFields.templates.editField() ),
						$list            = $( enableFields.selectors.fieldsList ),
						leftSpace        = $field[ 0 ].getBoundingClientRect().left - $list[ 0 ].getBoundingClientRect().left + $field.outerWidth(),
						rightSpace       = $list.outerWidth() - ($field[ 0 ].getBoundingClientRect().left - $list[ 0 ].getBoundingClientRect().left),
						editOptionsStyle = {
							position : 'absolute',
							'z-index': 10,
							top      : Math.round( $field[ 0 ].getBoundingClientRect().top - $list[ 0 ].getBoundingClientRect().top + $list[ 0 ].scrollTop - 1 ) + 'px',
						},
						fieldObject      = getField( $field );

				  if ( leftSpace > rightSpace ) {
					  editOptionsStyle.right = Math.max( 0, Math.round( $list.outerWidth() - 20 - leftSpace ) );
					  editOptionsStyle.left  = 'auto';
				  } else {
					  editOptionsStyle.left  = Math.max( 0, (Math.round( $field[ 0 ].getBoundingClientRect().left - $list[ 0 ].getBoundingClientRect().left )) );
					  editOptionsStyle.right = 'auto';
				  }

				  const fieldType = $field.find( 'input.yith-wcbep-enable-field-kind' ).val();
				  switch ( fieldType ) {
					  case 'custom_field':
						  $editField.find( '#yith-wcbep-edit-field-taxonomy' ).closest( 'tr' ).remove();
						  $editField.find( '#yith-wcbep-edit-field-custom-field-id' ).val( fieldObject.getId() );
						  $editField.find( '#yith-wcbep-edit-field-custom-field-type' ).val( fieldObject.getType() );
						  $editField.find( '#yith-wcbep-edit-field-custom-field-label' ).val( fieldObject.getLabel() );
						  break;
					  case 'taxonomy':
						  $editField.find( '#yith-wcbep-edit-field-taxonomy' ).val( fieldObject.getTaxonomy() );
						  $editField.find( '#yith-wcbep-edit-field-custom-field-id' ).closest( 'tr' ).remove();
						  $editField.find( '#yith-wcbep-edit-field-custom-field-type' ).closest( 'tr' ).remove();
						  $editField.find( '#yith-wcbep-edit-field-custom-field-label' ).closest( 'tr' ).remove();
						  break;
				  }

				  $list.append( $fieldClone );
				  $fieldClone.css( editOptionsStyle );
				  $fieldClone.append( $editField );

				  if ( 'taxonomy' === fieldType ) {
					  enableFields.initTaxonomiesSelects();
				  }

				  $editField.slideDown( 'slow' );
				  $( document.body ).trigger( 'wc-enhanced-select-init' );
				  $fieldClone.on( 'click', '.yith-wcbep-enable-fields__edit-field__cancel-button', enableFields.closeEditFieldOption );
				  $fieldClone.on( 'click', '.yith-wcbep-enable-fields__edit-field__save-button', enableFields.saveFieldChanges );
			  },
			  deleteField                                 : function () {
				  const field = getField( $( this ) );
				  if ( field ) {
					  field.delete();
				  }
			  },
			  saveFieldChanges                            : function () {
				  const field      = getField( this ),
						$editPanel = $( this ).closest( '.yith-wcbep-enable-fields__edit-field' );
				  switch ( field.getKind() ) {
					  case 'custom_field':
						  const newId    = $editPanel.find( '#yith-wcbep-edit-field-custom-field-id' ).val(),
								newType  = $editPanel.find( '#yith-wcbep-edit-field-custom-field-type' ).val(),
								newLabel = $editPanel.find( '#yith-wcbep-edit-field-custom-field-label' ).val();
						  field.update( {id: field.getId(), newId, newType, newLabel} );
						  break;
					  case 'taxonomy':
						  const taxonomy = $editPanel.find( '#yith-wcbep-edit-field-taxonomy' ).val();
						  field.update( {id: field.getTaxonomy(), taxonomy} );
						  break;
				  }
			  },
			  closeEditFieldOption                        : () => $( '.yith-wcbep-enable-fields__field-clone' ).remove(),
			  addField                                    : function (e) {
				  e.preventDefault();
				  const kind     = $( enableFields.selectors.addCustomColumnSection.customColumnKindSelect ).val(),
						key      = `add${'taxonomy' === kind ? 'Taxonomy' : 'CustomField'}Column`,
						action   = yithWcbep.actions[ key ],
						security = yithWcbep.security[ key ],
						data     = {kind, action, security},
						$form    = $( enableFields.selectors.addCustomColumnSection.form );
				  switch ( kind ) {
					  case 'custom-field':
						  data.field_id = $( enableFields.selectors.addCustomColumnSection.customFieldID ).val();
						  data.type     = $( enableFields.selectors.addCustomColumnSection.type ).val();
						  data.label    = $( enableFields.selectors.addCustomColumnSection.label ).val() ?? data.field_id;
						  break;
					  case 'taxonomy':
						  data.taxonomy = $( enableFields.selectors.addCustomColumnSection.taxonomy ).val();
				  }
				  block( $form );
				  $.ajax( {
					  data,
					  type    : 'POST',
					  dataType: 'json',
					  url     : yithWcbep.ajaxurl,
					  success : function (response) {
						  if ( response && 'success' === response?.success ) {
							  yithWcbep.columnList[ response.data?.fieldKey ] = response.data?.fieldLabel;
							  yithWcbep.enabledColumns.push( response.data?.fieldKey );

							  $form.trigger( 'reset' );
							  $form.find( 'input:not(#yith-wcbep-custom-field-id),select' ).trigger( 'change' );

							  const list = $( '.yith-wcbep-enable-fields__list' );
							  if ( list.length ) {
								  response.data.fieldClasses += ' yith-wcbep-enable-fields__field--new';

								  let $newField,
									  beforeElement;

								  switch ( kind ) {
									  case 'custom-field':
										  yithWcbep.customFields[ response.data.fieldKey ] = {
											  id   : response.data.fieldId,
											  type : response.data.fieldType,
											  label: response.data.fieldLabel,
										  };

										  $newField        = $( enableFields.templates.field.customField( response.data ) );
										  beforeElement    = $( enableFields.selectors.fieldsSectionTitle.customFields );
										  var customFields = $( enableFields.modal.elements.main ).find( '.yith-wcbep-enable-fields__custom-field' );
										  if ( customFields.length ) {
											  beforeElement = customFields.last();
										  }
										  break;
									  case 'taxonomy':
										  $newField          = $( enableFields.templates.field.taxonomy( response.data ) );
										  beforeElement      = $( enableFields.selectors.fieldsSectionTitle.taxonomies );
										  var taxonomyFields = $( enableFields.modal.elements.main ).find( '.yith-wcbep-enable-fields__taxonomy-field' );
										  if ( taxonomyFields.length ) {
											  beforeElement = taxonomyFields.last();
										  }
										  enableFields.initTaxonomiesSelects();
										  break;
								  }
								  beforeElement.after( $newField );
								  enableFields.searchField();
								  enableFields.handleSectionTitlesVisibility();
								  const scrollTop      = list.scrollTop(),
										scrollHeight   = list[ 0 ].scrollHeight - list.innerHeight(),
										removeNewClass = () => setTimeout( () => $newField.removeClass( 'yith-wcbep-enable-fields__field--new' ), 2500 );
								  if ( 'none' !== $newField.css( 'display' ) ) {
									  list.animate(
										  {scrollTop: scrollHeight},
										  Math.abs( 500 * ((scrollTop - scrollHeight) / scrollHeight) ),
										  'swing',
										  removeNewClass,
									  );
								  } else {
									  removeNewClass();
								  }
							  }
						  } else {
							  // TODO: error showing.
						  }
					  },
					  complete: function () {
						  unblock( $form );
					  },
				  } );
			  },
			  handleFieldsCloneChanges                    : function () {
				  const field = getField( $( this ) );
				  if ( field.getChanges() ) {

				  }
			  },
			  handleFieldsCloneEnablingChanges            : function () {
				  const fieldCloneInput = $( this ),
						field           = getField( fieldCloneInput );
				  field.$element.find( 'input[type="checkbox"].yith-wcbep-enable-fields__field-input' ).prop( 'checked', fieldCloneInput.prop( 'checked' ) );
			  },
			  handleFieldsVisibilityOnAddCustomColumnModal: function () {
				  var value       = $( this ).val(),
					  fieldsTable = $( enableFields.selectors.addCustomColumnSection.fieldsTable );
				  fieldsTable.find( 'td' ).closest( 'tr:not(.yith-wcbep-add-custom-fields__kind-select-row)' ).hide();
				  fieldsTable.find( 'tr.yith-wcbep-add-custom-fields__show-if-' + value ).show();
				  fieldsTable.find( 'input[required], select[required]' ).prop( 'required', false );
				  switch ( value ) {
					  case 'custom-field':
						  $( '#yith-wcbep-custom-field-id' ).prop( 'required', true );
						  break;
					  case 'taxonomy':
						  $( '#yith-wcbep-taxonomy-field' ).prop( 'required', true );
						  break;
				  }
			  },
			  toggleAllFields                             : function () {
				  $( enableFields.selectors.allFieldsCheckboxes ).prop( 'checked', $( this ).is( ':checked' ) );
			  },
			  handleToggleAllFieldsCheckboxStatus         : function () {
				  $( enableFields.selectors.toggleAllFields ).prop( 'checked', ! $( enableFields.selectors.allFieldsCheckboxes + ':not(:checked)' ).length );
			  },
			  handleInvalidForm                           : function () {
				  var $input = $( this ),
					  color  = $input.is( ':valid' ) ? '' : '#ea0034';
				  $input.css( {
					  'border-color': color,
					  color,
				  } );
			  },
			  handleFieldListScroll                       : function () {
				  const $list      = $( this );
				  let boxShadowCSS = [];
				  if ( $list.length ) {
					  const shadow = {
						  top   : !! $list[ 0 ].scrollTop,
						  bottom: $list[ 0 ].scrollTop + $list[ 0 ].offsetHeight + 5 < $list[ 0 ].scrollHeight,
					  };
					  if ( shadow.top !== enableFields.handleFieldListScroll.boxShadow?.top || shadow.bottom !== enableFields.handleFieldListScroll.boxShadow?.bottom ) {
						  boxShadowCSS.push( 'inset 0px 100px 30px -90px rgba(178, 198, 210,' + (0.5 * +shadow.top) + ')' );
						  boxShadowCSS.push( 'inset 0px -100px 30px -90px rgba(178, 198, 210,' + (0.5 * +shadow.bottom) + ')' );
						  $list.parent().find( '.yith-wcbep-enable-fields__list-wrapper__shadow' ).css( 'box-shadow', boxShadowCSS.join( ', ' ) );
						  enableFields.handleFieldListScroll.boxShadow = shadow;
					  }
				  }
			  },
			  handleSectionTitlesVisibility               : function () {
				  if ( enableFields.modal ) {
					  var $modal            = $( enableFields.modal.elements.main ),
						  customFieldsTitle = $modal.find( enableFields.selectors.fieldsSectionTitle.customFields ),
						  taxonomiesTitle   = $modal.find( enableFields.selectors.fieldsSectionTitle.taxonomies );
					  if ( $modal.find( '.yith-wcbep-enable-fields__taxonomy-field:visible' ).length ) {
						  taxonomiesTitle.show();
					  } else {
						  taxonomiesTitle.hide();
					  }
					  if ( $modal.find( '.yith-wcbep-enable-fields__custom-field:visible' ).length ) {
						  customFieldsTitle.show();
					  } else {
						  customFieldsTitle.hide();
					  }
				  }
			  },
			  emptySearch                                 : function () {
				  $( enableFields.selectors.searchField ).val( '' ).trigger( 'change' );
			  },
			  searchField                                 : function () {
				  let removed         = 0;
				  const $input        = $( enableFields.selectors.searchField ),
						searchFor     = $input.val().toLowerCase(),
						listContainer = $input.closest( '.yith-wcbep-enable-fields__list-container' ).find( '.yith-wcbep-enable-fields__list' ),
						fields        = listContainer.find( '.yith-wcbep-enable-fields__field' );

				  fields.each( (index, field) => {
					  const $field = $( field ),
							match  = $field.find( 'input.yith-wcbep-enable-fields__field-input' ).val().toLowerCase().indexOf( searchFor ) !== -1 || $field.find( '.yith-wcbep-enable-fields__field-label' ).text().toLowerCase().indexOf( searchFor ) !== -1;
					  $field.css( 'display', match ? 'flex' : 'none' );
					  ! match && removed++;
				  } );

				  enableFields.handleSectionTitlesVisibility();
				  enableFields.handleToggleAllFieldsCheckboxStatus();
				  $( enableFields.selectors.fieldsList ).trigger( 'scroll' );

				  listContainer.toggleClass( 'yith-wcbep-enable-fields__list--empty-search', removed === fields.length );
			  },
		  };

	enableFields.init();
	$( document ).on( 'change', 'input, textarea, select', function () { window.onbeforeunload = function () { }; } );

	// TODO: remove these lines [DEV]
	// enableFields.openModal();

} );
