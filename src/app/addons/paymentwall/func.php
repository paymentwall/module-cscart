<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Registry;

require_once Registry::get('config.dir.lib') . '/other/paymentwall-php/lib/paymentwall.php';

function fn_paymentwall_uninstall_payment_processors()
{
    db_query("DELETE FROM ?:payment_descriptions WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('paymentwall.php', 'brick.php')))");
    db_query("DELETE FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('paymentwall.php', 'brick.php'))");
    db_query("DELETE FROM ?:payment_processors WHERE processor_script IN ('paymentwall.php', 'brick.php')");
}

function fn_paymentwall_init_configs($projectKey, $secretKey, $apiType = Paymentwall_Config::API_GOODS)
{
    Paymentwall_Config::getInstance()->set(array(
        'api_type' => $apiType,
        'public_key' => $projectKey,
        'private_key' => $secretKey
    ));
}

function fn_paymentwall_generate_widget($orderInfo, $paymentInfo, $extraAttr = array())
{
    $widget = fn_paymentwall_prepare_widget($orderInfo, $paymentInfo, $extraAttr = []);
    // Generate Widget
    return $widget->getHtmlCode(array(
        'width' => '100%',
        'height' => 400,
        'frameborder' => 0
    ));
}

function fn_paymentwall_generate_widget_url($orderInfo, $paymentInfo, $extraAttr = [])
{
    $widget = fn_paymentwall_prepare_widget($orderInfo, $paymentInfo, $extraAttr);
    return $widget->getUrl();
}

function fn_paymentwall_prepare_widget($orderInfo, $paymentInfo, $extraAttr = array())
{

    fn_paymentwall_init_configs($paymentInfo['key'], $paymentInfo['secret']);

    $widget = new Paymentwall_Widget(
        empty($orderInfo['user_id']) ? $orderInfo['ip_address'] : $orderInfo['user_id'],
        $paymentInfo['widget_type'],
        array(
            new Paymentwall_Product(
                $orderInfo['order_id'],
                fn_paymentwall_get_real_price($orderInfo),
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
            fn_paymentwall_prepare_user_profile_data($orderInfo),
            $extraAttr
        )
    );

    return $widget;
}

function fn_paymentwall_handle_pingback()
{
    if(isset($_GET['goodsid']) && $orderInfo = fn_get_order_info($_GET['goodsid'])){

        $configs = $orderInfo['payment_method']['processor_params'];

        fn_paymentwall_init_configs($configs['key'], $configs['secret']);

        $pingback = new Paymentwall_Pingback($_GET, fn_get_client_ip_server());

        if ($pingback->validate()) {

            if ($pingback->isDeliverable()) {

                // Call Delivery Confirmation API
                // Delivery Confirmation
                $delivery = new Paymentwall_GenerericApiObject('delivery');
                $response = $delivery->post(fn_paymentwall_prepare_delivery_confirmation(
                    $orderInfo,
                    $pingback->getReferenceId(),
                    DELIVERY_STATUS_ORDER_PLACED,
                    $configs['test_mode']
                ));

                // Update order status: Processed
                fn_finish_payment($pingback->getProductId(), array(
                    'order_status' => PW_ORDER_STATUS_PROCESSED,
                    'reason_text' => sprintf('Paymentwall payment approved (Order ID: #%s, Transaction ID: #%s)', $pingback->getProductId(), $pingback->getReferenceId()),
                    'transaction_id' => $pingback->getReferenceId()
                ));

            } elseif ($pingback->isCancelable()) {
                fn_finish_payment($pingback->getProductId(), array(
                    'order_status' => PW_ORDER_STATUS_CANCELED,
                    'reason_text' => 'Transaction was canceled'
                ));
            }

            return true;
        } else {
            echo $pingback->getErrorSummary();
            return false;
        }

    }else{
        echo 'Order invalid!';
        return false;
    }


}

function fn_paymentwall_get_configs($paymentId)
{
    $processorParams = db_get_field("SELECT processor_params FROM ?:payments WHERE payment_id = ?s", $paymentId);
    return unserialize($processorParams);
}

function fn_paymentwall_get_real_price($orderInfo)
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
function fn_paymentwall_prepare_delivery_confirmation($orderInfo, $ref, $status, $isTest = false, $tracking_data = array() )
{
    $productType = PW_DELIVERY_API_PRODUCT_PHYSICAL;
    if (fn_paymentwall_has_virtual($orderInfo['products'])) {
        $productType = PW_DELIVERY_API_PRODUCT_DIGITAL;
    }
    $data =  array(
        'payment_id' => $ref,
        'type' => $productType,
        'merchant_reference_id' =>$orderInfo['order_id'],
        'status' => $status,
        'estimated_delivery_datetime' => date('Y/m/d H:i:s'),
        'estimated_update_datetime' => date('Y/m/d H:i:s'),
        'is_test' => $isTest,
        'reason' => 'none',
        'refundable' => 'yes',
        'details' => !empty($tracking_data['comments']) ? $tracking_data['comments'] : 'Order status has been updated on ' . date('Y/m/d H:i:s'),
        'product_description' => '',
        'shipping_address[country]' => $orderInfo['s_country'],
        'shipping_address[city]' => $orderInfo['s_city'],
        'shipping_address[zip]' => $orderInfo['s_zipcode'],
        'shipping_address[state]' => $orderInfo['s_state'],
        'shipping_address[street]' => $orderInfo['s_address'] . ($orderInfo['s_address_2'] ? "\n" . $orderInfo['s_address'] : ""),
        'shipping_address[phone]' => !empty($orderInfo['s_phone']) ? $orderInfo['s_phone'] : !empty($orderInfo['b_phone']) ? $orderInfo['b_phone'] : 'NA' ,
        'shipping_address[email]' => $orderInfo['email'],
        'shipping_address[firstname]' => !empty($orderInfo['s_firstname']) ? $orderInfo['s_firstname'] : !empty($orderInfo['b_firstname']) ? $orderInfo['b_firstname'] : 'NA' ,
        'shipping_address[lastname]' => !empty($orderInfo['s_lastname']) ? $orderInfo['s_lastname'] : !empty($orderInfo['b_lastname']) ? $orderInfo['b_lastname'] : 'NA' ,
        'attachments' => null
    );

    if (!empty($tracking_data['tracking_number'])) {
        $data['carrier_tracking_id'] = $tracking_data['tracking_number'];
        $data['carrier_type'] = !empty($tracking_data['carrier']) ? $tracking_data['carrier'] : '';
    }
    return $data;
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

function fn_get_client_ip_server() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(!empty($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(!empty($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(!empty($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(!empty($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;

}

function fn_paymentwall_get_payment_field($field, $unserialize = false) {
    $data = db_get_field(
        'SELECT ?:payments.' . $field
        . ' FROM ?:payments'
        . ' LEFT JOIN ?:payment_processors'
        . ' ON ?:payment_processors.processor_id = ?:payments.processor_id'
        . ' WHERE ?:payment_processors.processor_script = ?s'
        . ' AND ?:payments.status = ?s', PAYMENTWALL_PROCESSOR_SCRIPT, PAYMENT_STATUS_ACTIVE
    );

    return !$unserialize ? $data : unserialize($data);
}

/**
 * @return array|mixed|null
 */
function fn_paymentwall_get_local_payments() {
    $processorParams = fn_paymentwall_get_payment_field('processor_params', true);
    fn_paymentwall_init_configs($processorParams['key'], $processorParams['secret']);
    $userIp = fn_get_client_ip_server();
    $userCountry = fn_paymentwall_get_country_by_ip($userIp, $processorParams);

    if (!empty($userCountry)) {
        $params = array(
            'key' =>  $processorParams['key'],
            'country_code' => $userCountry ,
            'sign_version' => 2,
            'currencyCode' => CART_PRIMARY_CURRENCY,
            'amount' => !empty($_SESSION['cart']['total']) ? $_SESSION['cart']['total'] : 0
        );

        $params['sign'] = (new Paymentwall_Signature_Widget())->calculate(
            $params,
            $params['sign_version']
        );

        $url = Paymentwall_Config::API_BASE_URL . '/payment-systems/?' . http_build_query($params);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        if (curl_error($curl)) {
            return null;
        }
        if ($response != '') {
            $result = fn_paymentwall_prepare_local_payment(json_decode($response, true));
            return $result;
        }
    }
    return [];
}


function fn_paymentwall_get_country_by_ip($ip , $processorParams) {
    if (!empty($processorParams['key'])) {
        $params = array(
            'key' => $processorParams['key'],
            'uid' => USER_ID_GEOLOCATION,
            'user_ip' => $ip
        );
        $url = Paymentwall_Config::API_BASE_URL . '/rest/country?' . http_build_query($params);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        if (curl_error($curl)) {
            return null;
        }
        $response = json_decode($response, true);
        if (!empty($response['code'])) {
            return $response['code'];
        }
    }
    return null;
}

function fn_paymentwall_create_shipment_post($shipment_data, $order_info, $group_key, $all_products, $shipment_id) {
    if (
        $order_info['payment_method']['processor'] == 'Paymentwall' &&
        !empty($shipment_data['tracking_number'])
    ) {
        $paymentInfo = $order_info['payment_info'];
        $processorParam = $order_info['payment_method']['processor_params'];
        fn_paymentwall_init_configs($processorParam['key'], $processorParam['secret']);
        $delivery = new Paymentwall_GenerericApiObject('delivery');
        $response = $delivery->post(fn_paymentwall_prepare_delivery_confirmation(
            $order_info,
            $paymentInfo['transaction_id'],
            DELIVERY_STATUS_ORDER_SHIPPED,
            $processorParam['test_mode'],
            $shipment_data
        ));
        if (!empty($response['error_code'])) {
            fn_set_notification('E', fn_get_lang_var('warning'), 'Paymentwall delivery API error', true);
        }
    }
}

function fn_paymentwall_prepare_local_payment($datas) {
    $result = [];
    if (!empty($datas) && is_array($datas)) {
        foreach ($datas as $item) {
            if (
                !empty($item['id']) ||
                !empty($item['name']) ||
                !empty($item['img_url'])
            ) {
                $result[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'img_url' => $item['img_url']
                ];
            }
        }
    }
    return $result;
}

function fn_paymentwall_has_virtual($products) {
    foreach ($products as $product) {
        if ($product['extra']['is_edp'] == PRODUCT_IS_DOWNLOADABLE) {
            return true;
        }
    }
    return false;
}