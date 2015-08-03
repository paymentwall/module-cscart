{assign var="checkout" value="checkout"}
{if $params.orderId}
    {$params.iframe nofilter}
    {script src="js/addons/paymentwall/func.js"}
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
