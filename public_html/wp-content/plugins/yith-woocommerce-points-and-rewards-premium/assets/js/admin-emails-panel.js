jQuery(function( $ ) {

    const showHideUpdatePointMailOption = () => {
        const value = $( '#ywpar_update_point_mail_time input:checked' ).val();
        const container = $( '#ywpar_update_point_mail_on_admin_action' ).closest( '.yit-admin-panel-container' );
        const display = 'every_update' === value ? 'block' : 'none';
        container.css( "display", display );
    }

    $( document ).on( 'click', '.toggle-settings', function( e ){
        e.preventDefault();
        $( this ).closest( '.ywcpar-row' ).toggleClass( 'active' );
        const target = $( this ).data( 'target' );
        $( '#'+target ).slideToggle();

    } );


    $( document ).on( 'change', '#ywpar_update_point_mail_time', showHideUpdatePointMailOption );
    showHideUpdatePointMailOption();

    $( document ).on( 'click', '.ywpar-save-settings', function( e ){
        e.preventDefault();
        $( this ).closest( 'form' ).find( '.wp-switch-editor.switch-html' ).trigger('click');
        const email_key = $( this.closest( '.email-settings' ) ).attr( 'id' );
        const data = {
            'action' : 'ywpar_save_email_settings',
            'params' : $( this ).closest( 'form' ).serialize(),
            'email_key'    : email_key,
            'nonce' : yith_ywpar_emails.email_settings,
        }
        $.ajax( {
            type    : "POST",
            data    : data,
            url     : ajaxurl,
            success : function ( response ) {
                const row_active = $( '.ywcpar-row.active' );
                row_active.find( '.email-settings' ).slideToggle();
                row_active.toggleClass( 'active' );
            },
        });
    } )

    $( document ).on( 'change', '#ywpar-email-status', function(){

        const data = {
            'action'    : 'ywpar_save_email_status',
            'enabled'   : $(this).val(),
            'email_key' : $(this).closest('.yith-plugin-fw-onoff-container ').data('email_key'),
            'option_name' : $( this ).attr('name'),
            'nonce' : yith_ywpar_emails.email_status,
        }

        $.ajax( {
            type    : "POST",
            data    : data,
            url     : ajaxurl,
            success : function ( response ) {
                console.log('here');
            },
        });

    } )


});