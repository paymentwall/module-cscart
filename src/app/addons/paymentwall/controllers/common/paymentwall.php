<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// Process request mode
handleDispatch($mode);

function modeAjax()
{
    $status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
    die($status);
}

function modeFrame()
{
    Tygh::$app['view']->assign('orderId', $_REQUEST['order_id']);
}

// Payment widget
function modePayment()
{
    $orderId = isset($_SESSION['pw_order_id']) && $_SESSION['pw_order_id'] ? $_SESSION['pw_order_id'] : false;
    $iframe = '';

    if ($orderId && $orderInfo = fn_get_order_info($orderId)) {
        // Prepare Widget
        $iframe = fn_paymentwall_generate_widget($orderInfo, $orderInfo['payment_method']['processor_params']);
    } else {

    }

    // Clear Shopping Cart Session
    fn_clear_cart($_SESSION['cart']);
    unset($_SESSION['pw_order_id']);
    fn_add_breadcrumb('Paymentwall Payment', '#', true);

    Tygh::$app['view']->assign('params', array(
        'orderId' => $orderId,
        'baseUrl' => fn_url(),
        'iframe' => $iframe,
    ));
}

function modePingback()
{
    $result = fn_paymentwall_handle_pingback();

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
