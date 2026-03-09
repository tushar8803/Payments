<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require('vendor/autoload.php');

use Razorpay\Api\Api;

// 🔐 Test API Keys (replace with yours)
$keyId = "rzp_test_SKgDdZ7P8ncK8t";
$keySecret = "U5E71mPMcYvGBUt7wr3c30bV";

$api = new Api($keyId, $keySecret);

// Check if amount exists
if (!isset($_SESSION['total_price'])) {
    die("Amount not found. Please restart booking.");
}

$total_amount = $_SESSION['total_price'];
//$amount = $total_amount * 100;  convert to paise
$payment_amount=$_SESSION['payment_amount'];
$amount=$payment_amount*100;

// Create Razorpay Order
$orderData = [
    'receipt'         => 'receipt_' . time(),
    'amount'          => $amount,
    'currency'        => 'INR',
    'payment_capture' => 1
];

$order = $api->order->create($orderData);
$orderId = $order['id'];

$_SESSION['razorpay_order_id'] = $orderId;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>

<h2>Total Amount: ₹<?php echo $payment_amount; ?></h2>

<button id="payBtn">Pay Now</button>

<script>
var options = {
    "key": "<?php echo $keyId; ?>",
    "amount": "<?php echo $amount; ?>",
    "currency": "INR",
    "name": "Tushar Salon",
    "description": "Salon Appointment Booking",
    "order_id": "<?php echo $orderId; ?>",
    "handler": function (response){
        window.location.href = "verify_payment.php?payment_id="
            + response.razorpay_payment_id
            + "&order_id=" + response.razorpay_order_id
            + "&signature=" + response.razorpay_signature;
    }
};

var rzp1 = new Razorpay(options);

document.getElementById('payBtn').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script>

</body>
</html>