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

        $coefficient = db_get_field("SELECT coefficient FROM ?:currencies WHERE currency_code = ?s", $orderInfo['secondary_currency']);
        $realPrice = $orderInfo['total'] / $coefficient;
        $paymentInfo = getPaymentConfigs($orderInfo['payment_id']);

        // Prepare Widget Data
        $uid = empty($orderInfo['user_id']) ? $orderInfo['ip_address'] : $orderInfo['user_id'];
        $widgetCode = $paymentInfo['widget_type'];
        $currencyCode = $orderInfo['secondary_currency'];
        $testMode = $paymentInfo['test_mode'];
        $products = array();

        Paymentwall_Config::getInstance()->set(array(
            'api_type' => Paymentwall_Config::API_GOODS,
            'public_key' => $paymentInfo['key'],
            'private_key' => $paymentInfo['secret']
        ));

        if (count($orderInfo['products']) > 0) {
            $products[] = new Paymentwall_Product($orderId, $realPrice, $currencyCode, 'Order #' . $orderId);
        }

        $widget = new Paymentwall_Widget($uid, $widgetCode, $products, array(
            'email' => $orderInfo['email'],
            'payment_id' => $orderInfo['payment_id'],
            'integration_module' => 'cs_cart',
            'test_mode' => $testMode,
            'ref' => rand(99, 999)
        ));

        // Generate Widget
        $iframe = $widget->getHtmlCode(array(
            'width' => '100%',
            'height' => 400,
            'frameborder' => 0
        ));
    }

    fn_add_breadcrumb('Paymentwall Payment', '#', true);

    Tygh::$app['view']->assign('matchOrder', $matchOrder);
    Tygh::$app['view']->assign('orderId', $orderId);
    Tygh::$app['view']->assign('baseUrl', $baseUrl);
    Tygh::$app['view']->assign('iframe', $iframe);
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

    if ($pingback->validate()) {
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