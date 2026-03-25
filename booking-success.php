<?php
session_start();
include("config/db.php");

$appointment_id=$_SESSION['inserted_appointment_id'];  //appointment to fetch other details from appo&payments table


$sql = "SELECT 
appointments.id as appointment_id,
appointments.appointment_date,
appointments.start_time,
appointments.end_time,
appointments.total_price,
appointments.stylist_id,
payments.id as payment_id,
payments.amount
FROM appointments
JOIN payments 
ON appointments.id = payments.appointment_id
WHERE appointments.id = '$appointment_id'";

$result = mysqli_query($conn,$sql);

$data = mysqli_fetch_assoc($result);

$appointment_date = $data['appointment_date'];
$start_time = $data['start_time'];
$end_time = $data['end_time'];

$total_price = $data['total_price'];
$amount = $data['amount'];

$payment_id = $data['payment_id'];
$stylist_id = $data['stylist_id'];
$appointment_id=$data['appointment_id'];

//to fetch booked services
$services = [];
$sql2="SELECT services.service_name
FROM appointments_services
JOIN services
ON appointments_services.service_id = services.id
WHERE appointments_services.appointment_id = '$appointment_id'";

$result2 = mysqli_query($conn, $sql2);

while($row = mysqli_fetch_assoc($result2)){
    $services[] = $row['service_name'];
       
}

$sql3= "SELECT name FROM stylists WHERE id=$stylist_id";
$result3=mysqli_query($conn,$sql3);
$data2 = mysqli_fetch_assoc($result3);
$selected_stylist=$data2['name'];






?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Booking Confirmed</title>
  <!-- Optional: Bootstrap CDN for quick styling (works if you have internet). Remove if you prefer your own CSS. -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{ --accent:#2f9e44; --muted:#6c757d; }
    body{ background: #f5f7fa; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
    .confirm-card{ max-width:820px; margin: 48px auto 15px auto; background:#fff; border-radius:10px; box-shadow:0 6px 22px rgba(17,24,39,0.08); padding:32px; }
    .confirm-head{ display:flex; align-items:center; gap:18px; }
    .badge-success{ width:72px; height:72px; border-radius:12px; background:linear-gradient(180deg,var(--accent),#1f7b34); display:flex; align-items:center; justify-content:center; color:#fff; font-size:30px; }
    h1{ font-size:22px; margin:0; color: #1f2937; }
    .muted{ color:var(--muted); }
    .detail-row{ margin-top:18px; }
    .detail-item{ padding:14px; border-radius:8px; background:#fbfdff; border:1px solid #eef2f6; }
    .table-services td, .table-services th{ vertical-align:middle; }
    .btn-home{ background:var(--accent); color:#fff; }
    .small-note{ font-size:13px; color:#6b7280; }
    .clipboard-btn{ border:none; background:transparent; color:var(--accent); cursor:pointer; }
    @media (max-width:540px){ .confirm-card{ margin:18px; padding:18px; } .badge-success{ width:56px; height:56px;}
    
     
     }
  </style>
</head>
<body>

  <div id="receipt">


    
        <div class="confirm-card">
    <div class="confirm-head">
      <div class="badge-success" aria-hidden>
        ✓
      </div>
      <div>
        <h1>Thank you — your booking is confirmed!</h1>
        <!-- <p class="muted">We've sent a confirmation to your email/SMS. Please keep your appointment details handy.</p> -->
      </div>
    </div>

    <div class="row detail-row">
      <div class="col-md-6">
        <div class="detail-item">
          <strong>Appointment ID</strong>
          <div class="d-flex align-items-center justify-content-between mt-2">
            <div><code id="appointmentId"><?= htmlspecialchars($appointment_id ?? '—') ?></code></div>
           
          </div>
          <div class="small-note mt-1">Use this ID for support or modifications.</div>
        </div>
      </div>

      <div class="col-md-6 mt-3 mt-md-0">
        <div class="detail-item">
          <strong>Payment ID</strong>
          <div class="d-flex align-items-center justify-content-between mt-2">
            <div><code id="paymentId"><?= htmlspecialchars($payment_id ?? '—') ?></code></div>
           
          </div>
          <div class="small-note mt-1">Payment reference from the gateway.</div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-6">
        <div class="detail-item">
          <strong>Appointment Date</strong>
          <div class="mt-2"><?= htmlspecialchars(isset($appointment_date) ? date('d-m-Y', strtotime($appointment_date)) : '—') ?></div>
          <div class="small-note mt-1">Please arrive 5–10 minutes early.</div>
        </div>
      </div>

      <div class="col-md-6 mt-3 mt-md-0">
        <div class="detail-item">
          <strong>Time Slot</strong>
          <div class="mt-2"><?= htmlspecialchars(($start_time ?? '—') . ' — ' . ($end_time ?? '—')) ?></div>
          <div class="small-note mt-1">We reserve the slot for you; delays may affect service time.</div>
        </div>
      </div>
    </div>

   <div class="row mt-3">

  <div class="col-md-6">
    <div class="detail-item">
      <strong>Total Price</strong>
      <div class="mt-2">
        ₹<?= htmlspecialchars($total_price ?? '—') ?>
      </div>
      <div class="small-note mt-1">Total cost of selected services.</div>
    </div>
  </div>

  <div class="col-md-6 mt-3 mt-md-0">
    <div class="detail-item">
      <strong>Amount Paid</strong>
      <div class="mt-2">
        ₹<?= htmlspecialchars($amount?? '—') ?>
      </div>
      <div class="small-note mt-1">Payment successfully received.</div>
    </div>
  </div>

</div>

    <div class="row mt-3">

  <div class="col-md-6">
    <div class="detail-item">
      <strong>Stylist / Professional</strong>
      <div class="mt-2">
        <?= htmlspecialchars($selected_stylist ?? 'Not selected') ?>
      </div>
    </div>
  </div>

  <div class="col-md-6 mt-3 mt-md-0">
    <div class="detail-item">
      <strong>Services Opted</strong>
      <div class="mt-2">
       
        <?php if (!empty($services) && is_array($services)): ?>
          <ul class="mb-0">
            <?php foreach($services as $svc): ?>
              <li><?= htmlspecialchars($svc) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="muted">No services listed.</div>
         
        <?php endif; ?>
       
      </div>
    </div>
  </div>

  </div>

</div>




 <div class="text-center mt-4">

    <a href="index.php" class="btn btn-home mr-2">Go to Homepage</a>

    <button class="btn btn-outline-secondary" onclick="printReceipt()">Print</button>

</div>

  <script>

function printReceipt() {

    var printContent = document.getElementById("receipt").innerHTML;
    var originalContent = document.body.innerHTML;

    document.body.innerHTML = printContent;

    window.print();

    document.body.innerHTML = originalContent;

}

</script>

</body>
</html>
