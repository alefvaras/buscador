/* global yithWcbep */

jQuery( function ($) {

	const blockArgs         = {
			  message   : '',
			  overlayCSS: {backgroundColor: '#FFFFFF', opacity: 0.8, cursor: 'wait'},
		  },
		  block             = element => element.block( blockArgs ),
		  unblock           = element => element.unblock(),
		  columnsVisibility = {
			  selectors                       : {
				  openModalButton      : 'button.yith-wcbep-products-table-column-visibility:not( .yith-wcbep-button--disabled )',
				  saveButton           : 'button.yith-wcbep-columns-visibility-modal-button-save',
				  cancelButton         : 'button.yith-wcbep-columns-visibility-modal-button-cancel',
				  columnsCheckboxInputs: '.yith-wcbep-visibility-column input[type="checkbox"]',
				  toggleAllAction      : '.yith-wcbep-visibility-columns-select-all-action__label, .yith-wcbep-visibility-columns-select-all-action__input',
				  searchIcon           : '.yith-wcbep-visibility-columns-search-action__icon',
				  searchInput          : 'input#yith-wcbep-visibility-columns-search',
			  },
			  modal                           : false,
			  init                            : () => {
				  $( document ).on( 'click', columnsVisibility.selectors.openModalButton, columnsVisibility.openModal );
				  $( document ).on( 'click', columnsVisibility.selectors.saveButton, columnsVisibility.saveColumnsVisibility );
				  $( document ).on( 'click', columnsVisibility.selectors.cancelButton, columnsVisibility.closeModal );
				  $( document ).on( 'click', columnsVisibility.selectors.columnsCheckboxInputs, columnsVisibility.handleToggleColumnVisibility );
				  $( document ).on( 'click', columnsVisibility.selectors.toggleAllAction, columnsVisibility.handleToggleAllColumnsVisibility );
				  $( document ).on( 'click', columnsVisibility.selectors.searchIcon, columnsVisibility.maybeEmptySearch );
				  $( document ).on( 'change keyup', columnsVisibility.selectors.searchInput, columnsVisibility.searchColumn );
			  },
			  openModal                       : function () {
				  if ( yithWcbep.modals?.columnsVisibility ) {
					  columnsVisibility.modal = yith.ui.modal( {...yithWcbep.modals.columnsVisibility, onClose: () => columnsVisibility.modal = false} );

					  let columnsContainer = $( columnsVisibility.modal.elements.content ).find( '.yith-wcbep-visibility-columns-container' ),
						  columnsKeys      = Object.keys( yithWcbep.columnList );

					  columnsKeys.sort( (a, b) => yithWcbep.columnList[ a ] > yithWcbep.columnList[ b ] ? 1 : -1 );
					  columnsKeys.map( key => yithWcbep.alwaysVisibleColumns.indexOf( key ) !== -1 ? '' : columnsContainer.append( wp.template( 'yith-wcbep-columns-visibility-column' )( {key, name: yithWcbep.columnList[ key ]} ) ) );

					  const columns = $( columnsVisibility.modal.elements.content ).find( '.yith-wcbep-visibility-column' );

					  columns.show().removeClass( 'yith-wcbep-visibility-column--disabled' ).find( 'input[type="checkbox"]' ).attr( 'checked', true );
					  columns.not( yithWcbep.enabledColumns.map( value => '.yith-wcbep-visibility-column__' + value ).join( ', ' ) ).addClass( 'yith-wcbep-visibility-column--disabled' );
					  columns.find( yithWcbep.hiddenColumns.map( value => 'input#yith-wcbep-visibility-column-' + value ).join( ', ' ) ).attr( 'checked', false );

					  columnsVisibility.modal.elements.content.on( 'scroll', columnsVisibility.handleColumnsListScroll );

					  columnsVisibility.checkSelectAllvalue();
					  columnsVisibility.modal.elements.content.trigger( 'scroll' );
				  }
			  },
			  handleColumnsListScroll         : function () {
				  const $content  = $( this ),
						upper     = !! $content[ 0 ].scrollTop,
						lower     = $content[ 0 ].scrollTop + $content[ 0 ].offsetHeight + 5 < $content[ 0 ].scrollHeight,
						boxShadow = 'inset 0px -100px 30px -90px rgba(178, 198, 210, ' + (0.5 * +lower) + '), inset 0px 100px 30px -90px rgba(178, 198, 210, ' + (0.5 * +upper) + ')';
				  if ( columnsVisibility.handleColumnsListScroll?.upper !== upper || columnsVisibility.handleColumnsListScroll?.lower !== lower ) {
					  columnsVisibility.handleColumnsListScroll.upper = upper;
					  columnsVisibility.handleColumnsListScroll.lower = lower;
					  $content.find( '.yith-wcbep-column-visibility-modal-content-wrapper__shadow' ).css( 'box-shadow', boxShadow );
				  }
			  },
			  handleToggleColumnVisibility    : function () {
				  const input = $( this );
				  input.prop( 'checked', input.is( ':checked' ) );
				  columnsVisibility.checkSelectAllvalue();
			  },
			  handleToggleAllColumnsVisibility: function () {
				  let $modal         = columnsVisibility.getModal(),
					  selectAllInput = $modal.find( 'input#yith-wcbep-column-visibility-select-all' ),
					  counter        = 0,
					  checked        = selectAllInput.is( ':checked' );
				  selectAllInput.attr( 'checked', checked ).trigger( 'change' );
				  $( $modal.find( '.yith-wcbep-visibility-column' ).toArray().filter( col => 'none' !== $( col ).css( 'display' ) ) ).find( 'input[type="checkbox"]' ).each( (index, input) => setTimeout( () => $( input ).prop( 'checked', checked ).trigger( 'change' ), 10 * (counter++) ) );
			  },
			  checkSelectAllvalue             : () => {
				  if ( columnsVisibility?.modal ) {
					  const $modal     = $( columnsVisibility.modal.elements.main ),
							allChecked = $( $modal.find( '.yith-wcbep-visibility-column' ).toArray().filter( column => $( column ).css( 'display' ) !== 'none' ) ).find( 'input[type="checkbox"]:not(:checked)' ).length === 0,
							$selectAll = $modal.find( '#yith-wcbep-column-visibility-select-all' );
					  if ( allChecked !== $selectAll.prop( 'checked' ) ) {
						  $selectAll.prop( 'checked', allChecked );
					  }
				  }
			  },
			  closeModal                      : () => columnsVisibility.modal && columnsVisibility.modal.close(),
			  getModal                        : () => {
				  return columnsVisibility?.modal.elements.main ? $( columnsVisibility?.modal.elements.main ) : false;
			  },
			  saveColumnsVisibility           : function () {
				  let hidden_cols = $( '.yith-wcbep-visibility-column input:not(:checked)' ).map( (i, el) => el.value ).toArray();

				  block( columnsVisibility.modal.elements.main );

				  $.ajax( {
					  data    : {
						  hidden_cols,
						  action  : yithWcbep.actions.saveColumnsVisibility,
						  security: yithWcbep.security.saveColumnsVisibility,
					  },
					  type    : 'POST',
					  dataType: 'json',
					  url     : yithWcbep.ajaxurl,
					  success : response => {
						  if ( response && 'success' === response?.success ) {
							  columnsVisibility.closeModal();
							  yithWcbep.hiddenColumns = hidden_cols;
							  $( hidden_cols.map( key => `table.yith_wcbep_products th.column-${key}, table.yith_wcbep_products td.column-${key}` ).join( ', ' ) ).hide();
							  $( `table.yith_wcbep_products th:not(${hidden_cols.map( key => '.column-' + key ).join( ', ' )}), table.yith_wcbep_products td:not(${hidden_cols.map( key => '.column-' + key ).join( ', ' )})` ).show();
						  }
					  },
					  complete: () => {
						  if ( columnsVisibility.modal?.elements && columnsVisibility.modal.elements?.main ) {
							  unblock( columnsVisibility.modal.elements.main );
						  }
					  },
				  } );
			  },
			  maybeEmptySearch                : () => {
				  const $input = $( 'input#yith-wcbep-visibility-columns-search' );
				  $input.val() && $input.val( '' ).trigger( 'change' );
			  },
			  searchColumn                    : function () {
				  let removed                = 0;
				  const $input               = $( this ),
						$modalContent        = $input.closest( '.yith-plugin-fw__modal__content' ),
						columnsListContainer = $modalContent.find( '.yith-wcbep-visibility-columns-container' ),
						columnsList          = columnsListContainer.find( '.yith-wcbep-visibility-column:not(.yith-wcbep-visibility-column--disabled)' ),
						searchFor            = $input.val().toLowerCase();
				  if ( searchFor ) {
					  $modalContent.css( 'height', $modalContent.innerHeight() - 25 + 'px' );
					  columnsList.each( (index, column) => {
						  const $column        = $( column ),
								isDisabled     = $column.hasClass( 'yith-wcbep-visibility-column--disabled' ),
								matchTheSearch = $column.find( 'input' ).val().toLowerCase().indexOf( searchFor ) !== -1 || $column.find( 'label' ).text().toLowerCase().indexOf( searchFor ) !== -1;
						  $column.css( 'display', ! isDisabled && matchTheSearch ? 'flex' : 'none' );
						  if ( isDisabled || ! matchTheSearch ) {
							  removed++;
						  }
					  } );
				  } else {
					  $modalContent.css( 'height', 'auto' );
					  columnsList.not( '.yith-wcbep-visibility-column--disabled' ).show();
				  }

				  columnsListContainer.toggleClass( 'yith-wcbep-visibility-columns-container--empty-search', removed === columnsList.length );

				  columnsVisibility.modal.elements.content.trigger( 'scroll' );
				  columnsVisibility.checkSelectAllvalue();
			  },
		  };

	columnsVisibility.init();

	// TODO: remove these lines [DEV]
	// columnsVisibility.openModal();

} );
