<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

$coefficient = db_get_field("SELECT coefficient FROM ?:currencies WHERE currency_code = ?s", $order_info['secondary_currency']);
$order_id = $order_info['order_id'];
$realPrice = $order_info['total'] / $coefficient;
$products = "Order #" . $order_id;
$params = array(
    'key' => $processor_data['processor_params']['key'],
    'uid' => empty($order_info['user_id']) ? $order_info['ip_address'] : $order_info['user_id'],
    'widget' => $processor_data['processor_params']['widget_type'],
    'sign_version' => 2,
    'amount' => $realPrice,
    'currencyCode' => $order_info['secondary_currency'],
    'ag_name' => $products,
    'ag_external_id' => $order_id,
    'ag_type' => 'fixed'
);
$secret = $processor_data['processor_params']['secret'];
ksort($params);
// generate the base string
$baseString = '';
foreach ($params as $key => $value) {
    $baseString .= $key . '=' . $value;
}
$baseString .= $secret;
$params['sign'] = md5($baseString);
$par = http_build_query($params);
$url = 'https://wallapi.com/api/subscription';
?>
    <html>
    <head>
        <script type="text/javascript" src="<?php echo fn_url(); ?>js/jquery.js"></script>
    </head>
    <body>
    <iframe src="<?php echo $url; ?>?<?php echo $par; ?>" width="100%" height="828" frameborder="0"></iframe>
    <script type="text/javascript">
        $(document).ready(function () {
            setInterval(function () {
                $.post('<?php echo fn_url(); ?>index.php?dispatch=paymentwall.ajax',
                    {
                        order_id: '<?php echo $order_id;?>'
                    },
                    function (data) {
                        if (data == 'P') {
                            location.href = "<?php echo fn_url(); ?>index.php?dispatch=checkout.complete&order_id=<?php echo $order_id;?>"
                        }
                    });
            }, 5000);
        });
    </script>
    </body>
    </html>
<?php
// Cancel auto redirect payment
exit;
?>