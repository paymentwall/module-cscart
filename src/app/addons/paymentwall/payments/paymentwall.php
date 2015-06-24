<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Registry;

require_once Registry::get('config.dir.lib') . DS . 'other' . DS . 'paymentwall-php' . DS . 'lib' . DS . 'paymentwall.php';

$coefficient = db_get_field("SELECT coefficient FROM ?:currencies WHERE currency_code = ?s", $order_info['secondary_currency']);
$realPrice = $order_info['total'] / $coefficient;

$orderId = $order_info['order_id'];
fn_change_order_status($orderId, 'O');

// Redirect to widget page
$postUrl = fn_url('paymentwall.payment&order_id=' . $orderId);
$_SESSION['pw_order_id'] = $orderId;
header("Location: {$postUrl}");

// Cancel auto redirect payment
exit;