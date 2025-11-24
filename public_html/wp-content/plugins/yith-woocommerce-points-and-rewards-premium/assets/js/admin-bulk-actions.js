jQuery(
    function ($) {

        const initModal = () => {
            $( '.yith_woocommerce_points_and_rewards_panel_page' ).find('[data-deps]').each(function () {
                var t = $(this),
                    input = t.find('input'),
                    deps = t.attr('data-deps').split(','),
                    values = t.attr('data-deps_value').split(','),
                    conditions = [];

                $.each(deps, function (i, dep) {
                    $('[id="' + dep + '"]').find( 'input' ).first().click();
                    $('[id="' + dep + '"]').on('change', function () {

                        var value = this.value,
                            check_values = '';

                        // exclude radio if not checked
                        if (this.type == 'radio' && !$(this).is(':checked')) {
                            return;
                        }else

                        if (this.type == 'checkbox') {
                            value = $(this).is(':checked') ? 'yes' : 'no';

                        }

                        check_values = values[i] + ''; // force to string
                        check_values = check_values.split('|');
                        conditions[i] = $.inArray(value, check_values) !== -1;

                        if ($.inArray(false, conditions) === -1) {
                            t.show();
                        } else {
                            t.hide();
                        }

                    }).change();
                });
            });

            $('#ywpar_bulk_action_points').on('click', function (e) {
                e.preventDefault();
                window.onbeforeunload = '';

                var form = $(this).closest('form'),
                    form_values = form.serializeArray(),
                    container = $('.ywpar-bulk-actions-modal-content .loading');

				/* force date to be of format year-month-day */
				if ( form_values[2].value === 'add_points_to_orders' && form_values[7] !== undefined && form_values[7].value === 'from' && form_values[8] !== undefined && form_values[8].name === 'ywpar_apply_points_previous_order_start_date' && form_values[8].value !== undefined ) {
					form_values[8].value = new Date(form_values[8].value).toISOString().split('T')[0];
				}

                if ("from" === form_values.ywpar_apply_points_previous_order_to && "" === form_values.ywpar_apply_points_previous_order) {
                    $('#ywpar_apply_points_previous_order').addClass('ywpar-error');
                    return;
                }

                container.show();

                $('.ywpar-bulk-trigger').append('<div class="ywpar-bulk-progress"><div>0%</div></div>');

                process_step(1, form_values, form);

            });

            var process_step = function (step, data, form) {

                var block_container = $('.ywpar-bulk-trigger'),
                    container = $('.ywpar-bulk-actions-modal-content .loading');
                data.push( { name: 'action', value: 'ywpar_bulk_action' }, { name: 'step', value: step } );
                $.ajax({
                    type: 'POST',
                    url: yith_ywpar_bulk_actions.ajax_url,
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            if ('done' === response.data.step) {
                                block_container.find('.ywpar-bulk-progress').hide('slow').remove();
                                const mainContainer = $( '#yith_woocommerce_points_and_rewards_bulk-container' ),
                                    responseWrapper = mainContainer.find('.responseWrapper');
                                responseWrapper.find( '.message' ).text ( response.data.message );
                                mainContainer.find('.optionContainer').hide();
                                container.hide();
                                $( '.ywpar-modal-bulk-actions-wrapper').removeClass( 'loading' );
                                responseWrapper.show();


                                setTimeout(function () {
                                    $('span.ywpar-bulk-response').remove();
                                    window.location.reload();
                                }, 2000);
                            } else {
                                block_container.find('.ywpar-bulk-progress div').html(response.data.percentage + '%');
                                block_container.find('.ywpar-bulk-progress div').animate({
                                    width: response.data.percentage + '%',
                                }, 50, function () {
                                    // Animation complete.
                                });
                                process_step(parseInt(response.data.step), data, form);
                            }
                        } else {
                            $(document).find('.ywpar-bulk-trigger').append('<span class="ywpar-bulk-response"><i class="yith-icon yith-icon-check"></i>' + response.data.error + '</span>');
                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                        }
                    }
                });

            };

            $( '#ywpar_apply_points_previous_order_start_date' ).datepicker();

            $( document.body ).trigger( 'yith-framework-enhanced-select-init' );

            $( document.body ).trigger( 'wc-enhanced-select-init' );
        };

        $( document ).on(
            'click',
            '.bulk-actions-custom-button',
            function(ev){
                ev.preventDefault();

                yith.ui.modal(
                    {
                        content: $( 'script[data-template="ywpar-modal-bulk-actions"]' ).text().split( /\$\{(.+?)\}/g ),
                        title: yith_ywpar_bulk_actions.modal_title,
                        width: 900,
                        allowWpMenu: false,
                        closeWhenClickingOnOverlay: true,
                        allowClosingWithEsc: true,
                        classes: {
                            title: 'ywpar-bulk-actions-modal-title',
                            content: 'ywpar-bulk-actions-modal-content',
                            footer: 'ywpar-bulk-actions-modal-footer',
                            wrap: 'ywpar-modal-bulk-actions-wrapper'
                        },
                        onCreate: initModal,

                    }
                );
            }
        );


    }
);
