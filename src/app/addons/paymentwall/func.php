<?php

if (!defined('AREA')) { die('Access denied'); }

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

function fn_paymentwall_generateWidget($orderInfo,$paymentInfo, $extraAttr = array())
{
    fn_paymentwall_initPaymentwallSdk($paymentInfo['key'], $paymentInfo['secret']);

    $uid = empty($orderInfo['user_id']) ? $orderInfo['ip_address'] : $orderInfo['user_id'];
    $products = array();
    $realPrice = fn_paymentwall_getRealPrice($orderInfo);

    if (count($orderInfo['products']) > 0) {
        $products[] = new Paymentwall_Product($orderInfo['order_id'], $realPrice, $orderInfo['secondary_currency'], 'Order #' . $orderInfo['order_id']);
    }

    $widget = new Paymentwall_Widget($uid, $paymentInfo['widget_type'], $products, array(
        'email' => $orderInfo['email'],
        'payment_id' => $orderInfo['payment_id'],
        'integration_module' => 'cs_cart',
        'test_mode' => $paymentInfo['test_mode'],
        'ref' => rand(99, 999),
    ));

    $defaultAttr = array(
        'width' => '100%',
        'height' => 400,
        'frameborder' => 0
    );
    $extraAttr = array_merge($defaultAttr, $extraAttr);

    // Generate Widget
    return $widget->getHtmlCode($extraAttr);
}

function fn_paymentwall_handlePingback($configs, $orderId, $type)
{
    fn_paymentwall_initPaymentwallSdk($configs['key'], $configs['secret']);

    $params = array_merge($_GET, array(
        'sign_version' => Paymentwall_Signature_Abstract::VERSION_THREE
    ));

    $pingback = new Paymentwall_Pingback($params, $_SERVER['REMOTE_ADDR']);

    if ($pingback->validate()) {
        if ($type == PW_CREDIT_TYPE_CHARGEBACK) {
            // Update order status: Canceled
            fn_paymentwall_updateOrderStatus($orderId, 'I');

            // Take membership from user
            // This is optional, but we recommend this type of crediting to be implemented as well
            //$this->model_checkout_order->update($goodsId, $this->config->get('cancel_status'));
        } else {
            // Update order status: Processed
            fn_paymentwall_updateOrderStatus($orderId, 'P');

            // Give membership to user
            //$this->model_checkout_order->confirm($goodsId, $this->config->get('complete_status'));
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
    $data = array(
        'status' => $status
    );
    db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', $data, $orderId);
}

function fn_paymentwall_getRealPrice($orderInfo){
    $coefficient = db_get_field(
        "SELECT coefficient FROM ?:currencies WHERE currency_code = ?s",
        $orderInfo['secondary_currency']
    );
    return $orderInfo['total'] / $coefficient;
}