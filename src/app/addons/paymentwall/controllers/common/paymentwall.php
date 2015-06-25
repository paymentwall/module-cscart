<?php

if (!defined('AREA')) { die('Access denied');}

use Tygh\Registry;

if ($mode == 'ajax') {
    $status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
    if ($status == "P") {
        fn_clear_cart($_SESSION['cart']);
    }
    die($status);
}

if ($mode == 'frame') {
    Tygh::$app['view']->assign('orderId', $_REQUEST['order_id']);
}

// Payment widget
if ($mode == 'payment') {

    $rid = $_REQUEST['order_id'];
    $sid = $_SESSION['pw_order_id'];
    $iframe = '';
    $baseUrl = fn_url();
    $matchOrder = ($rid == $sid);
    $orderId = $sid;
    $orderInfo = fn_get_order_info($orderId);

    if ($matchOrder && $orderInfo) {
        // Get Payment Info
        $paymentInfo = fn_paymentwall_getPaymentConfigs($orderInfo['payment_id']);

        // Prepare Widget
        $iframe = fn_paymentwall_generateWidget($orderInfo, $paymentInfo);
    }

    fn_add_breadcrumb('Paymentwall Payment', '#', true);

    Tygh::$app['view']->assign('matchOrder', $matchOrder);
    Tygh::$app['view']->assign('orderId', $orderId);
    Tygh::$app['view']->assign('baseUrl', $baseUrl);
    Tygh::$app['view']->assign('iframe', $iframe);
}

if ($mode == 'pingback') {

    unset($_GET['dispatch']);

    $orderId = isset($_GET['goodsid']) ? $_GET['goodsid'] : null;
    $paymentId = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;
    $type = isset($_GET['type']) ? $_GET['type'] : null;

    $configs = fn_paymentwall_getPaymentConfigs($paymentId);
    $result = fn_paymentwall_handlePingback($configs, $orderId, $type);

    if ($result) die(PW_DEFAULT_PINGBACK_RESPONSE);

    exit;
}
