<?php

if (!defined('AREA')) { die('Access denied'); }

$orderId = $order_info['order_id'];
fn_change_order_status($orderId, 'O');

// Redirect to widget page
$postUrl = fn_url('paymentwall.payment&order_id=' . $orderId);
$_SESSION['pw_order_id'] = $orderId;
header("Location: {$postUrl}");

// Cancel auto redirect payment
exit;