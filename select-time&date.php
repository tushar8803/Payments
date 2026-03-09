<?php
session_start();
include("../config/db.php");
// if(isset($_SESSION['payment_message'])){
//     $message = $_SESSION['payment_message'];
//     $result = $_SESSION['payment_result'];

//     unset($_SESSION['payment_message']);
//     unset($_SESSION['payment_result']);

//     echo "<script>
//         alert('$message');
//     ";

//     if($result == "success"){
//         echo "window.location.href='appointment-success.php';";
//     }
//     else{
//         echo "window.location.href='appointment-failure.php';";
//     }

//     echo "</script>";
// }

// $payment_message = "";
// $payment_result = "";

// if(isset($_SESSION['payment_message'])){
//     $payment_message = $_SESSION['payment_message'];
//     $payment_result = $_SESSION['payment_result'];

//     unset($_SESSION['payment_message']);
//     unset($_SESSION['payment_result']);
// }



if (
    !isset($_SESSION['selected_services']) ||
    !isset($_SESSION['selected_stylist'])
) {
    header("Location: select-services.php");
    exit();
}
unset($_SESSION['start_time']);
unset($_SESSION['appointment_date']);
unset($_SESSION['end_time']);
unset($_SESSION['payment_amount']);

$service_ids = $_SESSION['selected_services'];   //storing selected staff id & services id's from previous pages
$staff_id = $_SESSION['selected_stylist'];

$id_list = implode(",", $service_ids);  //converted array($service_ids ) into comma seperated string

$result = mysqli_query(
    $conn,
    "SELECT * FROM services WHERE id IN ($id_list)"  //fetching selected services rows 
);

$total_price = 0;
$total_duration = 0;
$selected_services = [];

while ($service = mysqli_fetch_assoc($result)) {
    $selected_services[] = $service;     //storing a row one by one in form of array into $selected_services[] 
    $total_price += $service['price'];
    $total_duration += $service['duration'];
}
$_SESSION['total_duration'] = $total_duration;    //to calculate end time in store-datetime.php
$_SESSION['total_price'] = $total_price;




// Fetch barbers
$barbers = mysqli_query($conn, "SELECT * FROM stylists");

?>

<!DOCTYPE html>
<html>

<head>
    <title>Square Booking Flow</title>

    <link rel="stylesheet" href="../assets/css/select-time&date.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap" rel="stylesheet">
</head>

<body>

    <header class="main-header">
        <div class="header-content">
            <div class="logo">
                <img src="../assets/logo.png" alt="Saloon Logo">
                <h1>The Gent's Place</h1>
            </div>

            <div class="page-title">
                <h2>SELECT DATE & TIME</h2>
            </div>

            <div class="header-right">
                <span>Contact: +91 98765 43210</span>
            </div>
        </div>
    </header>

    <form method="POST">
        <div class="booking-container">

            <!-- LEFT SIDE -->
            <div class="left-panel">

                <!-- Calendar -->
                <div class="calendar-box">
                    <div id="appointment_date"></div>
                </div>

                <input type="hidden" name="selected_date" id="selected_date">
                <input type="hidden" name="selected_time" id="selected_time">
                <input type="hidden" name="staff_id" value="<?= $staff_id ?>">
                <input type="hidden" name="total_duration" value="<?= $total_duration ?>">



                <!-- Selected Date -->
                <div id="selected_date_display" class="selected-date">
                    Please select a date
                </div>

                <!-- Time Slots -->
                <div id="time_slots"></div>



            </div>


            <!-- RIGHT SIDE -->
            <div class="right-panel">
                <!--new-->

                <hr>

                <h4>Select Payment</h4>

                <div class="payment-options">

                    <?php
                    $half = $total_price * 0.5;
                    $full = $total_price;
                    ?>

                    <button type="button" class="pay-btn" data-amount="<?php echo $half; ?>">
                        Pay 50% : ₹<?php echo $half; ?>
                    </button>

                    <button type="button" class="pay-btn" data-amount="<?php echo $full; ?>">
                        Pay 100% : ₹<?php echo $full; ?>
                    </button>

                </div>

                <input type="hidden" id="selected_payment" name="selected_payment">

                <hr>






                <div class="summary-box">
                    <h3>Appointment Summary</h3>

                    <!-- Services -->

                    <h4>Selected Services</h4>
                    <div class="summary-details">


                        <?php foreach ($selected_services as $s): ?> <h4><?php echo htmlspecialchars($s['service_name']);
                                                                            ?></h4> <?php endforeach; ?>
                    </div>

                    <hr>

                    <?php
                    // Add this before the HTML block to get the name
                    $staff_query = mysqli_query($conn, "SELECT name FROM stylists WHERE id = '$staff_id'");
                    $staff_data = mysqli_fetch_assoc($staff_query);
                    $staff_name = $staff_data['name'] ?? 'Not selected';
                    ?>

                    <p><strong>Stylist:</strong> <?php echo htmlspecialchars($staff_name); ?></p>

                    <!-- Date & Time (JS will update this automatically) -->
                    <div class="summary-datetime">
                        <p><strong>Date:</strong> <span id="summary_date">Not selected</span></p>
                        <p><strong>Time:</strong> <span id="summary_time">Not selected</span></p>
                    </div>
                    <hr>

                    <div class="total">
                        <?php
                        $hours = floor($total_duration / 60);
                        $minutes = $total_duration % 60;

                        $formatted_duration = "";

                        if ($hours > 0) {
                            $formatted_duration .= $hours . " hour";
                            if ($hours > 1) $formatted_duration .= "s";
                        }

                        if ($minutes > 0) {
                            if ($hours > 0) $formatted_duration .= " ";
                            $formatted_duration .= $minutes . " minute";
                            if ($minutes > 1) $formatted_duration .= "s";
                        }
                        ?>

                        <strong>Total Duration:<span> <?php echo $formatted_duration; ?></span></strong>
                    </div>
                    <hr>

                    <div class="total">
                        <strong>Total Price: <span>₹<?php echo $total_price; ?></span></strong>

                    </div>



                    <p><strong>Payment:</strong> ₹<span id="summary_payment">Not selected</span></p>


                    <!-- <a href="<?php echo isset($_SESSION['start_time']) ? '../payment.php' : 'select-time&date.php'; ?>">
                        <button type="button" class="confirm-btn"
                            <?php if (!isset($_SESSION['selected_time'])) echo "disabled"; ?>>
                            Next
                        </button> -->

                   
                </div>



            </div>

        </div>
    </form>
     <button id="payNow" class="confirm-btn"
                        <?php if (!(isset($_SESSION['payment_amount']) && isset($_SESSION['start_time']))) echo "disabled"; ?>>
                        Proceed to Payment
                    </button>




    <script src="../assets/js/ajax-request.js"></script>
    <script src="../assets/js/select-datetime.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
   <script src="https://checkout.razorpay.com/v1/checkout.js"></script>



    <script>
        flatpickr("#appointment_date", {
            inline: true,
            minDate: "today",
            dateFormat: "Y-m-d",

            onChange: function(selectedDates, dateStr) {

                if (selectedDates.length > 0) {

                    // Store date
                    document.getElementById("selected_date").value = dateStr;

                    // Show formatted date
                    let options = {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    };

                    let formattedDate = selectedDates[0]
                        .toLocaleDateString('en-IN', options);

                    document.getElementById("selected_date_display")
                        .innerHTML = formattedDate;

                    document.getElementById("summary_date")
                        .innerHTML = formattedDate;
                    // 🔥 LOAD TIME SLOTS
                    loadTimeSlots(dateStr);

                    document.getElementById("selected_time").value = selectedTime;
                    document.getElementById("summary_time").innerText = selectedTime;

                    // Enable confirm button
                    document.querySelector(".confirm-btn").disabled = false;

                }




            }
        });




        document.getElementById("payNow").onclick = function() {

            fetch("../create_order.php")
                .then(res => res.json())
                .then(data => {
                  

                    var options = {

                        key: "rzp_test_SKgDdZ7P8ncK8t",

                        amount: data.amount,

                        currency: "INR",

                        name: "Tushar Salon",

                        description: "Salon Appointment Booking",

                        order_id: data.order_id,

                        handler: function(response) {

                            window.location.href =
                                "../verify_payment.php?payment_id=" + response.razorpay_payment_id +
                                "&order_id=" + response.razorpay_order_id +
                                "&signature=" + response.razorpay_signature;

                        }

                    };

                    var rzp = new Razorpay(options);

                    rzp.open();

                });
        }
        
    </script>
    <!-- <script src="https://checkout.razorpay.com/v1/checkout.js"></script> -->
   
   <!-- <script>

document.addEventListener("DOMContentLoaded", function(){

    let message = "<?php echo $payment_message; ?>";
    let result = "<?php echo $payment_result; ?>";

    if(message !== ""){
        alert(message);

        if(result === "success"){
            window.location.href = "appointment-success.php";
        }
        else{
             window.location.href = "appointment-failure.php";
        }
    }

});

</script> -->

</body>

</html>