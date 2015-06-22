<?php

if (!defined('BOOTSTRAP')) { die('Access denied');}

use Tygh\Registry;
require_once Registry::get('config.dir.lib') . DS . 'other' . DS . 'paymentwall-php' . DS . 'lib' . DS . 'paymentwall.php';

Paymentwall_Config::getInstance()->set(array(
    'api_type' => Paymentwall_Config::API_GOODS,
    'public_key' => $processor_data['processor_params']['key'],
    'private_key' => $processor_data['processor_params']['secret']
));

$coefficient = db_get_field("SELECT coefficient FROM ?:currencies WHERE currency_code = ?s", $order_info['secondary_currency']);

$order_id = $order_info['order_id'];
$realPrice = $order_info['total'] / $coefficient;
$products = array();
$uid = empty($order_info['user_id']) ? $order_info['ip_address'] : $order_info['user_id'];
$widget_code = $processor_data['processor_params']['widget_type'];
$currencyCode = $order_info['secondary_currency'];

if(count($order_info['products']) > 0){
    $products[] = new Paymentwall_Product($order_id, $realPrice, $currencyCode,  'Order #' . $order_id);
}
?>
    <html>
    <head>
    </head>
    <body>
    <?php
        $widget  = new Paymentwall_Widget($uid, $widget_code, $products, array(
            'email' => $order_info['email'],
            'payment_id' => $order_info['payment_id']
        ));

        // Generate iframe
        echo $widget->getHtmlCode(array(
            'width' => '100%',
            'height' => 400,
            'frameborder' => 0
        ));
    ?>
    <script type="text/javascript">
        (function () {
            setInterval(function () {
                var r = new XMLHttpRequest();
                r.open("POST", '<?php echo fn_url(); ?>index.php?dispatch=paymentwall.ajax', true);
                r.onreadystatechange = function () {
                    if (r.readyState != 4 || r.status != 200) return;
                    if (r.responseText == 'P') {
                        location.href = "<?php echo fn_url(); ?>index.php?dispatch=checkout.complete&order_id=<?php echo $order_id;?>"
                    }
                };
                var formData = new FormData();
                formData.append('order_id', '<?php echo $order_id;?>');
                r.send(formData);
            }, 5000);
        })();
    </script>
    </body>
    </html>
<?php
// Cancel auto redirect payment
exit;