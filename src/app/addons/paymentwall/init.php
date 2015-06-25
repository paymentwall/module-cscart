<?php

if (!defined('AREA')) { die('Access denied'); }

fn_register_hooks(
    'initPaymentwallSdk',
    'handlePingback',
    'updateOrderStatus',
    'getPaymentConfigs',
    'generateWidget'
);