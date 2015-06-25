<?php

if (!defined('AREA')) {
    die('Access denied');
}

use Tygh\Registry;

require_once Registry::get('config.dir.lib') . '/other/paymentwall-php/lib/paymentwall.php';

function fn_paymentwall_uninstall_payment_processors()
{
    db_query("DELETE FROM ?:payment_descriptions WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('paymentwall.php')))");
    db_query("DELETE FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('paymentwall.php'))");
    db_query("DELETE FROM ?:payment_processors WHERE processor_script IN ('paymentwall.php')");
}


function fn_paymentwall_initPaymentwallSdk($projectKey, $secretKey, $apiType = Paymentwall_Config::API_GOODS)
{
    Paymentwall_Config::getInstance()->set(array(
        'api_type' => $apiType,
        'public_key' => $projectKey,
        'private_key' => $secretKey
    ));
}

function fn_paymentwall_generateWidget($orderInfo, $paymentInfo, $extraAttr = array())
{
    $defaultAttr = array(
        'width' => '100%',
        'height' => 400,
        'frameborder' => 0
    );

    fn_paymentwall_initPaymentwallSdk($paymentInfo['key'], $paymentInfo['secret']);

    $realPrice = fn_paymentwall_getRealPrice($orderInfo);

    $widget = new Paymentwall_Widget(
        empty($orderInfo['user_id']) ? $orderInfo['ip_address'] : $orderInfo['user_id'],
        $paymentInfo['widget_type'],
        array(
            new Paymentwall_Product(
                $orderInfo['order_id'],
                $realPrice,
                $orderInfo['secondary_currency'],
                'Order #' . $orderInfo['order_id']
            )
        ), array(
        'email' => $orderInfo['email'],
        'payment_id' => $orderInfo['payment_id'],
        'integration_module' => 'cs_cart',
        'test_mode' => $paymentInfo['test_mode']
    ));

    // Generate Widget
    return $widget->getHtmlCode(array_merge($defaultAttr, $extraAttr));
}

function fn_paymentwall_handlePingback($configs)
{
    fn_paymentwall_initPaymentwallSdk($configs['key'], $configs['secret']);

    $pingback = new Paymentwall_Pingback($_GET, $_SERVER['REMOTE_ADDR']);

    if ($pingback->validate()) {
        if ($pingback->getType() == PW_CREDIT_TYPE_CHARGEBACK) {
            // Update order status: Canceled
            fn_paymentwall_updateOrderStatus($pingback->getProductId(), PW_ORDER_STATUS_CANCELED);
        } else {
            // Update order status: Processed
            fn_paymentwall_updateOrderStatus($pingback->getProductId(), PW_ORDER_STATUS_PROCESSED);
        }
        return true;
    } else {
        echo $pingback->getErrorSummary();
        return false;
    }
}

function fn_paymentwall_getPaymentConfigs($paymentId)
{
    $processorParams = db_get_field("SELECT processor_params FROM ?:payments WHERE payment_id = ?s", $paymentId);
    return unserialize($processorParams);
}

function fn_paymentwall_updateOrderStatus($orderId, $status)
{
    db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', array(
        'status' => $status
    ),
        $orderId
    );
}

function fn_paymentwall_getRealPrice($orderInfo)
{
    $coefficient = db_get_field(
        "SELECT coefficient FROM ?:currencies WHERE currency_code = ?s",
        $orderInfo['secondary_currency']
    );
    return $orderInfo['total'] / $coefficient;
}