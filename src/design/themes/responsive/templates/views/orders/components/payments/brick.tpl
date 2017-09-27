{script src="https://api.paymentwall.com/brick/brick.1.3.js"}

{if $card_id}
    {assign var="id_suffix" value="`$card_id`"}
{else}
    {assign var="id_suffix" value=""}
{/if}

<div class="clearfix">
    <div class="ty-credit-card">
        <div class="ty-credit-card__control-group ty-control-group">
            <label for="credit_card_number_{$id_suffix}"
                   class="ty-control-group__title">{__("card_number")}</label>
            <input size="35" type="text" id="credit_card_number_{$id_suffix}" name="payment_info[card_number]" value=""
                   class="ty-credit-card__input cm-autocomplete-off" data-brick="card-number"/>
        </div>

        <div class="ty-credit-card__control-group ty-control-group">
            <label for="credit_card_month_{$id_suffix}"
                   class="ty-control-group__title">{__("valid_thru")}</label>
            <label for="credit_card_year_{$id_suffix}" class="hidden"></label>
            <input type="text" id="credit_card_month_{$id_suffix}" name="payment_info[expiry_month]" size="2"
                   maxlength="2" class="ty-credit-card__input-short" data-brick="card-expiration-month"/>&nbsp;&nbsp;/&nbsp;&nbsp;
            <input type="text" id="credit_card_year_{$id_suffix}" name="payment_info[expiry_year]" size="4"
                   maxlength="4" class="ty-credit-card__input-short" data-brick="card-expiration-year"/>&nbsp;
        </div>

        <div class="ty-control-group cvv-field">
            <label for="credit_card_cvv2_{$id_suffix}"
                   class="ty-control-group__title cm-autocomplete-off">{__("cvv2")}</label>
            <input type="text" id="credit_card_cvv2_{$id_suffix}" name="payment_info[cvv2]" size="4" maxlength="4"
                   class="ty-credit-card__cvv-field-input" data-brick="card-cvv"/>
            <input type="hidden" name="brick[token]" id="brick-token"/>
            <input type="hidden" name="brick[fingerprint]" id="brick-fingerprint"/>
            <input type="hidden" id="brick-get-token-success" value="0"/>

            <div class="ty-cvv2-about">
                <span class="ty-cvv2-about__title">{__("what_is_cvv2")}</span>

                <div class="ty-cvv2-about__note">
                    <div class="ty-cvv2-about__info mb30 clearfix">
                        <div class="ty-cvv2-about__image">
                            <img src="{$images_dir}/visa_cvv.png" alt=""/>
                        </div>
                        <div class="ty-cvv2-about__description">
                            <h5 class="ty-cvv2-about__description-title">{__("visa_card_discover")}</h5>

                            <p>{__("credit_card_info")}</p>
                        </div>
                    </div>
                    <div class="ty-cvv2-about__info clearfix">
                        <div class="ty-cvv2-about__image">
                            <img src="{$images_dir}/express_cvv.png" alt=""/>
                        </div>
                        <div class="ty-cvv2-about__description">
                            <h5 class="ty-cvv2-about__description-title">{__("american_express")}</h5>

                            <p>{__("american_express_info")}</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" class="cm-ajax-force">
    (function (_, $) {
        var brick = new Brick({
            public_key: "{if $cart.payment_method_data.processor_params.test_mode eq 1}{$cart.payment_method_data.processor_params.test_public_key}{else}{$cart.payment_method_data.processor_params.public_key}{/if}",
            form: {
                formatter: true
            }
        }, 'custom');

        $(document).ready(function () {

            $("#place_order_{$tab_id}")
                    .unbind('click.brickToken')
                    .bind('click.brickToken', function (event) {
                        event.preventDefault();

                        if ($('input#payment_{$payment_id}').attr('checked')) {
                            if ($('#brick-get-token-success').val() == 1) {
                                $('#brick-get-token-success').val(0)
                                return true;
                            }

                            brick.tokenizeCard({
                                card_number: $('#credit_card_number_{$id_suffix}').val(),
                                card_expiration_month: $('#credit_card_month_{$id_suffix}').val(),
                                card_expiration_year: $('#credit_card_year_{$id_suffix}').val(),
                                card_cvv: $('#credit_card_cvv2_{$id_suffix}').val()
                            }, function (response) {
                                if (response.type == 'Error') {
                                    // handle errors
                                    alert("Brick error(s):\n" + " - " + ((typeof response.error == 'string') ? response.error : response.error.join("\n - ")));
                                    return false;
                                } else {

                                    $('#brick-token').val(response.token);
                                    $('#brick-fingerprint').val(Brick.getFingerprint());
                                    $('#brick-get-token-success').val(1);

                                    $("form[name=payments_form_{$tab_id}]").submit();
                                }
                            });
                            return false;
                        }
                    });
        });
    })(Tygh, Tygh.$);
</script>
