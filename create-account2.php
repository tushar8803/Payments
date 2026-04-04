<?php

    session_start();
        //import database
    include("config/db.php");

    if (!isset($_POST['send_otp']) && !isset($_POST['signup'])) {
        $_SESSION['signup_step'] = 'form';
        unset($_SESSION['signup_data']);
        unset($_SESSION['signup_otp']);
        unset($_SESSION['signup_expiry']);
    }
    $_SESSION["user"] = "";
    $_SESSION["usertype"] = "";

    $_SESSION['signup_step'] = $_SESSION['signup_step'] ?? 'form';
    
    // $_SESSION['email_verified'] = $_SESSION['email_verified'] ?? false;
    
    // Set the new timezone
    // date_default_timezone_set('Asia/Kolkata');
    // $date = date('Y-m-d');

    // $_SESSION["date"] = $date;
    
    if (isset($_POST['edit_details'])) {
        $_SESSION['signup_step'] = 'form';
        unset($_SESSION['signup_data']);
    }
    
    $errors = [];

    require 'vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // require '../config/PHPMailer/Exception.php';
    // require '../config/PHPMailer/PHPMailer.php';
    // require '../config/PHPMailer/SMTP.php';

    if (isset($_POST['send_otp'])) {

            $fullname = trim($_POST['fullname']);
            $new_email = trim($_POST['newemail']);
            $tele = trim($_POST['tele']);
            $newpassword = $_POST['newpassword'];
            $cpassword = $_POST['cpassword'];

            // ==========================
            // 1. Full Name Validation
            // ==========================
            if (empty($fullname)) {
                $errors['fullname'] = "Full Name is required";
            } elseif (!preg_match("/^[A-Za-z ]+$/", $fullname)) {
                $errors['fullname'] = "Only letters and spaces allowed";
            }

            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)){
                $errors['email'] = "Invalid email format!";
            }
            
            // check duplicate email
            $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$new_email'");
            if (mysqli_num_rows($check) > 0) {
                $errors['email'] = "Email already exists!";
            }

            // ==========================
            // 3. Phone Validation
            // ==========================
            if (!preg_match("/^[0-9]{10}$/", $tele)) {
                $errors['tele'] = "Phone must be 10 digits";
            }
            // ==========================
            // 4. Password Validation
            // ==========================
            if (strlen($newpassword) < 6) {
                $errors['password'] = "Password must be at least 6 characters";
            } elseif (!preg_match("/[0-9]/", $newpassword)) {
                $errors['password'] = "Password must contain at least one number";
            } elseif (!preg_match("/[\W]/", $newpassword)) {
                $errors['password'] = "Password must contain one special character";
            }
            // ==========================
            // 5. Confirm Password
            // ==========================
            if ($newpassword !== $cpassword) {
                $errors['cpassword'] = "Passwords do not match";
            }
            if (empty($errors)){
                $_SESSION['signup_data'] = [
                    'name' => $_POST['fullname'],
                    'phone' => $_POST['tele'],
                    'email'=>   $_POST['newemail'],
                    'password' => $_POST['newpassword']
                ];
                
                // generate OTP
                $otp = rand(100000, 999999);
                $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

                // store in sessions for verification
                $_SESSION['signup_email'] = $new_email;
                $_SESSION['signup_otp'] = password_hash($otp, PASSWORD_DEFAULT);
                $_SESSION['signup_expiry'] = $expiry;

                // ================= PHPMailer =================
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'thegentsplace9@gmail.com';
                    $mail->Password   = 'qvol jkei sxbi hzjo'; // app password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;
                    $mail->setFrom('thegentsplace9@gmail.com', 'The Gents Place');
                    $mail->addAddress($new_email);
                    $mail->Subject = 'Your OTP for signup on Gents Place';
                    $mail->Body    = "Hello,\n\nYour OTP for password signup on The Gents Place is: $otp\nThis code will expire in 5 minutes.\n\nIf you did not request this, please ignore this email.\n\nRegards,\nThe Gent's Place Team";
                    $mail->send();
                    $success_msg = "OTP sent to your email!";
                    $_SESSION['signup_step'] = 'otp';
                    // start timer after page loads
                } catch (Exception $e) {
                    $errors[] = "Failed to send OTP. Error: " . $mail->ErrorInfo;
                }
            }
        }
        
    if (isset($_POST['signup'])) {

        $entered_otp = $_POST['otp'];

        if (empty($entered_otp)) {
            $errors['otp'] = "Please enter OTP!";
        } elseif (!isset($_SESSION['signup_otp']) || !isset($_SESSION['signup_expiry']))  {
            $errors['otp'] = "Please request OTP first!";
        } elseif (strtotime($_SESSION['signup_expiry']) < time()) {
            $errors['otp'] = "OTP expired!";
        } elseif (!password_verify($entered_otp, $_SESSION['signup_otp'])) {
            $errors['otp'] = "Invalid OTP!";
        } else {
            unset($_SESSION['signup_step']);
            unset($_SESSION['signup_otp']);
            unset($_SESSION['signup_expiry']);
        }

        if (empty($errors)) {
            $data = $_SESSION['signup_data'];

            $name = $data['name'];
            $phone = $data['phone'];
            $password = $data['password'];
            
            $email=$_SESSION['signup_email'];
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $conn->query("INSERT INTO users (email,name,password,phone_number,role)
            VALUES('$email','$name','$hashedPassword','$phone','customer')");

            $_SESSION["user"] = $email;
            $_SESSION["usertype"] = "customer";
            $_SESSION["username"] = $fullname;

            $result = $conn->query("select id from users where email='$email'");
            $row = mysqli_fetch_assoc($result);
            $user_id = $row['id'];
            $_SESSION['user_id'] = $user_id;
                
            header('Location: index.php');
            exit();
        }
    }
    include "includes/header.php";
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap" rel="stylesheet">
</head>

<body>
    <div class="signup-container">
        <div class="signup-card">
            <!-- ================= FULL FORM ================= -->
            <?php if($_SESSION['signup_step'] === 'form'): ?>
            <div id="signup-form">
                <h2 class="signup-title">Let's Get Started</h2>
                <p class="signup-subtitle">Create your account to continue</p>

                <form method="POST">
                    <!-- FULL NAME -->
                    <div class="signup-group">
                        <label class="signup-label">Full Name</label>
                        <input type="text" name="fullname" class="signup-input"
                            value="<?php echo $_POST['fullname'] ?? ''; ?>">
                        <span class="signup-error"><?php echo $errors['fullname'] ?? ''; ?></span>
                    </div>

                    <!-- EMAIL -->
                    <div class="signup-group">
                        <label class="signup-label">Email</label>
                        <input type="text" name="newemail" class="signup-input"
                            value="<?php echo $_SESSION['signup_email'] ?? ($_POST['newemail'] ?? ''); ?>">
                        <span class="signup-error"><?php echo $errors['email'] ?? ''; ?></span>
                    </div>

                    <!-- PHONE -->
                    <div class="signup-group mt-3">
                        <label class="signup-label">Mobile Number</label>
                        <input type="tel" name="tele" class="signup-input" value="<?php echo $_POST['tele'] ?? ''; ?>">
                        <span class="signup-error"><?php echo $errors['tele'] ?? ''; ?></span>
                    </div>

                    <!-- PASSWORD -->
                    <div class="signup-group">
                        <label class="signup-label">Password</label>
                        <input type="password" name="newpassword" class="signup-input">
                        <span class="signup-error"><?php echo $errors['password'] ?? ''; ?></span>
                    </div>

                    <!-- CONFIRM PASSWORD -->
                    <div class="signup-group">
                        <label class="signup-label">Confirm Password</label>
                        <input type="password" name="cpassword" class="signup-input">
                        <span class="signup-error"><?php echo $errors['cpassword'] ?? ''; ?></span>
                    </div>

                    <!-- SEND OTP BUTTON -->
                    <button type="submit" name="send_otp" class="signup-btn">
                        Send OTP
                    </button>
                    <div class="signup-links">
                        <p>Already have an account?
                            <a href="login.php">Login</a>
                        </p>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- ================= OTP FORM ================= -->
            <?php if($_SESSION['signup_step'] === 'otp'): ?>
            <div id="otp-form">
                <h2 class="signup-title">Verify OTP</h2>
                <form method="POST">
                    <label class="signup-label">Enter OTP</label>
                    <input type="text" name="otp" class="signup-input" placeholder="Enter OTP">
                    <span class="signup-error"><?php echo $errors['otp'] ?? ''; ?></span>
                    <button type="submit" name="signup" class="signup-btn">
                        Create Account
                    </button>
                    <button type="submit" name="edit_details" class="signup-btn">Edit Details</button>

                </form>

            </div>
            <?php endif; ?>
        </div>

    </div>

    <?php include "includes/footer.php"; ?>
</body>

</html>