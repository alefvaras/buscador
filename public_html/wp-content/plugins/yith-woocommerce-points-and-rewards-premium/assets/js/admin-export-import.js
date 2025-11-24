jQuery(
	function ($) {

		$( document ).on(
			'click',
			'.export-import-custom-button',
			function(ev){
				ev.preventDefault();

				yith.ui.modal(
					{
						content: $( 'script[data-template="ywpar-modal"]' ).text().split( /\$\{(.+?)\}/g ),
						title: yith_ywpar_export_import.modal_title,
						width: 600,
						allowWpMenu: false,
						closeWhenClickingOnOverlay: true,
						allowClosingWithEsc: true,
						classes: {
							title: 'ywpar-export-import-modal-title',
							content: 'ywpar-export-import-modal-content',
							footer: 'ywpar-export-import-modal-footer',
							wrap: 'ywpar-modal-wrapper'
						},

					}
				);
			}
		);

		// Export Import steps handling.
		const changeStep = function( stepTo ){
			if( stepTo === 1 ){
				$( '.ywpar-export-import-modal-title' ).show();
			}else{
				$( '.ywpar-export-import-modal-title' ).hide();
			}
			$( '.single-step.active' ).removeClass( 'active' ).hide();
			$( '.single-step[data-step="' + stepTo + '"]' ).show().addClass( 'active' );
			$( '.step-label.active' ).removeClass( 'active' );
			$( '.step-label[data-step="' + stepTo + '"]' ).addClass( 'active' );
		};

		$( document ).on(
			'click',
			'.move-step.ywpar-export-import-modal-button',
			function(e){
				e.preventDefault();
				$( '.single-configuration' ).hide().removeClass( 'active' );
				const type = $( 'input[name="ywpar-export-import-radio-modal"]:checked' ).val();
				$( '.' + type + '-configuration' ).addClass( 'active' ).show();
				changeStep( $( this ).data( 'step-to' ) );
			}
		);

		$( document ).on(
			'click',
			'.previous',
			function(e){
				e.preventDefault();
				changeStep( $( this ).data( 'step-to' ) );
			}
		);

		$( document ).on(
			'click',
			'button.try-again',
			function(e){
				e.preventDefault();
				changeStep( $( this ).data( 'step-to' ) );
			}
		);

		$( document ).on(
			'click',
			'#close-modal',
			function(){
				$( '.yith-plugin-fw__modal__close' ).trigger( 'click' );
				location.reload();
			}
		);

		$( document ).on(
			'click',
			'.ywpar-submit-button',
			function( e ){
				e.preventDefault();
				const form = $( '.single-configuration.active' ).find( 'form' );
				const data = {
					action : 'yith_wpar_submit_import_export_form',
					options : form.serializeArray(),
					type   : form.attr( 'id' ),
				};
				$.ajax(
					{
						type    : "POST",
						data    : data,
						url     : yith_ywpar_export_import.ajax_url,
						success : function ( response ) {
							let downloadLink    = document.createElement( "a" );
							const fileData      = ['\ufeff' + response],
								blobObject      = new Blob(
									fileData,
									{
										type: "text/csv;charset=utf-8;"
									}
								),
								url             = URL.createObjectURL( blobObject ),
								d               = new Date(),
								date_output     = d.getDate() + "-" + (d.getMonth() + 1) + "-" + d.getFullYear() + "-" + d.getHours() + "-" + d.getMinutes() + "-" + d.getSeconds(),
								actionPerformed = $( '.single-configuration.active' ).attr( 'id' );

							if ( 'export' === actionPerformed ) {
								downloadLink.href     = url;
								downloadLink.download = "points-rewards-export_" + date_output + ".csv";

								document.body.appendChild( downloadLink );
								downloadLink.click();
								document.body.removeChild( downloadLink );
								response = {
									success : true,
								}
							}

							changeStep( 3 );

							const stepCompleted = $( '#step-completed' );

							if( response.success ){
								const msg = actionPerformed === 'import' ? yith_ywpar_export_import.msg_import_done : yith_ywpar_export_import.msg_export_done;
								stepCompleted.find( '.error' ).hide();
								stepCompleted.find( '.success' ).show();
								stepCompleted.find( '.message' ).text( msg );
							}else{
								stepCompleted.find( '.error' ).show();
								stepCompleted.find( '.success' ).hide();
								response.data.forEach( function( data ){
									stepCompleted.find( '.message' ).text( data.message );
								} );

							}

						},
						complete : function(){
							$( document ).trigger( 'yith-ywpar-import-export-completed' );
						}
					}
				);
			}
		);

	}
);
