<?php
session_start();
date_default_timezone_set("Asia/Kolkata");
require_once "../config/db.php";

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*
Fetch booking history
- show only past appointments
- latest first
*/

$query = "SELECT 
a.id AS appointment_id,
a.total_price,
a.appointment_date,
a.start_time,
a.end_time,
a.status,

s.name AS stylist_name,

GROUP_CONCAT(sv.service_name SEPARATOR ', ') AS services,

p.id AS payment_id,
p.amount

FROM appointments a

LEFT JOIN payments p 
ON a.id = p.appointment_id

LEFT JOIN stylists s
ON a.stylist_id = s.id

LEFT JOIN appointments_services aps
ON a.id = aps.appointment_id

LEFT JOIN services sv
ON aps.service_id = sv.id

WHERE a.user_id = ?
AND (
a.appointment_date < CURDATE()
OR (a.appointment_date = CURDATE() AND a.end_time < CURTIME())
)

GROUP BY 
a.id,
a.total_price,
a.appointment_date,
a.start_time,
a.end_time,
a.status,
s.name,
p.id,
p.amount

ORDER BY a.appointment_date DESC, a.start_time DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">

        <h2 class="text-center mb-4">Booking History</h2>

        <?php if (!empty($data)): ?>

            <table class="table table-bordered">

                <thead class="table-dark">
                    <tr>
                        <th>Appointment ID</th>
                        <th>Date</th>
                        <th>Payment ID</th>
                        <th>Services</th>
                        <th>Stylist</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Total Price</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($data as $row): ?>

                        <tr>

                            <td><?php echo htmlspecialchars($row['appointment_id']); ?></td>

                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>

                            <td><?php echo htmlspecialchars($row['payment_id']); ?></td>

                            <td><?php echo htmlspecialchars($row['services']); ?></td>

                            <td><?php echo htmlspecialchars($row['stylist_name']); ?></td>

                            <td><?php echo htmlspecialchars($row['start_time']); ?></td>

                            <td><?php echo htmlspecialchars($row['end_time']); ?></td>

                            <td>₹<?php echo number_format($row['total_price'], 2); ?></td>

                            <td>₹<?php echo number_format($row['amount'], 2); ?></td>

                            <td>
                                <?php
                                $status = strtolower($row['status']);

                                $badgeClass = ($status == "completed" || $status == "booked")
                                    ? "success"
                                    : "danger";
                                ?>

                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars(ucwords($row['status'])); ?>
                                </span>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="alert alert-info text-center">
                No booking history found.
            </div>

        <?php endif; ?>

    </div>

</body>

</html>

<?php $conn->close(); ?>