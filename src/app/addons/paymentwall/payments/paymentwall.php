<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Registry;

require_once Registry::get('config.dir.lib') . DS . 'other' . DS . 'paymentwall-php' . DS . 'lib' . DS . 'paymentwall.php';

Paymentwall_Config::getInstance()->set(array(
    'api_type' => Paymentwall_Config::API_GOODS,
    'public_key' => $processor_data['processor_params']['key'],
    'private_key' => $processor_data['processor_params']['secret']
));

$coefficient = db_get_field("SELECT coefficient FROM ?:currencies WHERE currency_code = ?s", $order_info['secondary_currency']);
$realPrice = $order_info['total'] / $coefficient;

$orderId = $order_info['order_id'];
fn_change_order_status($orderId, 'O');

$products = array();
$uid = empty($order_info['user_id']) ? $order_info['ip_address'] : $order_info['user_id'];
$widget_code = $processor_data['processor_params']['widget_type'];
$currencyCode = $order_info['secondary_currency'];
$testMode = $processor_data['processor_params']['test_mode'];

if (count($order_info['products']) > 0) {
    $products[] = new Paymentwall_Product($order_id, $realPrice, $currencyCode, 'Order #' . $order_id);
}

$widget = new Paymentwall_Widget($uid, $widget_code, $products, array(
    'email' => $order_info['email'],
    'payment_id' => $order_info['payment_id'],
    'integration_module' => 'cs_cart',
    'test_mode' => $testMode,
    'ref' => rand(99, 999)
));

// Generate iframe
$iframe = $widget->getHtmlCode(array(
    'width' => '100%',
    'height' => 400,
    'frameborder' => 0
));

Registry::get('view')->assign('iframe', $iframe);
Registry::get('view')->assign('orderId', $orderId);
Registry::get('view')->assign('baseUrl', fn_url());
Registry::get('view')->display('addons/paymentwall/window.tpl');

// Cancel auto redirect payment
exit;