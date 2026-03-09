<?php /*
session_start();
echo "STEP 1 - File Reached";
exit(); */
?>

<?php  /*
session_start();
echo "<pre>";
print_r($_GET);
exit();  */
?>


<?php

session_start();
date_default_timezone_set('Asia/Kolkata');
require('vendor/autoload.php');

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$api = new Api("rzp_test_SKgDdZ7P8ncK8t", "U5E71mPMcYvGBUt7wr3c30bV");

$attributes = [
    'razorpay_order_id' => $_GET['order_id'],
    'razorpay_payment_id' => $_GET['payment_id'],
    'razorpay_signature' => $_GET['signature']
];


try {
    $api->utility->verifyPaymentSignature($attributes);
    // echo "STEP 3 - Signature Verified";
    echo "<br>";

    // ✅ Payment is verified
    $payment_status = "paid";

    // Insert appointment into database

    include("config/db.php");

    $user_id = $_SESSION['user_id'];
    $stylist_id = $_SESSION['selected_stylist'];
    $total_price = $_SESSION['total_price'];
    $total_duration = $_SESSION['total_duration'];
    $appointment_date = $_SESSION['appointment_date'];
    $start_time = $_SESSION['start_time'];
    $end_time = $_SESSION['end_time'];

    $status = "booked";



    mysqli_begin_transaction($conn);

    try {


        $sql1 = "INSERT INTO appointments 
(user_id, stylist_id, total_price, total_duration, appointment_date, start_time, end_time, status)
VALUES 
('$user_id', '$stylist_id', '$total_price', '$total_duration', '$appointment_date', '$start_time', '$end_time', '$status')";

        if (!mysqli_query($conn, $sql1)) {
            throw new Exception(mysqli_error($conn));
        }

        $appointment_id = mysqli_insert_id($conn);
        echo "<br>";
        // echo $appointment_id;
        echo '<a href="index.php">Home Page</a>';

        $amount = $_SESSION['payment_amount'];
        $payment_method = "razorpay";
        $transaction_id = $_GET['payment_id'];
        $payment_status = "paid";
        $payment_date = date("Y-m-d H:i:s");

        $sql2 = "INSERT INTO payments
(appointment_id, amount, payment_method, transaction_id, payment_status, payment_date)
VALUES
('$appointment_id', '$amount', '$payment_method', '$transaction_id', '$payment_status', '$payment_date')";


        if (!mysqli_query($conn, $sql2)) {
            throw new Exception(mysqli_error($conn));
        }



        mysqli_commit($conn);
        
                echo "<script>
        window.location.href='booking-success.php';
        </script>";

        // $_SESSION['payment_message'] = "Payment Successful! Your appointment is booked.";
        // $_SESSION['payment_result'] = "success";
        // header("Location: user/select-time&date.php");
        exit();
    } catch (Exception $e) {

        mysqli_rollback($conn);

        
            echo "<script>
        window.location.href='booking-failure.php';
        </script>";

        // $_SESSION['payment_message'] = "Something wen wrong while booking your appointment";
        // $_SESSION['payment_result'] = "failed";
        // header("Location: user/select-time&date.php");
        
        exit();
    }
} catch (Exception $e) {
    echo "STEP 3 FAILED: " . $e->getMessage();
    $payment_status = "failed";

    
    echo "<script>
    window.location.href='booking-failure.php';
    </script>";
    // $_SESSION['payment_message'] = "Payment verification failed.";
    // $_SESSION['payment_result'] = "failed";
    // header("Location: user/select-time&date.php");
    exit();
}

?>
<?php /*
session_start();
echo "<pre>";
print_r($_SESSION);
exit(); */
?>
