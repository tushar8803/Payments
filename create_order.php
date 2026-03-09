<?php
session_start();
require('vendor/autoload.php');

use Razorpay\Api\Api;

$keyId = "rzp_test_SKgDdZ7P8ncK8t";
$keySecret = "U5E71mPMcYvGBUt7wr3c30bV";

$api = new Api($keyId, $keySecret);

$amount = $_SESSION['payment_amount'] * 100;

$orderData = [
    'receipt' => 'receipt_' . time(),
    'amount' => $amount,
    'currency' => 'INR',
    'payment_capture' => 1
];

$order = $api->order->create($orderData);

echo json_encode([
    "order_id" => $order['id'],
    "amount" => $amount
]);