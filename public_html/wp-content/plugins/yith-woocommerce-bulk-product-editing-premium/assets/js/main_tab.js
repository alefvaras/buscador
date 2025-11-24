/* global yithWcbep */

const priceStringToNumber = ( price ) => {
	return Math.abs( +( 'string' === typeof price ? price.replace( yithWcbep.wcDecimalSeparator, '.' ) : price ) );
};

jQuery( function ( $ ) {
	let moves                = [],
		currentMove          = 0,
		getTextEditorContent = textArea => wp.editor.getContent( textArea.attr( 'id' ) ),
		isNumeric            = num => !isNaN( num ),
		areEquals            = function ( a, b, type ) {
			let equals = a === b;
			if ( 'object' === typeof a && 'object' === typeof b ) {
				if ( 'category' === type ) {
					a = Object.keys( a );
					b = Object.keys( b );
				}
				equals = JSON.stringify( a ) === JSON.stringify( b );
			} else if ( ( 'string' === typeof a && isNumeric( b ) ) || ( isNumeric( a ) && 'string' === typeof b ) ) {
				equals = a.toString() === b.toString();
			} else if ( ( yithBulk.isJsonString( a ) && 'object' === typeof b ) || yithBulk.isJsonString( b ) && 'object' === typeof a ) {
				a      = yithBulk.isJsonString( a ) ? a : JSON.stringify( a );
				b      = yithBulk.isJsonString( b ) ? b : JSON.stringify( b );
				equals = a === b;
			} else if ( yithBulk.inArray( a, ['[]', '{}'] ) && yithBulk.inArray( b, ['[]', '{}'] ) ) {
				equals = true;
			}

			return equals;
		},
		triggerSelect2Init   = function () {
			$( document.body ).trigger( 'yith-framework-enhanced-select-init' );
			$( document.body ).trigger( 'wc-enhanced-select-init' );
		},
		block                = function ( element ) {
			if ( !element.find( '.blockUI' ).length ) {
				var blockArgs = {
					message   : '',
					overlayCSS: { backgroundColor: '#ffffff', opacity: 0.8, cursor: 'wait' }
				};
				element.block( blockArgs );
			}
		},
		getFormattedDate     = date => {
			return date instanceof Date && !isNaN( date ) ? `${date.getUTCFullYear()}-${( date.getUTCMonth() < 9 ? '0' : '' ) + ( date.getUTCMonth() + 1 )}-${( date.getUTCDate() < 10 ? '0' : '' ) + date.getUTCDate()}` : '';
		},
		unblock              = element => element.unblock(),
		productsTable        = {
			selectors                             : {
				table                            : 'table.yith_wcbep_products',
				tableCell                        : 'table.yith_wcbep_products td',
				tableCellExceptOnOff             : 'table.yith_wcbep_products td:not(.yith-wcbep-column-onoff-type, .yith-wcbep-column-not-editable)',
				tableCellOnOff                   : 'table.yith_wcbep_products td.yith-wcbep-column-onoff-type',
				tableHeaderAndFooterSortableLinks: 'table.yith_wcbep_products thead th a, table.yith_wcbep_products tfoot th a',
				floatingField                    : {
					container          : '.yith-wcbep-floating-editing-field-container',
					cancelButton       : '.yith-wcbep-floating-editing-field-container .yith-wcbep-floating-editing-field__cancel-button',
					applyButton        : '.yith-wcbep-floating-editing-field-container .yith-wcbep-floating-editing-field__apply-button',
					downloadableFile   : {
						container   : '.yith-wcbep-downloadable-files-container',
						addButton   : '.yith-wcbep-floating-editing-field-container .yith-wcbep-downloadable-files-container span.yith-wcbep-add-downloadable-file',
						removeButton: '.yith-wcbep-floating-editing-field-container .yith-wcbep-downloadable-files-container span.yith-wcbep-downloadable-file-trash',
						changeButton: '.yith-wcbep-floating-editing-field-container .yith-wcbep-downloadable-files-container span.yith-wcbep-downloadable-file-upload'
					},
					productImage       : {
						container         : '.yith-wcbep-product-image-container',
						containerWithImage: '.yith-wcbep-product-image-container--has-image',
						addButton         : '.yith-wcbep-product-image-container .yith-wcbep-product-image__action-add',
						editButton        : '.yith-wcbep-product-image-container--has-image .yith-wcbep-product-image__action-edit',
						removeButton      : '.yith-wcbep-product-image-container--has-image .yith-wcbep-product-image__action-remove'
					},
					productImageGallery: {
						container   : '.yith-wcbep-product-image-gallery-container',
						addButton   : '.yith-wcbep-product-image-gallery-container .yith-wcbep-product-image-gallery__add-image',
						removeButton: '.yith-wcbep-product-image-gallery-container .yith-wcbep-product-image-gallery-element__remove'
					}
				},
				saveButton                       : 'button.yith-wcbep-products-table-save:not(.yith-wcbep-products-table-save--saving)',
				undoButton                       : 'button.yith-wcbep-products-table-undo',
				redoButton                       : 'button.yith-wcbep-products-table-redo'
			},
			templates                             : {
				getFloatingFieldTemplate: type => $( '#tmpl-yith-wcbep-floating-editing-field-' + type ).length ? wp.template( 'yith-wcbep-floating-editing-field-' + type ) : false,
				floatingFieldsContainer : wp.template( 'yith-wcbep-floating-edit-field' )
			},
			floatingField                         : null,
			init                                  : function () {
				$( document ).on( 'click', productsTable.maybeCloseProductFieldEditing );
				$( document ).on( 'keydown', productsTable.maybeApplyProductFieldEditingChanges );

				$( document ).on( 'yith-wcbep-load-table', productsTable.handleTableLoadingTrigger );
				$( document ).on( 'yith-wcbep-update-progress-bar', productsTable.handleUpdateProgressBarTrigger );

				$( document ).on( 'click', productsTable.selectors.tableHeaderAndFooterSortableLinks, productsTable.handleColumnSorting );
				$( document ).on( 'click', productsTable.selectors.floatingField.cancelButton, productsTable.closeProductFieldEditing );
				$( document ).on( 'click', productsTable.selectors.floatingField.applyButton, productsTable.applyProductFieldEditing );
				$( document ).on( 'click', productsTable.selectors.tableCellExceptOnOff, productsTable.editProductField );
				$( document ).on( 'change', productsTable.selectors.tableCellOnOff, productsTable.handleOnOffFieldChange );
				$( document ).on( 'click', productsTable.selectors.saveButton, productsTable.saveChanges );
				$( document ).on( 'click', productsTable.selectors.undoButton, productsTable.undo );
				$( document ).on( 'click', productsTable.selectors.redoButton, productsTable.redo );
				$( productsTable.selectors.table ).on( 'scroll', productsTable.handleTableScroll );

				productsTable.makeItResizable();
				productsTable.initNotEditableColumnsTollTips();

				// Pagination handlers
				$( document ).on( 'click', '#yith-wcbep-products-wp-list .tablenav .pagination-links a', productsTable.handlePaginationChange );

				// Floating fields handlers
				$( document ).on( 'click', productsTable.selectors.floatingField.downloadableFile.removeButton, productsTable.removeDownloadableFile );
				$( document ).on( 'click', productsTable.selectors.floatingField.downloadableFile.addButton, productsTable.addFieldOnDownloadableFiles );
				$( document ).on( 'click', productsTable.selectors.floatingField.downloadableFile.changeButton, productsTable.selectDownloadableFileFromMediaLibrary );

				$( document ).on( 'click', productsTable.selectors.floatingField.productImage.addButton, productsTable.addProductImage );
				$( document ).on( 'click', productsTable.selectors.floatingField.productImage.editButton, productsTable.changeProductImage );
				$( document ).on( 'click', productsTable.selectors.floatingField.productImage.removeButton, productsTable.removeProductImage );

				$( document ).on( 'click', productsTable.selectors.floatingField.productImageGallery.addButton, productsTable.addProductImageToGallery );
				$( document ).on( 'click', productsTable.selectors.floatingField.productImageGallery.removeButton, productsTable.removeProductImageFromGallery );

				$( document ).on( 'change keyup blur', 'input#yith-wcbep-products-table-products-per-page', productsTable.handleProductsPerPageChanges );
				$( document ).on( 'change keyup blur', 'input#yith-wcbep-search-for-a-product', productsTable.handleProductsNameSearchChange );

				productsTable.initToolTips();
				productsTable.initExportForms();
			},
			initToolTips                          : () => {
				$( '.yith-wcbep-products-table-button__tip,.yith-wcbep-products-table-button__tip' ).tipTip( {
					attribute: 'data-tip',
					fadeIn   : 150,
					fadeOut  : 150,
					delay    : 700
				} );
			},
			initExportForms                       : () => {
				const exportForm = $( 'form.woocommerce-exporter' );
				exportForm.length && exportForm.wc_product_export_form();
			},
			handleColumnSorting                   : function () {
				const $table        = productsTable.getTable(),
					$column       = $( this ).closest( 'th' ),
					columnSorting = $column.hasClass( 'asc' ) ? 'asc' : 'desc';
				$table.find( `thead th.sorted:not(#${$column.attr( 'id' )})` ).removeClass( 'sorted' ).addClass( 'sortable' );
				if ( $column.hasClass( 'sortable' ) ) {
					$column.removeClass( 'sortable' ).addClass( 'sorted' );
				}
				$column.removeClass( columnSorting ).addClass( 'asc' === columnSorting ? 'desc' : 'asc' );
				$( document ).trigger( 'yith-wcbep-load-table' );
				return false;
			},
			makeItResizable                       : function () {
				var table        = $( '.yith-wcbep-products-table-container table.yith_wcbep_products' ),
					tableRows    = table.find( 'tr' ),
					tableColumns = table.find( 'th:not(.column-cb), td' );

				tableRows.resizable( { handles: 's' } );
				tableColumns.resizable( {
					handles: 'e',
					resize : productsTable.columnResizingHandler,
					stop   : productsTable.maybeUpdateColumnsWidth
				} );
			},
			initNotEditableColumnsTollTips        : () => $( '.yith-wcbep-column-not-editable' ).tipTip( {
				attribute: 'data-not-editable-message',
				fadeIn   : 150,
				fadeOut  : 150,
				delay    : 700
			} ),
			maybeUpdateColumnsWidth               : () => {
				productsTable.maybeUpdateColumnsWidth.lastResize = Date.now();
				setTimeout( () => {
					if ( !productsTable.maybeUpdateColumnsWidth.hasOwnProperty( 'lastResize' ) || Date.now() - productsTable.maybeUpdateColumnsWidth.lastResize >= 2000 ) {
						const $table        = productsTable.getTable(),
							$columns      = $table.find( 'thead th' ),
							columns_width = $columns.toArray().filter( column => 'none' !== $( column ).css( 'display' ) ).map( column => {
								return { id: column.id, width: $( column ).width() };
							} );
						Object.keys( columns_width ).forEach( index => document.documentElement.style.setProperty( '--yith-wcbep-column-' + columns_width[ index ].id + '-width', columns_width[ index ].width + 'px' ) );
						$.ajax( {
							data   : {
								action  : yithWcbep.actions.updateColumnsWidth,
								security: yithWcbep.security.updateColumnsWidth,
								columns_width
							},
							type   : 'POST',
							url    : yithWcbep.ajaxurl,
							success: response => {
								if ( 'success' !== response?.success ) {
									console.warn( response );
								}
							}
						} );

					}
				}, 2000 );
			},
			getTable                              : () => {
				return $( productsTable.selectors.table );
			},
			getTableData                          : () => {
				const $table  = productsTable.getTable();
				let tableData = {};

				// Sorting.
				const sortingColumn = $table.find( 'thead th.sorted.asc, thead th.sorted.desc' );
				if ( sortingColumn.length ) {
					tableData.order_by = sortingColumn.attr( 'id' );
					tableData.order    = sortingColumn.hasClass( 'asc' ) ? 'asc' : 'desc';
				}

				tableData.table_view     = $( 'select#yith-wcbep-table-view' ).val();
				tableData.posts_per_page = $( 'input#yith-wcbep-products-table-products-per-page' ).val();

				const searchProductName = $( '#yith-wcbep-search-for-a-product' ).val();
				if ( searchProductName ) {
					tableData.product_name = searchProductName;
				}

				const currentPage = $( '#yith-wcbep-products-table-current-page' ).val();
				if ( 1 !== currentPage ) {
					tableData.paged = currentPage;
				}

				return tableData;
			},
			handleTableLoadingTrigger             : ( e, data ) => productsTable.loadTable( data ),
			loadTable                             : function ( data ) {
				const table          = $( productsTable.selectors.table ),
					tableContainer = table.closest( '#yith-wcbep-products-wp-list' ),
					buildTable     = tableData => {
						if ( tableData ) {
							productsTable.updateProgressBar( data && data.hasOwnProperty( 'endProgess' ) ? data.endProgess : 100 );
							const selectedIDs = productsTable.getSelectedProducts(),
								tableBody   = table.find( 'tbody' );

							table.closest( '#yith-wcbep-products-wp-list ' )[ 0 ].classList = tableData.container_classes;
							productsTable.closeProductFieldEditing();

							table.find( 'thead, tfoot' ).html( $( '<tr></tr>' ).html( tableData?.column_headers ) );

							tableBody.html( tableData?.rows );
							tableBody.find( selectedIDs.map( productId => 'tr.yith-wcbep-product-' + productId ).join( ', ' ) ).find( 'th.check-column input' ).trigger( 'click' );

							const $tableNav = table.closest( '#yith-wcbep-products-wp-list' ).find( '.tablenav.bottom .tablenav-pages' );
							if ( tableData?.total_pages > 1 ) {
								$tableNav.removeClass( 'one-page' );
							} else {
								$tableNav.addClass( 'one-page' );
							}
							$tableNav.html( $( tableData.pagination.bottom ).html() );
							productsTable.makeItResizable();
							productsTable.initNotEditableColumnsTollTips();

							productsTable.applyMove( moves.slice( 0, currentMove ), 'redo' );

							productsTable.handleUndoRedoUsability();
							bulkEditing.handleBulkButtonsStatus();

							$( '#yith-wcbep-products-table-total-pages' ).val( tableData?.total_pages ? tableData.total_pages : 1 );
							if ( !tableData?.paged ) {
								$( '#yith-wcbep-products-table-current-page' ).val( 1 );
							} else {
								$( '#yith-wcbep-products-table-current-page' ).val( Math.abs( tableData?.paged ) );
							}

							if ( data?.newProductID ) {
								const $newProduct = table.find( 'tr.yith-wcbep-product-' + data.newProductID );
								if ( $newProduct.length ) {
									$newProduct.addClass( 'yith-wcbep-new-product-row' );
									setTimeout( () => $newProduct.removeClass( 'yith-wcbep-new-product-row' ), 15000 );
								}
							}
						} else {
							productsTable.updateProgressBar( -1 );
						}
						bulkEditing.productSelectionChange();
					};
				if ( !yithWcbep.enabledColumns.filter( column => !yithBulk.inArray( column, yithWcbep.alwaysVisibleColumns ) ).length ) {
					tableContainer.addClass( 'yith-wcbep-empty-state-due-disabled-fields' );
					tableContainer.removeClass( 'yith-wcbep-empty-state-due-filters yith-wcbep-empty-state-zero-products' );
					return;
				} else {
					tableContainer.removeClass( 'yith-wcbep-empty-state-due-disabled-fields' );
				}

				if ( data?.tableData ) {
					buildTable( data.tableData );
					unblock( tableContainer );
				} else {
					let args = {
						action     : yithWcbep.actions.loadProductsTable,
						security   : yithWcbep.security.loadProductsTable,
						ywcbep_args: { ...productsTable.getTableData(), ...data }
					};
					if ( args.ywcbep_args.hasOwnProperty( 'paged' ) ) {
						args.paged = args.ywcbep_args.paged;
					}

					const queryStringParams = new URLSearchParams( window.location.search );
					if ( queryStringParams.has( 'lang' ) ) {
						args[ 'lang' ] = queryStringParams.get( 'lang' );
					}

					block( tableContainer );
					productsTable.updateProgressBar( data && data.hasOwnProperty( 'startProgess' ) ? data.startProgess : 50 );

					let success = false;
					$.ajax( {
						data    : args,
						type    : 'POST',
						url     : yithWcbep.ajaxurl,
						success : response => {
							if ( response ) {
								buildTable( response );
								success = true;
							}
							if ( data && data.hasOwnProperty( 'successCallback' ) ) {
								data.successCallback( { ...response, isSuccessCallback: true } );
							}
						},
						complete: () => {
							unblock( tableContainer );
							!success && productsTable.updateProgressBar( -1 );
							if ( data && data.hasOwnProperty( 'completeCallback' ) ) {
								data.completeCallback( { isCompleteCallback: true } );
							}
						}
					} );
				}
			},
			handleUpdateProgressBarTrigger        : ( e, data ) => data.hasOwnProperty( 'progress' ) && productsTable.updateProgressBar( data.progress ),
			updateProgressBar                     : progress => {
				const $progressBar      = $( '.yith-wcbep-products-table-progress-bar' ),
					completeClass     = { success: 'yith-wcbep-products-table-progress-bar--complete', failure: 'yith-wcbep-products-table-progress-bar--error' },
					percentage        = Math.max( Math.min( progress, 100 ), 0 ),
					resetErrorState   = () => {
						$progressBar.removeClass( completeClass.failure );
						if ( -1 === productsTable.updateProgressBar.currentProgress ) {
							$progressBar.css( 'width', 0 );
						}
					},
					resetSuccessState = () => {
						$progressBar.removeClass( completeClass.success );
						if ( 100 === productsTable.updateProgressBar.currentProgress ) {
							$progressBar.css( 'width', 0 );
						}
					};

				productsTable.updateProgressBar.currentProgress = progress;

				if ( -1 === progress ) {
					$progressBar.addClass( completeClass.failure );
					setTimeout( resetErrorState, 2000 );
				} else if ( isNumeric( percentage ) ) {
					$progressBar.css( 'width', percentage + '%' );

					if ( 100 === percentage ) {
						$progressBar.addClass( completeClass.success );
						setTimeout( resetSuccessState, 3000 );
					} else {
						$progressBar.removeClass( Object.values( completeClass ).join( ' ' ) );
					}
				}
			},
			columnResizingHandler                 : function ( event, ui ) {
				const column = $( this );
				let columnID = column.attr( 'id' );
				if ( column.is( 'td' ) ) {
					const columOptions = column.data( 'col-options' );
					columnID           = columOptions?.col_name;
				}
				const index = $( this ).index() + 1;
				column.css( 'min-width', ui.size.width );
				$( '.yith-wcbep-products-table-container table th:nth-child(' + index + '), .yith-wcbep-products-table-container table td:nth-child(' + index + ')' ).not( column ).css( 'min-width', '0px' );
			},
			getEditingColumn                      : () => productsTable?.floatingField && productsTable.floatingField?.column ? productsTable.floatingField.column : false,
			editProductField                      : function ( e ) {
				const column        = $( this ),
					editingColumn = productsTable.getEditingColumn();
				if ( !editingColumn || editingColumn[ 0 ] !== column[ 0 ] ) {
					productsTable.applyProductFieldEditing();

					const columnOptions = column.data( 'col-options' ),
						columnType    = columnOptions?.type;

					if ( 'onoff' === columnType || ( 'title' === columnOptions?.col_name && $( e.target ).is( 'a' ) ) ) {
						return;
					}

					const tableContainer        = $( '.yith-wcbep-products-table-container' ),
						floatingField         = productsTable.getFloatingField( column ),
						$floatingEditingField = $( productsTable.templates.floatingFieldsContainer( { editingField: floatingField ? $( '<div></div>' ).html( floatingField ).html() : '' } ) ),
						columnRow             = column.closest( 'tr' ),
						columnPosition = column[0].getBoundingClientRect(),
						tablePosition = tableContainer.find('table')[0].getBoundingClientRect();

					$floatingEditingField.css({
						position: 'absolute',
						left    : columnPosition.x - tablePosition.x + columnPosition.width / 2,
						top     : columnRow[0].offsetTop + columnRow.height()
					} );

					productsTable.floatingField = {
						type : columnOptions?.type,
						field: $floatingEditingField,
						column
					};
					tableContainer.append( $floatingEditingField );
					const floatingInput = productsTable.getFloatingFieldInput();

					// Floating field's init.
					switch ( columnType ) {
						case 'image-gallery': {
							const imagesList       = $floatingEditingField.find( '.yith-wcbep-product-image-gallery__images' ),
								draggableOptions = {
									items  : '.yith-wcbep-product-image-gallery-element-container',
									opacity: 0.65
								};
							imagesList.sortable( draggableOptions );
						}
							break;
						case 'downloadable-files': {
							const filesList        = $floatingEditingField.find( '.yith-wcbep-downloadable-files' ),
								draggableOptions = {
									axis   : 'y',
									items  : '.yith-wcbep-downloadable-file',
									handle : '.yith-wcbep-downloadable-file-drag-handler',
									opacity: 0.65
								};
							filesList.sortable( draggableOptions );
						}
							break;
						case 'text-editor':
							const textEditor = $floatingEditingField.find( 'textarea' );
							if ( textEditor.length ) {
								textEditor.attr( 'id', textEditor.attr( 'id' ) + Date.now() );
								wp.editor.initialize( textEditor.attr( 'id' ), yithBulk.wpEditorDefaultOptions );
							}
							break;
						case 'text':
							floatingInput[ 0 ].selectionStart = floatingInput[ 0 ].selectionEnd = floatingInput.val().length; // To put the cursor at the end of the input.
							break;
					}
					$( document ).trigger( 'yith_fields_init' );
					triggerSelect2Init();

					if ( floatingInput ) {
						const focus = () => {
							if ( floatingInput.is( 'select.enhanced' ) ) {
								floatingInput.select2( 'open' );
							} else {
								floatingInput.first().select();
							}
						};
						if ( 'date' === columnType || floatingInput.is( 'select.enhanced' ) ) {
							setTimeout( focus, 300 );
						} else {
							focus();
						}
					}
				}
			},
			getFloatingField                      : function ( $column ) {
				const columnOptions = $column.data( 'col-options' );
				let $field          = false;
				if ( columnOptions?.type ) {
					let template = productsTable.templates.getFloatingFieldTemplate( columnOptions.type );
					if ( template ) {
						let value = $column.find( 'input.yith-wcbep-column-value' ).val();

						switch ( columnOptions.type ) {
							case 'attribute':
								value  = value ? JSON.parse( value ) : {};
								$field = $( template( { taxonomy: $column.find( '.yith-wcbep-column-container' ).data( 'taxonomy' ) ?? '' } ) );
								if ( value?.terms ) {
									const $fieldSelect = $field.find( 'select.yith-wcbep-floating-editing-field__attribute' );
									Object.keys( value.terms ).forEach( termID => {
										$fieldSelect.append( $( `<option value="${termID}" selected>${value.terms[ termID ]}</option>` ) );
									} );
								}
								if ( value?.is_visible ) {
									$field.find( 'input#yith-wcbep-attribute-options__is-visible' ).attr( 'checked', true );
								}
								if ( value?.is_variation ) {
									$field.find( 'input#yith-wcbep-attribute-options__is-variation' ).attr( 'checked', true );
								}
								break;
							case 'image':
								value  = value ? JSON.parse( value ) : {};
								$field = $( template() );
								if ( value?.image_url ) {
									$field.addClass( 'yith-wcbep-product-image-container--has-image' );
									$field.prepend( $( `<img src="${value.image_url}" data-image-id="${value.image_id}">` ) );
								}
								break;
							case 'image-gallery':
								value               = value ? JSON.parse( value ) : [];
								$field              = $( template() );
								const imageTemplate = productsTable.templates.getFloatingFieldTemplate( 'image-gallery-element' ),
									imagesList    = $field.find( '.yith-wcbep-product-image-gallery__images' );
								Object.values( value ).reverse().forEach( galleryImage => imagesList.prepend( imageTemplate( galleryImage ) ) );
								break;
							case 'downloadable-files':
								value              = value ? JSON.parse( value ) : [{}];
								$field             = $( template() );
								const fileTemplate = productsTable.templates.getFloatingFieldTemplate( 'downloadable-file' ),
									filesList    = $field.find( '.yith-wcbep-downloadable-files' );
								Object.values( value ).forEach( downloadableFile => filesList.append( fileTemplate( downloadableFile ) ) );
								break;
							case 'date':
								const date = value ? new Date( value * 1000 ) : '';
								$field     = $( template( { value: value ? getFormattedDate( date ) : '' } ) );
								break;
							case 'status':
							case 'tax-class':
							case 'tax-status':
							case 'visibility':
							case 'product-type':
							case 'stock-status':
							case 'shipping-class':
							case 'allow-backorders':
								$field = $( template( { value } ) );
								$field.find( 'option[value="' + value + '"]' ).attr( 'selected', true );
								break;
							case 'taxonomy':
								const taxonomyID = columnOptions?.col_name.substring( 14 );
								$field           = $( template( { taxonomyID } ) );
								const $select    = $field.find( 'select' );
								value            = value ? JSON.parse( value ) : {};
								if ( 'yith_shop_vendor' === taxonomyID ) {
									$select.attr( 'multiple', false );
								}
								Object.keys( value ).forEach( termID => $select.append( `<option value ="${termID}" selected>${value[ termID ]}</option>` ) );
								break;
							case 'price':
								value = yithBulk.formatPrice( value );
							default:
								$field = $( template( { value } ) );
								if ( yithBulk.isJsonString( value ) ) {
									const $select = $field.find( 'select[multiple]' );
									value         = JSON.parse( value );
									if ( $select.length ) {
										Object.keys( value ).forEach( val => $select.append( `<option value="${val}" selected>${value[ val ]}</option>` ) );
									}
								}
								$field = wp.hooks.applyFilters( 'yithWcbepGetFloatingField', $field, columnOptions, value, template );
								break;
						}
					} else {
						if ( !template ) {
							template = productsTable.templates.getFloatingFieldTemplate( 'text' );
						}
						$field = $( template( { value: $column.text() } ) );
					}
				}

				return $field;
			},
			getColumnInput                        : function ( column ) {
				const input = column ? column.find( 'input.yith-wcbep-column-value' ) : false;
				return input?.length ? input : false;
			},
			getColumnDisplayContainer             : function ( column ) {
				const container = column ? column.find( '.yith-wcbep-column-container' ) : false;
				return container?.length ? container : false;
			},
			updateColumnValue                     : function ( column, value, updateMoves ) {
				updateMoves = undefined === updateMoves ? true : updateMoves;

				if ( !column.length || column.hasClass( 'yith-wcbep-column-not-editable' ) ) {
					return false;
				}
				let change                   = false;
				const columnValueInput       = productsTable.getColumnInput( column ),
					  columnDisplayContainer = productsTable.getColumnDisplayContainer( column ),
					  initialValue           = columnDisplayContainer.data( 'initial-value' );
				if ( columnValueInput ) {
					let fromValue       = columnValueInput.val();
					const columnOptions = column.data( 'col-options' ),
						  columnType    = columnOptions.type;
					switch ( columnType ) {
						case 'attribute':
							fromValue = fromValue ? JSON.parse( fromValue ) : {};
							columnDisplayContainer.html( '<div class="yith-wcbep-select-values">' + Object.values( value?.terms ).join( ', ' ) + '</div>' );
							value = { ...fromValue, ...value };
							columnValueInput.val( value ? JSON.stringify( value ) : '' );
							break;
						case 'date': {
							let display = '';
							if ( value ) {
								value = isNumeric( value ) ? value : ( new Date( value ) ).getTime() / 1000;
								value = new Date( value * 1000 );

								if ( 'sale_price_to' === columnOptions?.col_name ) {
									value.setUTCHours( 23, 59, 59 );
								} else {
									value.setUTCHours( 0, 0, 1 );
								}
								display = getFormattedDate( value );
								value   = value.getTime() / 1000;
							}
							columnValueInput.val( value );
							columnDisplayContainer.html( display );
						}
							break;
						case 'image':
							fromValue = fromValue ? JSON.parse( fromValue ) : '';
							columnDisplayContainer.html( value ? `<img src="${value.image_url}">` : '' );
							columnValueInput.val( value ? JSON.stringify( value ) : '' );
							break;
						case 'image-gallery': {
							fromValue   = fromValue ? JSON.parse( fromValue ) : '';
							let display = $( '<div class="yith-wcbep-table-image-gallery"></div>' );
							value.length && value.forEach( ( image ) => {
								display.append( `<img src="${image.image_url}">` );
							} );
							columnDisplayContainer.html( display );
							columnValueInput.val( value ? JSON.stringify( value ) : '' );
						}
							break;
						case 'downloadable-files':
							fromValue   = fromValue ? JSON.parse( columnValueInput.val() ) : '';
							let display = '';
							if ( value.length > 0 ) {
								display = yithWcbep.i18n.downloadableFiles[ value.length === 1 ? 'singular' : 'plural' ].replace( '%s', value.length );
							} else {
								value = '';
							}
							columnDisplayContainer.html( display );
							columnValueInput.val( value ? JSON.stringify( value ) : '' );
							break;
						case 'category':
						case 'products':
						case 'tag':
						case 'taxonomy':
							value     = Object.keys( value ).length > 0 ? value : {};
							fromValue = fromValue ? JSON.parse( fromValue ) : {};
							columnDisplayContainer.html( '<div class="yith-wcbep-select-values">' + Object.values( value ).join( ', ' ) + '</div>' );
							columnValueInput.val( JSON.stringify( value ) );
							break;
						case 'price':
							if ( 'sale_price' === columnOptions?.col_name ) {
								value = '' !== value && priceStringToNumber( productsTable.getProductFieldValue( column.closest( 'tr' ).find( 'td.column-regular_price' ) ) ) <= priceStringToNumber( value ) ? initialValue : value;
							} else if ( 'regular_price' === columnOptions?.col_name ) {
								const $salePriceColumn = column.closest( 'tr' ).find( 'td.column-sale_price' ),
									  salePrice        = priceStringToNumber( productsTable.getProductFieldValue( $salePriceColumn ) );
								if ( priceStringToNumber( value ) <= salePrice ) {
									change = [productsTable.updateColumnValue( $salePriceColumn, '', false )];
								}
							}

							value = value ? priceStringToNumber( value ) : value;

							if ( 'yes' === yithWcbep.roundPrices && value ) {
								value = Math.round(Math.pow(10, yithWcbep.wcDecimals) * value) / Math.pow(10, yithWcbep.wcDecimals);
							}

							columnValueInput.val( value );
							columnDisplayContainer.find( '.yith-wcbep-price-amount' ).html( yithBulk.formatPrice( value ) );
							columnDisplayContainer.find( '.yith-wcbep-price-currency' ).css( 'display', '' !== value ? 'block' : 'none' );
							break;
						case 'onoff':
							columnValueInput.val( value );
							const onOff = columnDisplayContainer.find( '.yith-plugin-fw-onoff-container input[type="checkbox"].on_off' );
							if ( 'yes' === value ) {
								onOff.addClass( 'onoffchecked' );
							} else {
								onOff.removeClass( 'onoffchecked' );
							}
							onOff.val( value );
							onOff.prop( 'checked', 'yes' === value );
							break;
						case 'text-editor':
							columnValueInput.val( value );
							columnDisplayContainer.html( $( '<div class="yith-wcbep-text-editor-content"></div>' ).html( value ) );
							break;
						default : {
							let display = value;

							( { value, display } = wp.hooks.applyFilters( 'yithWcbepUpdateColumnValue', { value, display }, columnOptions, columnDisplayContainer ) );
							if ( display === value ) {
								value = yithBulk.maybeParseJSON( value );
								if ( 'object' === typeof value ) {
									if ( value.hasOwnProperty( 'value' ) && value.hasOwnProperty( 'text' ) ) {
										display = value.text;
										value   = value.value;
									} else {
										display = Array.isArray( value ) ? value.join( ', ' ) : Object.values( value ).join( ', ' );
										value   = JSON.stringify( value );
									}
								}
							}

							columnValueInput.val( value );
							columnDisplayContainer.html( display );
						}
							break;

					}

					const unitOfMeasure = columnDisplayContainer.attr( 'data-unit-of-measure' );
					if ( unitOfMeasure && columnDisplayContainer.text() ) {
						columnDisplayContainer.append( ' ' + unitOfMeasure );
					}

					if ( !areEquals( initialValue, value, columnType ) ) {
						column.addClass( 'yith-wcbep-column--changed' );
					} else {
						column.removeClass( 'yith-wcbep-column--changed' );
					}
					if ( !areEquals( fromValue, value ) ) {
						const currentChange = {
							column,
							from: fromValue,
							to  : value
						};
						if ( Array.isArray( change ) ) {
							change.unshift( currentChange );
						} else {
							change = currentChange;
						}
					}

					if ( !!change && updateMoves ) {
						productsTable.addMove( change );
					}
					productsTable.handleUndoRedoUsability();
					return change;
				}
			},
			addMove                               : move => {
				if ( Array.isArray( move ) && move.length === 0 ) {
					return;
				}

				if ( currentMove < moves.length ) {
					moves = moves.slice( 0, currentMove );
				}
				const $column = move?.column;
				if ( $column && $column.length ) {
					const colOptions = $column.data( 'col-options' );
					move.productID   = $column.closest( 'tr' ).data( 'product-id' );
					move.colName     = colOptions.col_name;
				}
				currentMove = moves.push( move );
				productsTable.handleUndoRedoUsability();
			},
			handleUndoRedoUsability               : () => {
				const saveButton = $( '.yith-wcbep-products-table-save' );
				if ( currentMove < 1 ) {
					saveButton.addClass( 'yith-wcbep-products-table-save--disabled' );
				} else {
					saveButton.removeClass( 'yith-wcbep-products-table-save--disabled' );
				}
				$( '.yith-wcbep-products-table-undo' ).attr( 'disabled', currentMove < 1 );
				$( '.yith-wcbep-products-table-redo' ).attr( 'disabled', moves.length <= currentMove );
			},
			getProductFieldEditingType            : () => {
				let type = '';
				if ( productsTable?.floatingField ) {
					if ( productsTable.floatingField?.type ) {
						type = productsTable.floatingField?.type;
					} else if ( productsTable.floatingField?.column ) {
						const columnOptions = productsTable.floatingField.column.data( 'col-options' );
						if ( columnOptions?.type ) {
							type = columnOptions.type;
						}
					}
				}
				return type;
			},
			getFloatingFieldInput                 : () => {
				let input = false;
				if ( productsTable?.floatingField && productsTable.floatingField?.field ) {
					let type = productsTable.getProductFieldEditingType();
					switch ( type ) {
						case 'attribute':
						case 'image':
						case 'image-gallery':
							input = productsTable.floatingField.field;
							break;
						case 'downloadable-files':
							input = productsTable.floatingField.field.find( '.yith-wcbep-downloadable-file:nth-child(1) .yith-wcbep-downloadable-file__name input' );
							break;
						case 'tag':
						case 'status':
						case 'category':
						case 'products':
						case 'taxonomy':
						case 'prod_type':
						case 'tax-class':
						case 'visibility':
						case 'tax-status':
						case 'product-type':
						case 'stock-status':
						case 'shipping-class':
						case 'allow-backorders':
							input = productsTable.floatingField.field.find( 'select' );
							break;
						case 'text-editor':
							input = productsTable.floatingField.field.find( 'textarea' );
							break;
						default:
							input = productsTable.floatingField.field.find( 'input' );
							if ( !input.length || input.hasClass( 'select2-search__field' ) ) {
								input = productsTable.floatingField.field.find( 'select' );
							}
							input = wp.hooks.applyFilters( 'yithWcbepGetFloatingFieldInput', input, productsTable.floatingField.field, type );
							break;
					}
				}
				return input;
			},
			getFloatingFieldValue                 : () => {
				const fieldType = productsTable.getProductFieldEditingType();
				let value       = '';
				if ( fieldType ) {
					const editingInput = productsTable.getFloatingFieldInput();
					switch ( fieldType ) {
						case 'attribute':
							const terms = {};
							editingInput.find( 'select.yith-wcbep-floating-editing-field__attribute option:selected' ).each( ( i, option ) => {
								terms[ option.value ] = option.text;
							} );
							value = {
								is_visible  : +( editingInput.find( 'input#yith-wcbep-attribute-options__is-visible' ).is( ':checked' ) ),
								is_variation: +( editingInput.find( 'input#yith-wcbep-attribute-options__is-variation' ).is( ':checked' ) ),
								terms
							};
							break;
						case 'date':
							const inputValue = editingInput.val(),
								  date       = inputValue ? new Date( inputValue ) : false;
							value            = date ? getFormattedDate( date ) : '';
							break;
						case 'image':
							const img = editingInput.find( 'img' );
							if ( img.length ) {
								value = {
									image_id : img.data( 'image-id' ),
									image_url: img.attr( 'src' )
								};
							}
							break;
						case 'image-gallery':
							value = [];
							editingInput.find( '.yith-wcbep-product-image-gallery-element-container' ).each( ( index, image ) => {
								const $image = $( image ).find( 'img' );
								value.push( { image_id: $image.data( 'image-id' ), image_url: $image.attr( 'src' ) } );
							} );
							break;
						case 'downloadable-files':
							value = [];
							editingInput.closest( '.yith-wcbep-downloadable-files' ).find( '.yith-wcbep-downloadable-file' ).each( ( index, file ) => {
								value.push( { name: $( file ).find( '.yith-wcbep-downloadable-file__name input' ).val(), file: $( file ).find( '.yith-wcbep-downloadable-file__file input' ).val() } );
							} );
							value = value.filter( file => !!file.file );
							break;
						case 'category':
						case 'products':
						case 'tag':
						case 'taxonomy':
							value = {};
							editingInput.find( 'option:selected' ).each( ( i, option ) => value[ option.value ] = option.innerText );
							break;
						case 'text-editor':
							value = getTextEditorContent( editingInput );
							break;
						case 'price':
						case 'sale_price':
							value = editingInput.val().toString().replace( yithWcbep.wcDecimalSeparator, '.' );
							value = isNumeric( parseFloat( value ) ) ? parseFloat( value ) : value;
							break;
						default:
							if ( editingInput.length ) {
								if ( editingInput.is( 'input' ) ) {
									value = editingInput.val();
								} else if ( editingInput.is( 'select' ) ) {
									if ( editingInput.prop( 'multiple' ) ) {
										value = {};
										editingInput.find( 'option:selected' ).toArray().forEach( option => value[ option.value ] = option.text );
									} else {
										value = { value: editingInput.val(), text: editingInput.find( 'option:selected' ).text() };
									}
								}
							}
							value = wp.hooks.applyFilters( 'yithWcbepGetFloatingFieldValue', value, editingInput, fieldType );
							break;
					}
				}

				return value;
			},
			getProductFieldValue                  : column => {
				column = column ? $( column ) : productsTable?.floatingField?.column;
				return column.find( 'input.yith-wcbep-column-value' ).val();
			},
			applyProductFieldEditing              : () => {
				if ( productsTable.floatingField?.column ) {
					productsTable.updateColumnValue( productsTable.floatingField.column, productsTable.getFloatingFieldValue() );
					productsTable.closeProductFieldEditing();
				}
			},
			isProductFieldEditingOpened           : () => !!productsTable.floatingField,
			closeProductFieldEditing              : () => {
				if ( productsTable.floatingField ) {
					$( '.yith-wcbep-floating-editing-field-container' ).remove();
					if ( productsTable.floatingField?.column ) {
						delete productsTable.floatingField;
					}
				}
			},
			maybeCloseProductFieldEditing         : e => {
				const $target              = e && e.hasOwnProperty( 'target' ) ? $( e.target ) : false,
					  allowedClasses       = [
						  'select2-selection__choice__remove',
						  'media-modal-backdrop',
						  'yith-wcbep-product-image-gallery-element__remove',
						  'ui-icon-circle-triangle-w',
						  'ui-icon-circle-triangle-e',
						  'yith-wcbep-downloadable-file-trash'
					  ],
					  allowedParentClasses = [
						  '.mce-btn',
						  '.yith-wcbep-products-table-container',
						  '.select2-dropdown',
						  '.media-modal',
						  '.mce-container',
						  '.mce-container-body',
						  '.ui-datepicker'
					  ];
				let allowed                = false;
				if ( $target ) {
					[...$target[ 0 ].classList].forEach( c => allowed = allowedClasses.indexOf( c ) !== -1 ? true : allowed );
				}
				if ( !$target || ( !$target.closest( allowedParentClasses.join( ', ' ) ).length && !allowed ) ) {
					productsTable.applyProductFieldEditing();
				}
			},
			maybeApplyProductFieldEditingChanges  : e => {
				if ( 13 === e?.keyCode && productsTable.isProductFieldEditingOpened() ) {
					productsTable.applyProductFieldEditing();
				}
			},
			handleTableScroll                     : function () {
				if ( productsTable.floatingField && productsTable.floatingField?.field && productsTable.floatingField?.column ) {
					const table = $( 'table.yith_wcbep_products' ),
						columnPosition = productsTable.floatingField.column[0].getBoundingClientRect(),
						tablePosition = table[0].getBoundingClientRect();
						  left  = columnPosition.x - tablePosition.x + columnPosition.width / 2;
					if ( left <= table.width() + table[ 0 ].offsetLeft && left >= 0 ) {
						productsTable.floatingField.field.css( { left } );
					} else {
						productsTable.floatingField.field.remove();
						productsTable.floatingField = null;
					}
				}
			},
			applyMove                             : ( move, action ) => {
				const roundPrices     = yithWcbep.roundPrices;
				yithWcbep.roundPrices = 'no';
				if ( Array.isArray( move ) ) {
					move.forEach( m => productsTable.applyMove( m, action ) );
				} else {
					if ( move?.column && !move.column.closest( 'body' ).length ) {
						move.column = $( productsTable.getTable().find( 'tr.yith-wcbep-product-' + move.productID + ' td.column-' + move.colName ) );
					}
					if ( move?.column && move.column.length ) {
						productsTable.updateColumnValue( move.column, 'undo' === action ? move.from : move.to, false );
					} else {
						//'undo' === action ? productsTable.undo() : productsTable.redo();
					}
				}
				yithWcbep.roundPrices = roundPrices;

			},
			undo                                  : function () {
				if ( currentMove > 0 ) {
					const move = moves[ --currentMove ];
					productsTable.applyMove( move, 'undo' );
				}
				productsTable.handleUndoRedoUsability();
			},
			redo                                  : function () {
				if ( moves.length > currentMove && currentMove >= 0 ) {
					const move = moves[ currentMove++ ];
					productsTable.applyMove( move, 'redo' );
				}
				productsTable.handleUndoRedoUsability();
			},
			handlePaginationChange                : function ( e ) {
				e.preventDefault();
				const $currentPage = $( '#yith-wcbep-products-table-current-page' ),
					  $totalPages  = $( '#yith-wcbep-products-table-total-pages' ),
					  $link        = $( this );
				let page           = parseInt( $currentPage.val() );

				if ( $link.hasClass( 'next-page' ) && page < $totalPages.val() ) {
					page++;
				} else if ( $link.hasClass( 'last-page' ) ) {
					page = parseInt( $totalPages.val() );
				} else if ( $link.hasClass( 'prev-page' ) && $currentPage.val() > 1 ) {
					page--;
				} else if ( $link.hasClass( 'first-page' ) ) {
					page = 1;
				}

				if ( page !== parseInt( $currentPage.val() ) ) {
					$currentPage.val( page );
					$( document ).trigger( 'yith-wcbep-load-table', { paged: page } );
				}
			},
			selectDownloadableFileFromMediaLibrary: function () {
				const urlInput       = $( this ).parent().find( '.yith-wcbep-downloadable-file__file input' ),
					  wpMediaLibrary = wp.media();

				wpMediaLibrary.open();
				wpMediaLibrary.on( 'select', function () {
					var attachment = wpMediaLibrary.state().get( 'selection' ).first().toJSON();
					urlInput.val( attachment.url );
				} );
			},
			addFieldOnDownloadableFiles           : function () {
				const filesList    = $( this ).closest( '.yith-wcbep-downloadable-files-container' ).find( '.yith-wcbep-downloadable-files' ),
					  fileTemplate = productsTable.templates.getFloatingFieldTemplate( 'downloadable-file' );
				filesList.append( fileTemplate( {} ) );
			},
			removeDownloadableFile                : function () {
				const file      = $( this ).closest( '.yith-wcbep-downloadable-file' ),
					  filesList = file.parent();
				file.remove();
				if ( filesList.find( '.yith-wcbep-downloadable-file' ).length === 0 ) {
					filesList.parent().find( '.yith-wcbep-add-downloadable-file' ).trigger( 'click' );
				}
			},
			addProductImage                       : function () {
				const imageContainer = $( this ).closest( '.yith-wcbep-product-image-container' ),
					  wpMediaLibrary = wp.media( {} );

				wpMediaLibrary.open();
				wpMediaLibrary.on( 'select', function () {
					const attachment = wpMediaLibrary.state().get( 'selection' ).first().toJSON(),
						  img        = $( '<img>' );
					imageContainer.addClass( 'yith-wcbep-product-image-container--has-image' );
					imageContainer.prepend( img );
					img.attr( 'data-image-id', attachment.id );
					img.attr( 'src', attachment.url );
				} );
			},
			changeProductImage                    : function () {
				const imageContainer = $( this ).closest( '.yith-wcbep-product-image-container' ),
					  wpMediaLibrary = wp.media();

				wpMediaLibrary.open();
				wpMediaLibrary.on( 'select', function () {
					var attachment = wpMediaLibrary.state().get( 'selection' ).first().toJSON();
					let img        = imageContainer.find( 'img' );
					if ( !img.length ) {
						img = $( '<img>' );
						imageContainer.prepend( img );
					}
					img.attr( 'data-image-id', attachment.id );
					img.attr( 'src', attachment.url );
				} );
			},
			removeProductImage                    : function () {
				const imageContainer = $( this ).closest( '.yith-wcbep-product-image-container' );
				imageContainer.removeClass( 'yith-wcbep-product-image-container--has-image' );
				imageContainer.find( 'img' ).remove();
			},
			addProductImageToGallery              : function () {
				const addImageToGalleryButton = $( this ),
					  imagesContainer         = addImageToGalleryButton.parent(),
					  wpMediaLibrary          = wp.media( { multiple: 'add' } );

				wpMediaLibrary.open();
				wpMediaLibrary.on( 'select', function () {
					const attachments   = wpMediaLibrary.state().get( 'selection' ).map( attachment => attachment.toJSON() ),
						  imageTemplate = productsTable.templates.getFloatingFieldTemplate( 'image-gallery-element' );
					attachments.filter( attachment => imagesContainer.find( `img[data-image-id="${attachment.id}"]` ).length === 0 ).forEach( attachment => addImageToGalleryButton.before( imageTemplate( { image_id: attachment.id, image_url: attachment.url } ) ) );
				} );
			},
			removeProductImageFromGallery         : function () {
				$( this ).closest( '.yith-wcbep-product-image-gallery-element-container' ).remove();
			},
			getSelectedProducts                   : () => {
				return $( productsTable.selectors.table + ' tbody .check-column input:checked' ).map( ( index, input ) => input.value ).toArray();
			},
			handleProductsPerPageChanges          : function ( e ) {
				productsTable.handleProductsPerPageChanges.lastTimeChanged = Date.now();
				const $input                                               = $( this ),
					  inputValue                                           = parseInt( $input.val() ),
					  products_per_page                                    = Math.max( isNumeric( inputValue ) ? Math.abs( inputValue ) : 1, 1 ),
					  loadTable                                            = () => productsTable.loadTable( { update_products_per_page: 1 } ),
					  checkLoadTable                                       = wait => {
						  wait = undefined === wait ? true : wait;
						  if ( ( !wait || 1500 <= Date.now() - productsTable.handleProductsPerPageChanges.lastTimeChanged ) && productsTable.handleProductsPerPageChanges.lastValue !== products_per_page ) {
							  productsTable.handleProductsPerPageChanges.lastValue = products_per_page;
							  $( '#yith-wcbep-products-table-current-page' ).val( 1 );
							  loadTable();
						  }
					  };
				if ( 'focusout' === e?.type ) {
					$input.val( products_per_page );
					checkLoadTable( false );
				} else {
					setTimeout( checkLoadTable, 1500 );
				}
			},
			handleProductsNameSearchChange        : function ( e ) {
				productsTable.handleProductsNameSearchChange.lastTimeChanged = Date.now();
				const $input                                                 = $( this ),
					  inputValue                                             = $input.val(),
					  checkLoadTable                                         = wait => {
						  wait = undefined === wait ? true : wait;
						  if ( ( !wait || 1500 <= Date.now() - productsTable.handleProductsNameSearchChange.lastTimeChanged ) && productsTable.handleProductsNameSearchChange.lastValue !== inputValue ) {
							  productsTable.handleProductsNameSearchChange.lastValue = inputValue;
							  productsTable.loadTable();
						  }
					  };

				'focusout' === e?.type ? checkLoadTable( false ) : setTimeout( checkLoadTable, 1500 );
			},
			handleOnOffFieldChange                : function () {
				const $column = $( this );
				productsTable.updateColumnValue( $column, $column.find( '.yith-plugin-fw-onoff-container  input[type="checkbox"].on_off' ).val() );
			},
			saveChanges                           : function () {
				productsTable.maybeCloseProductFieldEditing();
				let progress          = 0,
					error             = false;
				const $saveButton     = $( this ),
					  $table          = productsTable.getTable(),
					  tableContainer  = $table.closest( '#yith-wcbep-products-wp-list' ),
					  productsChanges = productsTable.getChanges(),
					  productsIDs     = Object.keys( productsChanges ),
					  step            = 100 / ( ( Math.ceil( productsIDs.length / yithWcbep.productsToSavePerRequest ) + 1 ) * 2 ),
					  saveChanges     = () => {
						  let success = false;
						  progress += step;
						  productsTable.updateProgressBar( progress );
						  if ( productsIDs.length && !error ) {
							  const products_changes = {};
							  productsIDs.splice( 0, yithWcbep.productsToSavePerRequest ).forEach( productID => {
								  products_changes[ productID ] = productsChanges[ productID ];
							  } );
							  $.ajax( {
								  data    : {
									  action  : yithWcbep.actions.saveProductsChanges,
									  security: yithWcbep.security.saveProductsChanges,
									  products_changes
								  },
								  type    : 'POST',
								  url     : yithWcbep.ajaxurl,
								  success : function ( response ) {
									  if ( 'success' === response?.success ) {
										  success = true;
										  progress += step;
										  productsTable.updateProgressBar( progress );
									  }
								  },
								  complete: () => {
									  error = !success;
									  saveChanges();
								  }
							  } );
						  } else {
							  if ( error ) {
								  productsTable.updateProgressBar( -1 );
								  unblock( tableContainer );
							  } else {
								  moves       = [];
								  currentMove = 0;
								  productsTable.loadTable( { startProgess: progress } );
							  }
							  $saveButton.removeClass( 'yith-wcbep-products-table-save--saving' );
						  }
					  };
				$saveButton.addClass( 'yith-wcbep-products-table-save--saving' );
				block( tableContainer );
				saveChanges();
			},
			getChanges                            : () => {
				const $table = productsTable.getTable();
				let changes  = {};
				$table.find( 'tbody tr' ).each( ( i, row ) => {
					const $row         = $( row ),
						  productID    = +$row.data( 'product-id' );
					let productChanges = {};
					$row.find( 'td' ).each( ( i, column ) => {
						const $column       = $( column ),
							  columnOptions = $column.data( 'col-options' ),
							  $columnInput  = productsTable.getColumnInput( $column );
						if ( $columnInput ) {
							const $columnContainer = $column.find( '.yith-wcbep-column-container' ),
								  currentValue     = $columnInput.val(),
								  initialValue     = $columnContainer.attr( 'data-initial-value' );

							if ( !areEquals( initialValue, currentValue ) && columnOptions?.col_name ) {
								productChanges[ columnOptions.col_name ] = currentValue;
							}
						}
					} );
					if ( Object.keys( productChanges ).length ) {
						changes[ productID ] = productChanges;
					}
				} );
				return changes;
			}
		},
		bulkActions          = {
			selectors       : {
				productsTable          : 'table.yith_wcbep_products',
				checkedProductsCheckbox: 'table.yith_wcbep_products tbody tr th.check-column input:checked',
				applyBulkAction        : 'button.yith-wcbep-products-table-apply-bulk-action',
				bulkActionSelect       : 'select#yith-wcbep-bulk-action'
			},
			init            : function () {
				$( document ).on( 'click', bulkActions.selectors.applyBulkAction, bulkActions.handleBulkAction );
			},
			handleBulkAction: function () {
				const bulkActionsContainer = $( this ).closest( '.yith-wcbep-products-table-bulk-actions-container' ),
					  bulk_action          = $( bulkActions.selectors.bulkActionSelect ).val(),
					  product_ids          = productsTable.getSelectedProducts(),
					  ajaxCall             = function () {
						  block( bulkActionsContainer );
						  $.ajax( {
							  data    : {
								  bulk_action,
								  product_ids,
								  action  : yithWcbep.actions.bulkActions,
								  security: yithWcbep.security.bulkActions
							  },
							  type    : 'POST',
							  url     : yithWcbep.ajaxurl,
							  success : function ( response ) {
								  if ( 'success' === response?.success ) {
									  $( document ).trigger( 'yith-wcbep-load-table' );
								  }
							  },
							  complete: () => unblock( bulkActionsContainer )
						  } );
					  };
				if ( product_ids.length ) {
					switch ( bulk_action ) {
						case 'edit':
							bulkEditing.openModal();
							break;
						case 'delete':
							yith.ui.confirm( { ...yithWcbep.modals.confirmProductDeletion, onConfirm: ajaxCall, classes: { confirm: 'yith-wcbep-confirm-delete-button' } } );
							break;
						case 'export':
							const $form                   = $( '#yith-wcbep-export-form' ),
								  maybeRemoveNotification = () => {
									  if ( !$form.hasClass( 'woocommerce-exporter__exporting' ) ) {
										  $formContainer.removeClass( 'yith-wcbep-export-form-container--exporting' ).addClass( 'yith-wcbep-export-form-container--exported' );
										  setTimeout( () => $formContainer.removeClass( 'yith-wcbep-export-form-container--exported' ), 300 );
									  } else {
										  setTimeout( maybeRemoveNotification, 1500 );
									  }
								  };
							$formContainer                = $form.closest( '.yith-wcbep-export-form-container' );
							$formContainer.removeClass( 'yith-wcbep-export-form-container--exported' ).addClass( 'yith-wcbep-export-form-container--exporting' );

							$form.find( 'input#yith-wcbep-export-form__selected-products' ).val( JSON.stringify( productsTable.getSelectedProducts() ) );
							$form.submit();
							maybeRemoveNotification();
							break;
						default:
							ajaxCall();
							break;
					}
				}
			}
		},
		bulkEditing          = {
			modal                        : null,
			init                         : () => {
				$( document ).on( 'click', 'button.yith-wcbep-products-table-bulk-editing', bulkEditing.openModal );
				$( document ).on( 'change', productsTable.selectors.table + ' .check-column input', bulkEditing.productSelectionChange );

				$( document ).on( 'change', '.yith-wcbep-bulk-editing-field .yith-wcbep-bulk-editing-field__options select.yith-wcbep-bulk-editing-field-action', bulkEditing.handleFieldActionChange );

				// Product image handlers
				$( document ).on( 'click', '.yith-wcbep-bulk-editing-field-upload-image .yith-wcbep-bulk-editing-upload-image-container:not(.yith-wcbep-bulk-editing-upload-image-container--uploaded)', bulkEditing.handleImageUpload );
				$( document ).on( 'click', '.yith-wcbep-bulk-editing-field-upload-image .yith-wcbep-bulk-editing-upload-image-container--uploaded .yith-wcbep-bulk-editing-upload-image--remove', bulkEditing.handleImageRemove );

				// Image gallery handlers
				$( document ).on( 'click', '.yith-wcbep-bulk-editing-field-upload-multiple-images .yith-wcbep-bulk-editing-upload-image-container:not(.yith-wcbep-bulk-editing-upload-image-container--uploaded)', bulkEditing.handleMultipleImageUpload );
				$( document ).on( 'click', '.yith-wcbep-bulk-editing-field-upload-multiple-images .yith-wcbep-bulk-editing-upload-image-container--uploaded .yith-wcbep-bulk-editing-upload-image--remove', bulkEditing.removeImageFromList );

				// Advanced Options toggle
				$( document ).on( 'click', '.yith-wcbep-bulk-editing-field--has-advanced-options .yith-wcbep-bulk-editing-field__label', bulkEditing.toggleFieldAdvancedOptions );

				// Sale price scheduling toggle
				$( document ).on( 'click', '.yith-wcbep-bulk-editing-field-sale-price .yith-wcbep-bulk-editing-toggle-sale-price-scheduling', bulkEditing.toggleSalePriceScheduleFields );

				$( document ).on( 'click', 'button.yith-wcbep-bulk-editing-modal-button-save', bulkEditing.saveChanges );
				$( document ).on( 'click', 'button.yith-wcbep-bulk-editing-modal-button-cancel', bulkEditing.closeModal );
				$( document ).on( 'click', '.yith-wcbep-bulk-editing-container ul.yith-plugin-fw__tabs > li.yith-plugin-fw__tab > a.yith-plugin-fw__tab__handler[href="#yith-wcbep-bulk-editing-custom-fields"]', bulkEditing.renderCustomFieldsTab );
				$( document ).on( 'click', '.yith-wcbep-bulk-editing-container ul.yith-plugin-fw__tabs > li.yith-plugin-fw__tab > a.yith-plugin-fw__tab__handler[href="#yith-wcbep-bulk-editing-custom-taxonomies"]', bulkEditing.renderCustomTaxonomiesTab );
				$( document ).on( 'click', '.yith-wcbep-button--disabled', bulkEditing.shakeButton );
			},
			shakeButton                  : function () {
				const $button = $( this );
				$button.addClass( 'yith-wcbep-button-disabled--shaking' );
				setTimeout( () => $button.removeClass( 'yith-wcbep-button-disabled--shaking' ), 900 );
			},
			openModal                    : () => {
				if ( null !== bulkEditing.modal || !productsTable.getSelectedProducts().length ) {
					return;
				}

				bulkEditing.modal = yith.ui.modal( {
					onClose: () => bulkEditing.modal = null,
					...yithWcbep.modals.bulkEditing
				} );

				const $modalContent           = bulkEditing.modal.elements.content,
					  $tabs                   = $modalContent.find( '.yith-wcbep-bulk-editing-container .yith-plugin-fw__tabs' ),
					  customFieldsDisplay     = yithWcbep.enabledColumns.filter( column => column.indexOf( 'yith_wcbep_cf' ) !== -1 && yithWcbep.columnList.hasOwnProperty( column ) ).length ? 'block' : 'none',
					  customTaxonomiesDisplay = yithWcbep.enabledColumns.filter( column => column.indexOf( 'yith_wcbep_tf' ) !== -1 && yithWcbep.columnList.hasOwnProperty( column ) ).length ? 'block' : 'none';

				$tabs.find( 'li.yith-plugin-fw__tab a[href="#yith-wcbep-bulk-editing-custom-fields"]' ).parent().css( 'display', customFieldsDisplay );
				$tabs.find( 'li.yith-plugin-fw__tab a[href="#yith-wcbep-bulk-editing-custom-taxonomies"]' ).parent().css( 'display', customTaxonomiesDisplay );

				$modalContent.find( 'select#yith-wcbep-bulk-editing-sale-price-sale_price-action option[value$="from-regular"]' ).attr( 'disabled', !yithBulk.inArray( 'regular_price', yithWcbep.enabledColumns ) );
				$modalContent.find( 'textarea.yith-wcbep-bulk-editing-text-editor, textarea.yith-wcbep-bulk-editing-replace-text-editor-with' ).each( ( index, textArea ) => {
					const textEditor = $( textArea );
					textEditor.attr( 'id', textEditor.attr( 'id' ) + Date.now() );
					wp.editor.initialize( textEditor.attr( 'id' ), yithBulk.wpEditorDefaultOptions );
				} );

				$( document ).trigger( 'yith-plugin-fw-tabs-init' ).trigger( 'yith_fields_init' );
				bulkEditing.updateFieldsVisibility();
				triggerSelect2Init();
			},
			closeModal                   : () => {
				if ( null !== bulkEditing.modal ) {
					bulkEditing.modal.close();
					bulkEditing.modal = null;
				}
			},
			updateFieldsVisibility       : () => {
				if ( bulkEditing?.modal ) {
					const $modal = bulkEditing.modal.elements.content;
					$modal.find( 'div.yith-plugin-fw__tab-panel:not(#yith-wcbep-bulk-editing-custom-fields, #yith-wcbep-bulk-editing-custom-taxonomies)' ).toArray().forEach( panel => {
						const $panel         = $( panel );
						let hasVisibleFields = false;
						$panel.find( '.yith-wcbep-bulk-editing-field' ).toArray().forEach( field => {
							const $field    = $( field ),
								  fieldID   = $field.data( 'field-id' ),
								  fieldType = $field.data( 'field-type' ),
								  isVisible = yithBulk.inArray( ( 'attribute' === fieldType ? 'attr_pa_' : '' ) + fieldID, [...yithWcbep.enabledColumns, ...yithWcbep.alwaysVisibleColumns] );
							if ( !hasVisibleFields && isVisible ) {
								hasVisibleFields = true;
							}
							$field.css( 'display', isVisible ? 'auto' : 'none' );
						} );
						if ( !hasVisibleFields ) {
							$modal.find( `ul.yith-plugin-fw__tabs li.yith-plugin-fw__tab a[href="#${$panel.attr( 'id' )}"]` ).parent().css( 'display', 'none' );
						}
					} );
				}
			},
			getCustomFieldTemplate       : type => type && $( 'script#tmpl-yith-wcbep-bulk-editing-custom-field-' + type ).length ? wp.template( 'yith-wcbep-bulk-editing-custom-field-' + type ) : false,
			getCustomTaxonomyTemplate    : () => wp.template( 'yith-wcbep-bulk-editing-custom-taxonomy' ),
			renderCustomFieldsTab        : () => {
				if ( !bulkEditing.modal.customFieldsTabLoaded ) {
					const $panel = bulkEditing.modal.elements.content.find( 'div#yith-wcbep-bulk-editing-custom-fields.yith-plugin-fw__tab-panel' );
					Object.keys( yithWcbep.customFields ).filter( key => yithBulk.inArray( key, yithWcbep.enabledColumns ) ).forEach( key => {
						const customField = yithWcbep.customFields[ key ],
							  template    = bulkEditing.getCustomFieldTemplate( customField.type );
						$panel.append( template( { id: key, label: customField.label } ) );
					} );
					bulkEditing.modal.customFieldsTabLoaded = true;
				}
				triggerSelect2Init();
			},
			renderCustomTaxonomiesTab    : () => {
				if ( !bulkEditing.modal.customTaxonomiesTabLoaded ) {
					const $panel   = bulkEditing.modal.elements.content.find( 'div#yith-wcbep-bulk-editing-custom-taxonomies.yith-plugin-fw__tab-panel' ),
						  template = bulkEditing.getCustomTaxonomyTemplate();
					Object.keys( yithWcbep.columnList ).filter( key => key.indexOf( 'yith_wcbep_tf_' ) === 0 && yithBulk.inArray( key, yithWcbep.enabledColumns ) ).forEach( key => {
						$panel.append( template( { id: key, taxonomy: key.substring( 14 ), label: yithWcbep.columnList[ key ] } ) );
					} );
					bulkEditing.modal.customTaxonomiesTabLoaded = true;
				}
				triggerSelect2Init();
			},
			productSelectionChange       : () => {
				const $bulkButton      = $( 'button#yith-wcbep-products-table-bulk-editing' ),
					  selectedProducts = productsTable.getSelectedProducts().length;
				$bulkButton.find( '.yith-wcbep-products-table-bulk-editing-selected-product-count' ).html( selectedProducts ? '(' + selectedProducts + ')' : '' );
				bulkEditing.handleBulkButtonsStatus();
			},
			handleBulkButtonsStatus      : () => {
				const $button = $( 'button#yith-wcbep-products-table-bulk-editing, button#yith-wcbep-products-table-apply-bulk-action' );
				if ( productsTable.getSelectedProducts().length ) {
					$button.removeClass( 'yith-wcbep-button--disabled' );
				} else {
					$button.addClass( 'yith-wcbep-button--disabled' );
				}
			},
			handleFieldActionChange      : function () {
				const $select       = $( this ),
					  selectedValue = $select.val();
				let $field          = $select.closest( '.yith-wcbep-bulk-editing-field' );
				const fieldType     = $field.data( 'field-type' );
				let options         = {
					display: 'flex',
					toShow : [],
					toHide : []
				};
				switch ( fieldType ) {
					case 'number':
						options[ yithBulk.inArray( selectedValue, ['new', 'increase', 'decrease'] ) ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-number-container' );
						break;
					case 'image':
						$field = $field.closest( '.yith-plugin-fw__tab-panel#yith-wcbep-bulk-editing-images' );
						options[ 'new' === selectedValue ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-field-upload-image, .yith-wcbep-bulk-editing-field-upload-image .yith-wcbep-bulk-editing-upload-image-container' );
						break;
					case 'image-gallery':
						$field = $field.closest( '.yith-plugin-fw__tab-panel#yith-wcbep-bulk-editing-images' );
						options[ yithBulk.inArray( selectedValue, ['new', 'prepend', 'append'] ) ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-field-upload-multiple-images, .yith-wcbep-bulk-editing-field-upload-multiple-images .yith-wcbep-bulk-editing-upload-images-container' );
						break;
					case 'attribute':
					case 'taxonomy':
						options[ yithBulk.inArray( selectedValue, ['new', 'add', 'remove'] ) ? 'toShow' : 'toHide' ].push( `.yith-wcbep-bulk-editing-${fieldType}-container` );
						break;
					case 'date':
						options[ 'new' === selectedValue ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-date-container' );
						options[ yithBulk.inArray( selectedValue, ['increase-by-days', 'decrease-by-days'] ) ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-days-container' );
						break;
					case 'price':
					case 'decimal':
						options[ yithBulk.inArray( selectedValue, ['new', 'increase-by-value', 'decrease-by-value', 'increase-by-percentage', 'decrease-by-percentage'] ) ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-' + fieldType + '-container' );
						break;
					case 'sale-price':
						options[ yithBulk.inArray( selectedValue, ['new', 'increase-by-value', 'decrease-by-value', 'increase-by-percentage', 'decrease-by-percentage', 'decrease-by-percentage-from-regular', 'decrease-by-value-from-regular'] ) ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-sale-price-container' );
						break;
					default:
						options[ yithBulk.inArray( selectedValue, ['new', 'add', 'remove', 'replace', 'prepend', 'append'] ) ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-' + fieldType + '-container' );
						options[ 'replace' === selectedValue ? 'toShow' : 'toHide' ].push( '.yith-wcbep-bulk-editing-replace-' + fieldType + '-with-container' );
						options = wp.hooks.applyFilters( 'yithWcbepBulkEditingFieldActionChangeOptions', options, fieldType, selectedValue );
						break;
				}

				const $unitOfMeasure = $field.find( '.yith-wcbep-bulk-editing-container__unit-of-measure' );
				if ( $unitOfMeasure.length ) {
					if ( selectedValue.indexOf( 'percentage' ) !== -1 ) {
						$unitOfMeasure.html( '%' );
					} else {
						const baseUnitOfMeasure = $unitOfMeasure.attr( 'data-unit-of-measure' ) !== undefined ? $unitOfMeasure.data( 'unit-of-measure' ) : '';
						$unitOfMeasure.html( baseUnitOfMeasure );
					}
				}

				options.toHide.length && $field.find( options.toHide.join( ', ' ) ).hide();
				options.toShow.length && $field.find( options.toShow.join( ', ' ) ).css( 'display', options.display );
			},
			handleImageUpload            : function () {
				const $imageUploadContainer = $( this ),
					  wpMediaLibrary        = wp.media();

				wpMediaLibrary.open();
				wpMediaLibrary.on( 'select', function () {
					var attachment = wpMediaLibrary.state().get( 'selection' ).first().toJSON();
					$imageUploadContainer.addClass( 'yith-wcbep-bulk-editing-upload-image-container--uploaded' );
					$imageUploadContainer.append( `<img src="${attachment.url}" data-image-id="${attachment.id}">` );
				} );
			},
			handleImageRemove            : function () {
				const $container = $( this ).closest( '.yith-wcbep-bulk-editing-upload-image-container' );
				$container.find( 'img' ).remove();
				$container.removeClass( 'yith-wcbep-bulk-editing-upload-image-container--uploaded' );
			},
			handleMultipleImageUpload    : function () {
				const $uploadButton    = $( this ),
					  $imagesContainer = $uploadButton.closest( '.yith-wcbep-bulk-editing-upload-images-container' ),
					  wpMediaLibrary   = wp.media( { multiple: 'add' } ),
					  imageTemplate    = wp.template( 'yith-wcbep-bulk-editing-upload-multiple-images-image' );
				wpMediaLibrary.open();
				wpMediaLibrary.on( 'select', function () {
					const attachments = wpMediaLibrary.state().get( 'selection' ).map( attachment => attachment.toJSON() );
					attachments.filter( attachment => $imagesContainer.find( `img[src="${attachment.url}"]` ).length === 0 ).forEach( attachment => $uploadButton.before( imageTemplate( { image_url: attachment.url, image_id: attachment.id } ) ) );
				} );
			},
			removeImageFromList          : function () {
				$( this ).closest( '.yith-wcbep-bulk-editing-upload-image-container' ).remove();
			},
			toggleFieldAdvancedOptions   : function () {
				const $fieldContainer  = $( this ).closest( '.yith-wcbep-bulk-editing-field' ),
					  $advancedOptions = $fieldContainer.find( '.yith-wcbep-bulk-editing-field-advanced-options' );
				$fieldContainer.toggleClass( 'yith-wcbep-bulk-editing-field--has-advanced-options--visible' );
				$advancedOptions.slideToggle();
			},
			toggleSalePriceScheduleFields: function () {
				const $toggle                  = $( this ),
					  $scheduleSalePriceFields = $toggle.closest( '.yith-plugin-fw__tab-panel' ).find( '#yith-wcbep-bulk-editing-field-sale_price_from, #yith-wcbep-bulk-editing-field-sale_price_to' );
				$toggle.toggleClass( 'yith-wcbep-bulk-editing-toggle-sale-price-scheduling--visible' );
				$toggle.hasClass( 'yith-wcbep-bulk-editing-toggle-sale-price-scheduling--visible' ) ? $scheduleSalePriceFields.css( 'display', 'flex' ).hide().fadeIn() : $scheduleSalePriceFields.fadeOut();
			},
			editProducts                 : ( productIDs, changes ) => {
				const $table       = $( 'table.yith_wcbep_products' );
				let movesCollector = [];
				productIDs.forEach( productID => {
					const $productRow = $table.find( `tr.yith-wcbep-product-${productID}` );
					changes.forEach( ( { field, fieldID, action, options } ) => {
						const fieldColumn = $productRow.find( `td.column-${'attribute' === field ? 'attr_pa_' : ''}${fieldID ?? ''}` );
						let value         = '';
						if ( fieldColumn ) {
							let fieldValue = productsTable.getProductFieldValue( fieldColumn );
							switch ( field ) {
								case 'text':
								case 'text-editor':
									switch ( action ) {
										case 'new':
											value = options?.value;
											break;
										case 'prepend':
											value = options?.value + fieldValue;
											break;
										case 'append':
											value = fieldValue + options?.value;
											break;
										case 'remove':
											value = fieldValue.replaceAll( options?.value, '' );
											break;
										case 'replace':
											value = fieldValue.replaceAll( options?.search, options?.replace );
											break;
										case 'empty':
											value = '';
											break;
									}
									break;
								case 'number':
									switch ( action ) {
										case 'new':
											value = options?.value;
											break;
										case 'increase':
											value = options?.value;

											if ( '' !== value ) {
												value = ( +fieldValue ) + ( +options?.value );
											}
											break;
										case 'decrease':
											value = options?.value;

											if ( value ) {
												value = ( +fieldValue ) - ( +options?.value );
											}
											break;
										case 'empty':
											value = '';
											break;
									}
									break;
								case 'image':
									if ( 'new' === action ) {
										value = options?.value;
									} else if ( 'empty' === action ) {
										value = '';
									}
									break;
								case 'image-gallery':
									fieldValue = fieldValue ? JSON.parse( fieldValue ) : [];
									switch ( action ) {
										case 'new':
											value = options?.value;
											break;
										case 'append':
											value = [...fieldValue, ...options?.value];
											break;
										case 'prepend':
											value = [...options?.value, ...fieldValue];
											break;
									}
									break;
								case 'category':
								case 'tag':
								case 'products':
								case 'badge':
									fieldValue = fieldValue ? JSON.parse( fieldValue ) : {};
									switch ( action ) {
										case 'new':
											value = options?.value;
											break;
										case 'add':
											value = { ...fieldValue, ...options.value };
											break;
										case 'remove':
											value = fieldValue;
											Object.keys( options.value ).forEach( key => value.hasOwnProperty( key ) && delete value[ key ] );
											break;
										case 'replace':
											value                 = fieldValue;
											let fieldValueKeys    = Object.keys( fieldValue ),
												optionsSearchKeys = Object.keys( options.search );
											if ( fieldValueKeys.length && optionsSearchKeys.length && ( new Set( [...fieldValueKeys, ...optionsSearchKeys] ) ).size === fieldValueKeys.length ) {
												optionsSearchKeys.forEach( key => delete value[ key ] );
												value = { ...value, ...options.replace };
											}
											break;
										case 'empty':
											value = {};
											break;
									}
									break;
								case 'taxonomy':
									fieldValue = fieldValue ? JSON.parse( fieldValue ) : { is_visible: 0, is_variation: 0, terms: {} };
									value      = fieldValue;
									switch ( action ) {
										case 'new':
											value = options?.value ?? {};
											break;
										case 'add':
											value = { ...( value ?? {} ), ...( options?.value ?? {} ) };
											break;
										case 'remove':
											Object.keys( options?.value ?? {} ).forEach( termID => delete value[ termID ] );
											break;
										case 'empty':
											value = {};
											break;
									}
									break;
								case 'attribute':
									fieldValue = fieldValue ? JSON.parse( fieldValue ) : { is_visible: 0, is_variation: 0, terms: {} };
									value      = fieldValue;
									switch ( action ) {
										case 'new':
											value.terms = options?.value ?? {};
											break;
										case 'add':
											value.terms = { ...( value?.terms ?? {} ), ...options?.value };
											break;
										case 'remove':
											Object.keys( options?.value ?? {} ).forEach( termID => delete value.terms[ termID ] );
											break;
										case 'empty':
											value = { is_visible: 0, is_variation: 0, terms: {}, default: '' };
											break;
										case 'advanced-options':
											value = { ...value, ...options };
											break;
									}
									break;
								case 'decimal':
								case 'price':
									value = fieldValue;
									switch ( action ) {
										case 'new':
											value = priceStringToNumber( options?.value );
											break;
										case 'increase-by-value':
											if ( '' !== value ) {
												value = +value + priceStringToNumber( options?.value ?? 0 );
											}
											break;
										case 'decrease-by-value':
											if ( value ) {
												value -= priceStringToNumber( options?.value ?? 0 );
											}
											break;
										case 'increase-by-percentage':
											if ( value ) {
												value = ( value * ( 1 + ( priceStringToNumber( options?.value ?? 0 ) / 100 ) ) ).toFixed( 12 );
												if ( 'decimal' === field ) {
													value = Math.round( value * 100 ) / 100;
												}
											}
											break;
										case 'decrease-by-percentage':
											if ( value ) {
												value = ( value * ( 1 - ( priceStringToNumber( options?.value ?? 0 ) / 100 ) ) ).toFixed( 12 );
												if ( 'decimal' === field ) {
													value = Math.round( value * 100 ) / 100;
												}
											}
											break;
										case 'empty':
											value = '';
											break;
									}
									value = value ? Math.max( value, 0 ) : value;
									break;
								case 'sale-price':
									value              = fieldValue;
									const regularPrice = productsTable.getProductFieldValue( $productRow.find( 'td.column-regular_price' ) );
									switch ( action ) {
										case 'new':
											value = priceStringToNumber( options?.value );
											break;
										case 'increase-by-value':
											value = +value + priceStringToNumber( options?.value ?? 0 );
											break;
										case 'decrease-by-value':
											value -= priceStringToNumber( options?.value ?? 0 );
											break;
										case 'increase-by-percentage':
											value *= ( 1 + ( priceStringToNumber( options?.value ?? 0 ) / 100 ) );
											break;
										case 'decrease-by-percentage':
											value *= ( 1 - ( priceStringToNumber( options?.value ?? 0 ) / 100 ) );
											break;
										case 'decrease-by-value-from-regular':
											value = regularPrice - priceStringToNumber( options?.value ?? 0 );
											break;
										case 'decrease-by-percentage-from-regular':
											value = regularPrice * ( 1 - ( priceStringToNumber( options?.value ?? 0 ) / 100 ) );
											break;
									}
									value = 'empty' === action ? '' : Math.max( value, 0 );
									break;
								case 'date':
									switch ( action ) {
										case 'new' :
											value = options?.value;
											break;
										case 'increase-by-days' : {
											if ( fieldValue ) {
												const date = new Date( fieldValue * 1000 );
												date.setDate( date.getUTCDate() + parseInt( options.value ) );
												value = getFormattedDate( date );
											}
										}
											break;
										case 'decrease-by-days' : {
											if ( fieldValue ) {
												let date                    = new Date( fieldValue * 1000 );
												const MILLISECONDS_IN_A_DAY = 3600 * 1000 * 24,
													  newTime               = date.getTime() - MILLISECONDS_IN_A_DAY * parseInt( options.value );
												date                        = new Date( newTime );
												value                       = getFormattedDate( date );
											}
										}
											break;
										case 'empty' :
											value = '';
											break;
									}
									break;
								case 'onoff':
									value = options.value;
									break;
								default:
									if ( options.hasOwnProperty( 'value' ) && options.hasOwnProperty( 'text' ) ) {
										value = options;
									} else {
										value = options?.value;
									}
									break;
							}
							value = wp.hooks.applyFilters( 'yithWcbepBulkEditingEditProductsValue', value, field, action, options, fieldValue, $productRow );
						}
						const updated = productsTable.updateColumnValue( fieldColumn, value, false );
						if ( updated ) {
							movesCollector.push( updated );
						}
					} );
				} );
				productsTable.addMove( movesCollector );
			},
			saveChanges                  : () => {
				if ( null !== bulkEditing.modal ) {
					let changes = [];
					bulkEditing.modal.elements.content.find( '.yith-wcbep-bulk-editing-field' ).each( ( i, field ) => {
						const $field       = $( field ),
							  fieldType    = $field.data( 'field-type' ),
							  $fieldAction = $field.find( 'select.yith-wcbep-bulk-editing-field-action' ),
							  fieldID      = $field.data( 'field-id' );

						let action  = $fieldAction.length ? $fieldAction.val() : '',
							options = {};
						if ( $fieldAction.length ) {
							switch ( fieldType ) {
								case 'text':
								case 'text-editor':
									let value = $field.find( '.yith-wcbep-bulk-editing-' + fieldType ).val();
									if ( 'text-editor' === fieldType ) {
										value = getTextEditorContent( $field.find( '.yith-wcbep-bulk-editing-' + fieldType ) );
									}
									if ( yithBulk.inArray( action, ['new', 'prepend', 'append', 'remove'] ) ) {
										options.value = value;
									} else if ( 'replace' === action ) {
										let replace = $field.find( '.yith-wcbep-bulk-editing-replace-' + fieldType + '-with' ).val();
										if ( 'text-editor' === fieldType ) {
											replace = getTextEditorContent( $field.find( '.yith-wcbep-bulk-editing-replace-' + fieldType + '-with' ) );
										}
										options = {
											search: 'yes' === yithWcbep.useRegularExpressions ? new RegExp( value, 'g' ) : value,
											replace
										};
									}
									break;
								case 'number':
									options.value = $field.find( 'input.yith-wcbep-bulk-editing-number' ).val();
									break;
								case 'image':
									if ( 'new' === action ) {
										const $image = $field.parent().find( '.yith-wcbep-bulk-editing-field.yith-wcbep-bulk-editing-field-upload-image img' );
										if ( $image.length ) {
											options.value = {
												image_id : $image.data( 'image-id' ),
												image_url: $image.attr( 'src' )
											};
										}
									}
									break;
								case 'image-gallery':
									if ( yithBulk.inArray( action, ['new', 'prepend', 'append'] ) ) {
										const $images = $field.parent().find( '.yith-wcbep-bulk-editing-field.yith-wcbep-bulk-editing-field-upload-multiple-images img' );
										if ( $images.length ) {
											options.value = [];
											$images.each( ( index, image ) => {
												const $img = $( image );
												options.value.push( {
													image_id : $img.data( 'image-id' ),
													image_url: $img.attr( 'src' )
												} );
											} );
										}
									} else if ( 'empty' === action ) {
										options.value = '';
									}
									break;
								case 'onoff':
								case 'status':
								case 'visibility':
								case 'tax-status':
								case 'shipping-class':
								case 'stock-status':
								case 'allow-backorders':
								case 'product-type':
									options.value = action;
									options.text  = $fieldAction.find( 'option:selected' ).text();
									break;
								case 'tax-class':
									if ( 'no-changes' !== action ) {
										options.value = action;
									}
									break;
								case 'taxonomy':
								case 'attribute': {
									const $selectedOptions = $field.find( `select.yith-wcbep-bulk-editing-${fieldType} option:selected` );

									if ( yithBulk.inArray( action, ['new', 'add', 'remove'] ) ) {
										options.value = {};
										$selectedOptions.each( ( index, option ) => {
											options.value[ option.value ] = option.text;
										} );
									}
								}
									break;
								case 'sale-price':
								case 'price':
								case 'decimal':
									options.value = $field.find( `.yith-wcbep-bulk-editing-${fieldType}-container input` ).val();
									break;
								case 'date':
									if ( yithBulk.inArray( action, ['decrease-by-days', 'increase-by-days'] ) ) {
										options.value = $field.find( '.yith-wcbep-bulk-editing-days-container input' ).val();
									} else if ( 'new' === action ) {
										options.value = $field.find( '.yith-wcbep-bulk-editing-date-container input' ).val();
									}
									break;
								default: {
									if ( 'empty' !== action ) {
										const $selectedOptions = $field.find( `select.yith-wcbep-bulk-editing-${fieldType} option:selected` );
										if ( yithBulk.inArray( action, ['new', 'add', 'remove'] ) ) {
											options.value = {};
											$selectedOptions.each( ( index, option ) => {
												options.value[ option.value ] = option.text;
											} );
										} else if ( 'replace' === action ) {
											const $replacements = $field.find( `select.yith-wcbep-bulk-editing-replace-${fieldType}-with option:selected` );
											options             = {
												search : {},
												replace: {}
											};

											$selectedOptions.each( ( index, option ) => {
												options.search[ option.value ] = option.text;
											} );

											$replacements.each( ( index, option ) => {
												options.replace[ option.value ] = option.text;
											} );
										}
									} else {
										options.value = {};
									}
									options = wp.hooks.applyFilters( 'yithWcbepBulkEditingSaveChangesOptions', options, fieldType, action, $field, $fieldAction );
								}
									break;
							}

							if ( action && 'no-changes' !== action ) {
								changes.push( {
									field: fieldType,
									fieldID,
									action,
									options
								} );
							}
							let advancedOptions = {};
							if ( $field.hasClass( 'yith-wcbep-bulk-editing-field--has-advanced-options' ) ) {
								const $advancedOptions = $field.find( '.yith-wcbep-bulk-editing-field-advanced-options' );
								switch ( fieldType ) {
									case 'attribute':
										const visibleOnProduct = $advancedOptions.find( '.yith-wcbep-bulk-editing-field-advanced-option-visible-on-product-page .yith-wcbep-bulk-editing-field-advanced-option__field select' ).val(),
											  usedForVariation = $advancedOptions.find( '.yith-wcbep-bulk-editing-field-advanced-option-used-for-variation .yith-wcbep-bulk-editing-field-advanced-option__field select' ).val(),
											  defaultOption    = $advancedOptions.find( '.yith-wcbep-bulk-editing-field-advanced-option-default-term .yith-wcbep-bulk-editing-field-advanced-option__field select option:selected' );
										if ( '' !== visibleOnProduct ) {
											advancedOptions.is_visible = +( visibleOnProduct === 'yes' );
										}
										if ( '' !== usedForVariation ) {
											advancedOptions.is_variation = +( usedForVariation === 'yes' );
										}
										if ( defaultOption.length ) {
											advancedOptions.default                             = {};
											advancedOptions.default[ defaultOption[ 0 ].value ] = defaultOption[ 0 ].text;
										}
										break;
								}
								if ( Object.values( advancedOptions ).length ) {
									changes.push( {
										field  : fieldType,
										fieldID,
										action : 'advanced-options',
										options: advancedOptions
									} );
								}
							}
						}
					} );

					bulkEditing.editProducts( productsTable.getSelectedProducts(), changes );

					bulkEditing.closeModal();
				}
			}
		};

	productsTable.init();
	bulkActions.init();
	bulkEditing.init();

	const onConfirmRefresh = function ( event ) {
		if ( Object.keys( productsTable.getChanges() ).length ) {
			event.preventDefault();
			return event.returnValue = true;
		}
	};

	window.addEventListener( 'beforeunload', onConfirmRefresh, { capture: true } );

} );

