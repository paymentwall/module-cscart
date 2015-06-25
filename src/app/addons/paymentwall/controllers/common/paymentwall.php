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
    $orderId = $_SESSION['pw_order_id'];
    $iframe = '';
    $matchOrder = ($rid == $orderId);
    $orderInfo = fn_get_order_info($orderId);

    if ($matchOrder && $orderInfo) {
        // Prepare Widget
        $iframe = fn_paymentwall_generateWidget($orderInfo, fn_paymentwall_getPaymentConfigs($orderInfo['payment_id']));
    }

    fn_add_breadcrumb('Paymentwall Payment', '#', true);

    Tygh::$app['view']->assign('params', array(
        'matchOrder' => $matchOrder,
        'orderId' => $orderId,
        'baseUrl' => fn_url(),
        'iframe' => $iframe
    ));
}

function modePingback()
{
    $result = fn_paymentwall_handlePingback(
        fn_paymentwall_getPaymentConfigs(isset($_GET['payment_id']) ? $_GET['payment_id'] : null)
    );

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
        case PW_ORDER_STATUS_PROCESSED:
            // Order Processed : Clear shopping cart
            fn_clear_cart($_SESSION['cart']);
            break;
        default:
            break;
    }
}
