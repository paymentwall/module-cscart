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
    fn_paymentwall_initPaymentwallSdk($paymentInfo['key'], $paymentInfo['secret']);

    $widget = new Paymentwall_Widget(
        empty($orderInfo['user_id']) ? $orderInfo['ip_address'] : $orderInfo['user_id'],
        $paymentInfo['widget_type'],
        array(
            new Paymentwall_Product(
                $orderInfo['order_id'],
                fn_paymentwall_getRealPrice($orderInfo),
                $orderInfo['secondary_currency'],
                'Order #' . $orderInfo['order_id']
            )
        ),
        array_merge(
            array(
                'email' => $orderInfo['email'],
                'payment_id' => $orderInfo['payment_id'],
                'integration_module' => 'cs_cart',
                'test_mode' => $paymentInfo['test_mode']
            ),
            fn_paymentwall_prepare_user_profile_data($orderInfo)
        ));

    // Generate Widget
    return $widget->getHtmlCode(array_merge(array(
        'width' => '100%',
        'height' => 400,
        'frameborder' => 0
    ), $extraAttr));
}

function fn_paymentwall_handlePingback($configs)
{
    fn_paymentwall_initPaymentwallSdk($configs['key'], $configs['secret']);

    unset($_GET['dispatch']);
    $pingback = new Paymentwall_Pingback($_GET, $_SERVER['REMOTE_ADDR']);

    if ($pingback->validate()) {

        $paymentInfo = fn_paymentwall_getPaymentConfigs($pingback->getParameter('payment_id'));
        $orderInfo = fn_get_order_info($pingback->getProductId());

        if ($pingback->isDeliverable()) {

            // Call Delivery Confirmation API
            if ($paymentInfo['enable_delivery']) {
                // Delivery Confirmation
                $delivery = new Paymentwall_GenerericApiObject('delivery');
                $response = $delivery->post(fn_paymentwall_prepare_delivery_confirmation(
                    $orderInfo,
                    $pingback->getParameter('ref'),
                    $paymentInfo['test_mode']
                ));
                
            }

            // Update order status: Processed
            fn_paymentwall_updateOrderStatus($pingback->getProductId(), PW_ORDER_STATUS_PROCESSED);

        } elseif ($pingback->isCancelable()) {
            fn_paymentwall_updateOrderStatus($pingback->getProductId(), PW_ORDER_STATUS_CANCELED);
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

/**
 * @param $orderInfo
 * @param $ref
 * @param $isTest
 * @return array
 */
function fn_paymentwall_prepare_delivery_confirmation($orderInfo, $ref, $isTest = false)
{
    return array(
        'payment_id' => $ref,
        'type' => 'digital',
        'status' => 'delivered',
        'estimated_delivery_datetime' => date('Y/m/d H:i:s'),
        'estimated_update_datetime' => date('Y/m/d H:i:s'),
        'is_test' => $isTest,
        'reason' => 'none',
        'refundable' => 'yes',
        'details' => 'Item will be delivered via email by ' . date('Y/m/d H:i:s'),
        'product_description' => '',
        'shipping_address[country]' => $orderInfo['s_country'],
        'shipping_address[city]' => $orderInfo['s_city'],
        'shipping_address[zip]' => $orderInfo['s_zipcode'],
        'shipping_address[state]' => $orderInfo['s_state'],
        'shipping_address[street]' => $orderInfo['s_address'] . ($orderInfo['s_address_2'] ? "\n" . $orderInfo['s_address'] : ""),
        'shipping_address[phone]' => $orderInfo['s_phone'],
        'shipping_address[email]' => $orderInfo['email'],
        'shipping_address[firstname]' => $orderInfo['s_firstname'],
        'shipping_address[lastname]' => $orderInfo['s_lastname'],
    );
}

function fn_paymentwall_prepare_user_profile_data($orderInfo)
{
    return array(
        'customer[city]' => $orderInfo['b_city'],
        'customer[state]' => $orderInfo['b_state'],
        'customer[address]' => $orderInfo['b_address'],
        'customer[country]' => $orderInfo['b_county'],
        'customer[zip]' => $orderInfo['b_zipcode'],
        'customer[username]' => $orderInfo['user_id'] ? $orderInfo['user_id'] : $orderInfo['ip_address'],
        'customer[firstname]' => $orderInfo['b_firstname'],
        'customer[lastname]' => $orderInfo['b_lastname'],
        'email' => $orderInfo['email'],
    );
}