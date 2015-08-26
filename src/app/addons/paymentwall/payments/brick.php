<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$brick_configs = $processor_data['processor_params'];

if ($brick_configs['test_mode']) {
    fn_paymentwall_init_configs($brick_configs['test_public_key'], $brick_configs['test_private_key']);
} else {
    fn_paymentwall_init_configs($brick_configs['public_key'], $brick_configs['private_key']);
}

$pp_response = array();
$charge = new Paymentwall_Charge();

try {
    $charge->create(array_merge(
        fn_paymentwall_prepare_user_profile_data($order_info), // for User Profile API
        prepare_card_info($order_info)
    ));

    $response = $charge->getPublicData();
    if ($charge->isSuccessful()) {

        if ($charge->isCaptured()) {
            $pp_response['order_status'] = PW_ORDER_STATUS_PROCESSED;
            $pp_response["reason_text"] = "Payment approved! (Order ID: #" . $order_id . ", Transaction ID: #" . $charge->getId() . ")";
        } elseif ($charge->isUnderReview()) {
            $pp_response['order_status'] = PW_ORDER_STATUS_OPEN;
            $pp_response["reason_text"] = "";
        }

    } else {
        $errors = json_decode($response, true);
        $pp_response['order_status'] = PW_ORDER_STATUS_CANCELED;
        $pp_response["reason_text"] = $errors['error']['message'];
    }

} catch (Exception $e) {
    $pp_response['order_status'] = PW_ORDER_STATUS_CANCELED;
    $pp_response["reason_text"] = $e->getMessage();
}

function prepare_card_info($order_info)
{
    return array(
        'token' => $_POST['brick']['token'],
        'amount' => $order_info['total'],
        'currency' => $order_info['secondary_currency'],
        'email' => $order_info['email'],
        'fingerprint' => $_POST['brick']['fingerprint'],
        'description' => "Order #" . $order_info['order_id'],
    );
}
