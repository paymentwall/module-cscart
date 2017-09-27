<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_change_order_status($order_info['order_id'], PW_ORDER_STATUS_OPEN);

// Redirect to widget page
$postUrl = fn_url('paymentwall.payment');
$_SESSION['pw_order_id'] = $order_info['order_id'];
header("Location: {$postUrl}");

// Cancel auto redirect payment
exit;