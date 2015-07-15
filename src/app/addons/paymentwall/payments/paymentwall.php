<?php

if (!defined('AREA')) { die('Access denied'); }

fn_change_order_status($order_info['order_id'], 'O');

// Redirect to widget page
$postUrl = fn_url('paymentwall.payment&order_id=' . $order_info['order_id']);
$_SESSION['pw_order_id'] = $order_info['order_id'];
header("Location: {$postUrl}");

// Cancel auto redirect payment
exit;