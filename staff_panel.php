<?php
session_start();
date_default_timezone_set("Asia/Kolkata");
require_once "config/db.php"; // change path if needed

$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

$autoUpdate = "
    UPDATE appointments
    SET status = 'uncompleted'
    WHERE status = 'booked'
    AND (
        appointment_date < ?
        OR (appointment_date = ? AND end_time < ?)
    )
";

$stmtAuto = $conn->prepare($autoUpdate);
$stmtAuto->bind_param("sss", $currentDate, $currentDate, $currentTime);
$stmtAuto->execute();
$stmtAuto->close();

// --------------------------------------------------
// 1) Check login
// --------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stylist_id = (int)$_SESSION['user_id'];
$success = "";
$error = "";

// --------------------------------------------------
// 2) Update status to completed
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $new_status = "completed";

    // $stmt = $conn->prepare("
    //     UPDATE appointments
    //     SET status = ?
    //     WHERE id = ? AND stylist_id = ?
    // ");
    $stmt = $conn->prepare("
    UPDATE appointments
    SET status = ?
    WHERE id = ?
    AND stylist_id = ?
    AND appointment_date = ?
    AND ? >= start_time
");
    $stmt->bind_param("siiss", $new_status, $appointment_id, $stylist_id,$currentDate,$currentTime);
    $stmt->execute();
    // if ($stmt->execute()) {
    //     $success = "Appointment status updated successfully.";
    // } else {
    //     $error = "Failed to update status.";
    // }
    $stmt->close();
}

// --------------------------------------------------
// 3) Filters
// --------------------------------------------------
$filter = $_GET['filter'] ?? 'all';

$whereDate = "";
$params = [];
$types = "";

$today = date('Y-m-d');

// Custom range first priority
if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {

    $from = $_GET['from_date'];
    $to   = $_GET['to_date'];

    $whereDate = "AND a.appointment_date BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
    $types .= "ss";
} elseif ($filter === 'week') {

    $endDate = date('Y-m-d', strtotime('sunday this week'));

    $whereDate = "AND a.appointment_date BETWEEN ? AND ?";
    $params[] = $today;
    $params[] = $endDate;
    $types .= "ss";
} elseif ($filter === 'month') {

    $endDate = date('Y-m-d', strtotime('+1 month'));

    $whereDate = "AND a.appointment_date BETWEEN ? AND ?";
    $params[] = $today;
    $params[] = $endDate;
    $types .= "ss";
} else {

    // All upcoming
    $whereDate = "AND a.appointment_date >= ?";
    $params[] = $today;
    $types .= "s";
}

// --------------------------------------------------
// 4) Fetch appointments
//    Assumed tables:
//    appointments(id, appointment_date, start_time, end_time, total_price, status, stylist_id)
//    payments(id, appointment_id, amount)
//    stylists(id, name)
//    appointment_services(appointment_id, service_id)
//    services(id, service_name)
// --------------------------------------------------
$sql = "
    SELECT
        a.id AS appointment_id,
        a.appointment_date,
        a.start_time,
        a.end_time,
        a.total_price,
        a.status,
        u.name AS customer_name,
        u.phone_number AS customer_phone,
        p.id AS payment_id,
        p.amount AS amount_paid,
        GROUP_CONCAT(DISTINCT sv.service_name ORDER BY sv.service_name SEPARATOR ', ') AS services
    FROM appointments a
     LEFT JOIN users u
        ON a.user_id = u.id     
    LEFT JOIN payments p
        ON p.appointment_id = a.id
    LEFT JOIN appointments_services aps
        ON aps.appointment_id = a.id
    LEFT JOIN services sv
        ON sv.id = aps.service_id
    WHERE a.stylist_id = ?
    
     
      $whereDate
    GROUP BY
        a.id, a.appointment_date, a.start_time, a.end_time, a.total_price, a.status,
         p.id, p.amount
    ORDER BY a.appointment_date ASC, a.start_time ASC
";

$stmt = $conn->prepare($sql);

// build bind params dynamically
$bindParams = array_merge([$stylist_id], $params);
$bindTypes = "i" . $types;

// mysqli bind_param needs references
$tmp = [];
$tmp[] = &$bindTypes;
foreach ($bindParams as $key => $value) {
    $tmp[] = &$bindParams[$key];
}

call_user_func_array([$stmt, 'bind_param'], $tmp);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fff;
        }

        .container-box {
            max-width: 1400px;
            margin: 30px auto;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }

        .status-booked {
            background: #198754;
            color: #fff;
        }

        .status-completed {
            background: #0d6efd;
            color: #fff;
        }

        .status-uncompleted {
            background: #dc3545;
            color: #fff;
        }
    </style>
</head>

<body>

    <div class="container-box">
        <h2 class="text-center mb-4">Upcoming Appointments</h2>

        <form method="GET" class="d-flex gap-2 mb-3 align-items-end flex-wrap">

            <div>
                <label>Quick Filter</label>
                <select name="filter" class="form-select">
                    <option value="all" <?php if (($_GET['filter'] ?? '') == 'all') echo 'selected'; ?>>All Upcoming</option>
                    <option value="week" <?php if (($_GET['filter'] ?? '') == 'week') echo 'selected'; ?>>This Week</option>
                    <option value="month" <?php if (($_GET['filter'] ?? '') == 'month') echo 'selected'; ?>>Next 1 Month</option>
                </select>
            </div>

            <div>
                <label>From</label>
                <input type="date" name="from_date" class="form-control"
                    value="<?php echo $_GET['from_date'] ?? ''; ?>">
            </div>

            <div>
                <label>To</label>
                <input type="date" name="to_date" class="form-control"
                    value="<?php echo $_GET['to_date'] ?? ''; ?>">
            </div>

            <div>
                <button class="btn btn-dark">Apply</button>
            </div>

        </form>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Appointment ID</th>
                        <th>Date</th>
                        <th>Payment ID</th>
                        <th>Customer Name</th>
                        <th>Phone Number</th>
                        <th>Services</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Total Price</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['appointment_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['payment_id'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? '-'); ?></td>
                                <td>
                                    <a href="tel:<?php echo $row['customer_phone']; ?>">
                                        <?php echo $row['customer_phone']; ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($row['services'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                                <td>₹<?php echo htmlspecialchars(number_format((float)$row['total_price'], 2)); ?></td>
                                <td>₹<?php echo htmlspecialchars(number_format((float)($row['amount_paid'] ?? 0), 2)); ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'status-booked';
                                    if ($row['status'] === 'completed') $statusClass = 'status-completed';
                                    if ($row['status'] === 'uncompleted') $statusClass = 'status-uncompleted';
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- <?php if ($row['status'] !== 'completed'): ?>
                                <form method="POST" onsubmit="return confirm('Mark this appointment as completed?');">
                                    <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                    <button type="submit" name="update_status" class="btn btn-success btn-sm">
                                        Mark Completed
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">No action</span>
                            <?php endif; ?> -->
                                    <?php
                                    $isCompletedAllowed = (
                                        $row['appointment_date'] == date('Y-m-d') &&
                                        date('H:i:s') >= $row['start_time'] 
                                    );
                                    ?>

                                    <?php if ($row['status'] !== 'completed'): ?>
                                        <form method="POST" onsubmit="return confirm('Mark this appointment as completed?');">
                                            <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">

                                            <button
                                                type="submit"
                                                name="update_status"
                                                class="btn btn-success btn-sm"
                                                <?php if (!$isCompletedAllowed) echo 'disabled'; ?>>
                                                <?php echo $isCompletedAllowed ? 'Mark Completed' : 'Not Started Yet'; ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">No action</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">No appointments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>
<?php
$stmt->close();
$conn->close();
?>