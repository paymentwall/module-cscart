<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if($mode == 'ajax'){
	$status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
	if($status == "P"){
		fn_clear_cart($_SESSION['cart']);
	}
	die($status);
}

if ($mode == 'frame') {
	$view->assign('orderId', $_REQUEST['order_id']);
}

if ($mode == 'pingback') {
		define('CREDIT_TYPE_CHARGEBACK', 2);
		$ipsWhitelist = array(
			'174.36.92.186',
			'174.36.96.66',
			'174.36.92.187',
			'174.36.92.192',
			'174.37.14.28'
		);
		
		$userId = isset($_GET['uid']) ? $_GET['uid'] : null;
		$goodsId = isset($_GET['goodsid']) ? $_GET['goodsid'] : null;
		$length = isset($_GET['slength']) ? $_GET['slength'] : null;
		$period = isset($_GET['speriod']) ? $_GET['speriod'] : null;
		$type = isset($_GET['type']) ? $_GET['type'] : null;
		$refId = isset($_GET['ref']) ? $_GET['ref'] : null;
		$signature = isset($_GET['sig']) ? $_GET['sig'] : null;
		$result = false;
		if (!empty($userId) && !empty($goodsId) && isset($type) && !empty($refId) && !empty($signature)) {
			$paymentId = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $goodsId);
			$secretKey = fn_get_payment_method_data($paymentId);
			$signatureParams = array(
				'uid' => $userId,
				'goodsid' => $goodsId,
				'slength' => $length,
				'speriod' => $period,
				'type' => $type,
				'ref' => $refId
			);
			
			$signatureCalculated = generateSignature($signatureParams, $secretKey['processor_params']['secret']);
			
			// check if IP is in whitelist and if signature matches
			if (in_array($_SERVER['REMOTE_ADDR'], $ipsWhitelist) && ($signature == $signatureCalculated)) {
				$result = true;
				
				if ($type == CREDIT_TYPE_CHARGEBACK) {
					$data = array (
					    'status' => 'I'
					);
					db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', $data, $goodsId);
						
					// Take membership from user
					// This is optional, but we recommend this type of crediting to be implemented as well
					//$this->model_checkout_order->update($goodsId, $this->config->get('cancel_status'));
				}
				else {
					$data = array (
					    'status' => 'P'
					);
					db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', $data, $goodsId);
					// Give membership to user
					//$this->model_checkout_order->confirm($goodsId, $this->config->get('complete_status'));
				}
			}
	}
		if ($result) {
		
		die('OK');
		}
}

function generateSignature($params, $secret) {
	$str = '';

	foreach ($params as $k=>$v) {
		$str .= "$k=$v";
	}
	$str .= $secret;

	return md5($str);
}

?>
