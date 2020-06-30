(function($){
    $(document).ready(function(){
        var url = $('base').prop('href');
        $(document).ajaxStop(function(event,request,settings) {
            if ($('.ps-local').length === 0) {
                var psData = localStorage.getItem('ps_data');
                var paymentId = localStorage.getItem('payment_id');
                var paymentChecked = $('#radio_' + paymentId);
                var selectedPs = localStorage.getItem('selected_ps');
                $('label#payments_' + paymentId).parent('.litecheckout__shipping-method.litecheckout__field.litecheckout__field--xsmall').addClass('pw-hide');
                if ($('.ps-local').length === 0 && psData !== null) {
                    renderHTMLPaymentLocal(JSON.parse(psData));
                }
                if (paymentChecked.prop('checked') == false) {
                    $('label.ps-local-' + selectedPs).removeClass('ps-local-payment-selected');
                } else {
                    $('label.ps-local-' + selectedPs).addClass('ps-local-payment-selected');
                }
            }
        });

        if ($('#litecheckout_payments_form').length) {
            var psData = localStorage.getItem('ps_data');
            var currentTime = new Date().getTime();
            var psExpiryTime = localStorage.getItem('ps_expiry_time');
            if (psData == null || currentTime >= psExpiryTime || $('.ps-local').length === 0) {
                getHTMLPaymentLocal();
            }

        }

        $(document).on('click', '.ps-local', function() {
            var selectedPs = $(this).data('paypent-system');
            var paymentId = localStorage.getItem('payment_id');
            $(this).children('label').addClass('ps-local-payment-selected');
            $('#payments_' + paymentId).click();
            localStorage.setItem('selected_ps', selectedPs);

            $.ajax({
                type: 'post',
                caching: false,
                url: url,
                data: {
                    'dispatch': 'paymentwall.selected',
                    'selected_ps': selectedPs
                },
                success: function(response) {
                    console.log('set session ps success');
                }
            });
        });

        function getHTMLPaymentLocal() {
            $.ajax({
                type: 'get',
                caching: false,
                url: url,
                data: {
                    'dispatch' : 'paymentwall.ps',
                    'action': 'get_ps_system'
                },
                beforeSend: function() {
                    $('#ajax_overlay').show();
                    $('#ajax_loading_box').show();
                },
                success: function(response) {
                    $('#ajax_overlay').hide();
                    $('#ajax_loading_box').hide();
                    var res = JSON.parse(response);
                    var paymentChecked = $('#radio_' + res.payment_id);
					if (paymentChecked.prop('checked') != false) {
						paymentChecked.prop('checked', false);
					}

                    if (res.status == 'success' && typeof res.data.error == 'undefined') {
                        var date = new Date();
                        var psExpiryTime = date.setMinutes(date.getMinutes() + 10);
                        $('label#payments_' + res.payment_id).parent('.litecheckout__shipping-method.litecheckout__field.litecheckout__field--xsmall').addClass('pw-hide');
                        renderHTMLPaymentLocal(res.data);
                        // localStorage.setItem('selected_ps',  res.data[0].id);
                        localStorage.setItem('ps_data', JSON.stringify(res.data));
                        localStorage.setItem('payment_id', res.payment_id);
                        localStorage.setItem('ps_expiry_time', psExpiryTime);
                    } else {
                        localStorage.removeItem('selected_ps');
                        localStorage.removeItem('ps_data');
                        localStorage.removeItem('payment_id');
                    }
                },
                error: function(error) {
                    $('#ajax_overlay').hide();
                    $('#ajax_loading_box').hide();
                    console.log('Ajax request error');
                }
            });
        }

        function renderHTMLPaymentLocal(data) {
            var html = '';
            if (data.length > 0) {
                for (var i = 0; i < data.length; i++) {
                    html += '<div class="ps-local litecheckout__shipping-method litecheckout__field litecheckout__field--xsmall" data-paypent-system="'+ data[i].id  +'">';
                    html += '<label class="litecheckout__shipping-method__wrapper js-litecheckout-toggle ps-local-'+ data[i].id  +'" for="' + data[i].id + '">';
                    html += '<div class="litecheckout__shipping-method__logo">';
                    html += '<img src="' + data[i].img_url + '" class="litecheckout__shipping-method__logo-image" />';
                    html += '</div>';
                    html += '<p class="litecheckout__shipping-method__delivery-time">Pay via ' + data[i].name + '</p>';
                    html += '</label>';
                    html += '</div>';
                }
                html += '<style type="text/css">.pw-hide{display: none !important;}</style>';
                $('#litecheckout_step_payment .litecheckout__section > .litecheckout__group:not(".litecheckout__payment-methods")').append(html);
            }
        }
    });
})(Tygh.$);