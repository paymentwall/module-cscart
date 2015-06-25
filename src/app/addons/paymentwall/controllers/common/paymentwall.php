<?php

if (!defined('AREA')) { die('Access denied'); }

// Process request mode
handleDispatch($mode);

function modeAjax()
{
    $status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
    orderStatusProcessor($status);
    die($status);
}

function modeFrame()
{
    Tygh::$app['view']->assign('orderId', $_REQUEST['order_id']);
}

// Payment widget
function modePayment()
{
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

    Tygh::$app['view']->assign('params', array(
        'matchOrder' => $matchOrder,
        'orderId' => $orderId,
        'baseUrl' => $baseUrl,
        'iframe' => $iframe
    ));
}

function modePingback()
{
    unset($_GET['dispatch']);

    $orderId = isset($_GET['goodsid']) ? $_GET['goodsid'] : null;
    $paymentId = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;
    $type = isset($_GET['type']) ? $_GET['type'] : null;

    $configs = fn_paymentwall_getPaymentConfigs($paymentId);
    $result = fn_paymentwall_handlePingback($configs, $orderId, $type);

    if ($result) die(PW_DEFAULT_PINGBACK_RESPONSE);

    exit;
}

function handleDispatch($mode)
{
    switch ($mode) {
        case 'pingback':
            modePingback();
            break;
        case 'payment':
            modePayment();
            break;
        case 'frame':
            modePayment();
            break;
        case 'ajax':
            modeAjax();
            break;
        default:
            break;
    }
}

function orderStatusProcessor($status)
{
    switch ($status) {
        case 'P':
            // Order Processed : Clear shopping cart
            fn_clear_cart($_SESSION['cart']);
            break;
        default:
            break;
    }
}
