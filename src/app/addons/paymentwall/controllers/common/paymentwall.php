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
    $url = '';
    if ($orderId && $orderInfo = fn_get_order_info($orderId)) {
        // Prepare Widget
        $base = fn_url();
        $url = $base . "index.php?dispatch=paymentwall.checkstatus&order_id=" . $orderId;
        $psParam = fn_get_session_data('selected_ps');
        $extrAttrs['success_url'] = $url;
        $extrAttrs['ps'] = !empty($psParam) ? $psParam : '';
        $url = fn_paymentwall_generate_widget_url($orderInfo, $orderInfo['payment_method']['processor_params'], $extrAttrs);
    }
    // Clear session order_id
    unset($_SESSION['pw_order_id']);
    header('Location:' .$url);
    die;
}

function modePingback()
{
    $result = fn_paymentwall_handle_pingback();

    if ($result) die(PW_DEFAULT_PINGBACK_RESPONSE);

    exit;
}

function modeClearCart() {
    if (!empty($_GET['order_id'])) {
        $orderId = $_GET['order_id'];
        $base = fn_url();
        $url = $base . "index.php?dispatch=checkout.complete&order_id=" . $orderId;
        fn_clear_cart($_SESSION['cart']);
        header('Location:' .$url);
        die;
    }
}

function modePaymentSystem() {
    $responseDefault = array(
        'status' => false,
        'msg' => 'Ajax request missing parameter'
    );
    if (
        defined('AJAX_REQUEST') &&
        isset($_GET['action']) &&
        $_GET['action'] == 'get_ps_system'
    ) {
        $response = fn_paymentwall_get_local_payments();
        if (!empty($response) && !isset($response['error'])) {
            $responseDefault = array(
                'status' => 'success',
                'msg' => '',
                'data' => $response,
                'payment_id' =>  fn_paymentwall_get_payment_field('payment_id')
            );
        } else {
            $responseDefault['msg'] = !empty($response['error']) ? $response['error'] : 'Can\'t get payment system';
        }
    }
    $responseObj = json_encode($responseDefault);
    echo $responseObj;
    die;
}

function modeHandleSelectedPs() {
    if (
        defined('AJAX_REQUEST') &&
        !empty($_POST['selected_ps'])
    ) {
        fn_set_session_data('selected_ps', $_POST['selected_ps']);
    }
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
        case 'ps':
            modePaymentSystem();
            break;
        case 'selected':
            modeHandleSelectedPs();
            break;
        case 'checkstatus':
            modeClearCart();
            break;
        default:
            break;
    }
}
