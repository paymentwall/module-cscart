{script src="js/addons/paymentwall/func.js"}
{assign var="checkout" value="checkout"}
{if $params.orderId}
    <h1>Paymentwall</h1>
    <p>{__("widget_notice")}</p>
    {$params.iframe nofilter}
    <script type="text/javascript">
        (function(){
            paymentListener('{$params.orderId}', '{$params.baseUrl}');
        })()
    </script>
{else}
    <div>
        <h3>{__('pw_error_widget')}</h3>
        <p>{__('pw_redirect')}</p>
    </div>
    <script type="text/javascript">
        window.setTimeout(function(){
            window.location.href = "{""|fn_url}{$checkout}";
        }, 3000);
    </script>
{/if}
