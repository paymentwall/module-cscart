<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

fn_register_hooks(
    'fn_paymentwall_init_configs',
    'fn_paymentwall_generate_widget',
    'fn_paymentwall_handle_pingback',
    'fn_paymentwall_get_configs',
    'fn_paymentwall_get_real_price',
    'fn_paymentwall_prepare_delivery_confirmation',
    'fn_paymentwall_prepare_user_profile_data'
);