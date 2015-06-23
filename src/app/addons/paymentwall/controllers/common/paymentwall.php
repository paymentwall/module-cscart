<?php
if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Registry;

require_once Registry::get('config.dir.lib') . DS . 'other' . DS . 'paymentwall-php' . DS . 'lib' . DS . 'paymentwall.php';

if ($mode == 'ajax') {
    $status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
    if ($status == "P") {
        fn_clear_cart($_SESSION['cart']);
    }
    die($status);
}

if ($mode == 'frame') {
    $view->assign('orderId', $_REQUEST['order_id']);
}

if ($mode == 'pingback') {

    define('CREDIT_TYPE_CHARGEBACK', 2);
    unset($_GET['dispatch']);

    $orderId = isset($_GET['goodsid']) ? $_GET['goodsid'] : null;
    $paymentId = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    $result = false;

    $configs = getPaymentConfigs($paymentId);
    $result = handlePingback($configs, $orderId, $type);

    if ($result) die('OK');

    exit;
}

function handlePingback($configs, $orderId, $type)
{
    Paymentwall_Config::getInstance()->set(array(
        'api_type' => Paymentwall_Config::API_GOODS,
        'public_key' => isset($configs['key']) ? $configs['key'] : null,
        'private_key' => isset($configs['secret']) ? $configs['secret'] : null,
    ));

    $params = array_merge($_GET, array(
        'sign_version' => Paymentwall_Signature_Abstract::VERSION_THREE
    ));

    $pingback = new Paymentwall_Pingback($params, $_SERVER['REMOTE_ADDR']);

    if ($pingback->validate(true)) {
        if ($type == CREDIT_TYPE_CHARGEBACK) {
            $data = array(
                'status' => 'I'
            );
            db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', $data, $orderId);

            // Take membership from user
            // This is optional, but we recommend this type of crediting to be implemented as well
            //$this->model_checkout_order->update($goodsId, $this->config->get('cancel_status'));
        } else {
            $data = array(
                'status' => 'P'
            );
            db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', $data, $orderId);
            // Give membership to user
            //$this->model_checkout_order->confirm($goodsId, $this->config->get('complete_status'));
        }
        return true;
    } else {
        echo $pingback->getErrorSummary();
        return false;
    }
}

function getPaymentConfigs($paymentId)
{
    $processorParams = db_get_field("SELECT processor_params FROM ?:payments WHERE payment_id = ?s", $paymentId);
    return unserialize($processorParams);
}