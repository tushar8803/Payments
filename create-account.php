<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/signup.css">

    <title>Create Account</title>
    <style>
        .container {
            animation: transitionIn-X 0.5s;
        }
    </style>
</head>

<body>
    <?php

    //learn from w3schools.com
    //Unset all the server side variables

    session_start();

    $_SESSION["user"] = "";
    $_SESSION["usertype"] = "";

    // Set the new timezone
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d');

    $_SESSION["date"] = $date;


    //import database
    include("config/db.php");





    // Run this code ONLY when the form is submitted

    $errors = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['newemail']);
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

        // ==========================
        // 2. Email Validation
        // ==========================
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format";
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

        // ==========================
        // 6. Check Email Already Exists
        // ==========================
        if (empty($errors)) {

            $check = $conn->query("SELECT * FROM users WHERE email='$email'");

            if ($check->num_rows > 0) {
                $errors['email'] = "Email already registered";
            }
        }

        if (empty($errors)) {

            $hashedPassword = password_hash($newpassword, PASSWORD_DEFAULT);

            $conn->query("INSERT INTO users (email,name,password,phone_number,role)
        VALUES('$email','$fullname','$hashedPassword','$tele','customer')");

            $_SESSION["user"] = $email;
            $_SESSION["usertype"] = "customer";
            $_SESSION["username"] = $fname;

            $result = $conn->query("select id from users where email='$email'");
            $row = mysqli_fetch_assoc($result);
            $user_id = $row['id'];
            $_SESSION['user_id'] = $user_id;

            header('Location: index.php');



            exit();
        }
    }

    ?>




    </tr>
    <center>
        <div class="container">
            <table border="0" style="width: 69%;">
                <form action="" method="POST">
                <tr>
                    <td colspan="2">
                        <p class="header-text">Let's Get Started</p>
                        <p class="sub-text">It's Okay, Now Create User Account.</p>
                    </td>
                </tr>

                <tr>
                    

                        <td class="label-td" colspan="2">
                            <label for="fullname" class="form-label">Full Name: </label>
                        </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <input type="text" name="fullname" class="input-text" placeholder="Enter Your Full Name(only letters are allowed)"
                            value="<?php echo $_POST['fullname'] ?? ''; ?>">

                        <span style="color:red;">
                            <?php echo $errors['fullname'] ?? ''; ?>
                        </span>
                    </td>
                </tr>

                <tr>

                    <td class="label-td" colspan="2">
                        <label for="newemail" class="form-label">Email: </label>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <input type="email" name="newemail" class="input-text" placeholder="(abc@gmail.com)"
                            value="<?php echo $_POST['newemail'] ?? ''; ?>">

                        <span style="color:red;">
                            <?php echo $errors['email'] ?? ''; ?>
                        </span>
                    </td>

                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <label for="tele" class="form-label">Mobile Number: </label>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <input type="tel" name="tele" class="input-text" 
                            value="<?php echo $_POST['tele'] ?? ''; ?>">

                        <span style="color:red;">
                            <?php echo $errors['tele'] ?? ''; ?>
                        </span>

                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <label for="newpassword" class="form-label">Create New Password: </label>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <input type="password" name="newpassword" class="input-text" placeholder="must contain 6characters including 1number,1special character">

                        <span style="color:red;">
                            <?php echo $errors['password'] ?? ''; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <label for="cpassword" class="form-label">Conform Password: </label>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <input type="password" name="cpassword" class="input-text">

                        <span style="color:red;">
                            <?php echo $errors['cpassword'] ?? ''; ?>
                        </span>
                    </td>
                </tr>

                <!-- <tr>

                    <td colspan="2">
                        <?php echo $error ?>

                    </td>
                </tr> -->

                <tr>
                    <!-- <td>
                        <input type="reset" value="Reset" class="login-btn btn-primary-soft btn">
                    </td> -->
                    <td>
                        <input type="submit" value="Sign Up" class="login-btn btn-primary btn">
                    </td>
                <tr>
                    <td colspan="2">
                        <br>
                        <label for="" class="sub-text" style="font-weight: 280;">Already have an account&#63; </label>
                        <a href="login.php" class="hover-link1 non-style-link">Login</a>
                        <br><br><br>
                    </td>
                </tr>

                </form>
                </tr>
            </table>

        </div>
    </center>
</body>

</html>