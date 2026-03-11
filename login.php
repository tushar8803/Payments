<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/login.css">

    <title>Login</title>

</head>

<body>
    <?php

    //learn from w3schools.com
    //Unset all the server side variables

    session_start();

    $_SESSION["user"] = "";
    $_SESSION["usertype"] = "";
    $_SESSION["userid"] = "";

    // Set the new timezone
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d');

    $_SESSION["date"] = $date;


    //import database
    include("config/db.php");

    if ($_POST) {

        $identifier = $_POST['useridentifier'];
        $password = $_POST['userpassword'];

        $error = '<label for="promter" class="form-label"></label>';

        // First check if the identifier is an email in the webuser table
        $result = $conn->query("select * from users where email='$identifier'");
        $result2 = $conn->query("select * from users where phone_number='$identifier'");


        if ($result->num_rows == 1) {
            // If it's an email, proceed with the original login flow
            $utype = $result->fetch_assoc()['role'];   //fetching the value of usertype from the 
            // row stored in $result

            /* IMPORTANT */
            // checking which type of user trying to login(user/admin)
            if ($utype == 'customer') {
                $checker = $conn->query("select * from users where email='$identifier' and password='$password'");
                if ($checker->num_rows == 1) {
                    $_SESSION['user'] = $identifier;
                    $_SESSION['usertype'] = 'customer';

                    $result = $conn->query("select id from users where email='$identifier'");
                    $user_id = mysqli_fetch_assoc($result);
                    $user_id = $row['id'];
                    $_SESSION['user_id'] = $user_id;

                    header('location:user/select-service.php');
                } else {
                    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }
            } elseif ($utype == 'admin') {
                $checker = $conn->query("select * from users where email='$identifier' and password='$password'");
                if ($checker->num_rows == 1) {
                    $_SESSION['user'] = $identifier;
                    $_SESSION['usertype'] = 'admin';
                    header('location:admin/index.php');
                } else {
                    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }
            } elseif ($utype == 'staff') {
                $checker = $conn->query("select * from users where email='$identifier' and password='$password'");
                if ($checker->num_rows == 1) {
                    $_SESSION['user'] = $identifier;
                    $_SESSION['usertype'] = 'staff';
                    header('location: staff/index.php');
                } else {
                    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }
            }
        } elseif ($result2->num_rows == 1) {
            // If it's an email, proceed with the original login flow
            $utype = $result2->fetch_assoc()['role'];   //fetching the value of usertype from the 
            // row stored in $result

            /* IMPORTANT */
            // checking which type of user trying to login(user/admin)
            if ($utype == 'customer') {
                $checker = $conn->query("select * from users where phone_number='$identifier' and password='$password'");
                if ($checker->num_rows == 1) {
                    $_SESSION['user'] = $identifier;
                    $_SESSION['usertype'] = 'customer';

                    $result = $conn->query("select id from users where phone_number='$identifier'");
                    $row = mysqli_fetch_assoc($result);
                    $user_id = $row['id'];
                    $_SESSION['user_id'] = $user_id;

                    header('location:user/select-service.php');
                } else {
                    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }
            } elseif ($utype == 'admin') {
                $checker = $conn->query("select * from users where phone_number='$identifier' and password='$password'");
                if ($checker->num_rows == 1) {
                    $_SESSION['user'] = $identifier;
                    $_SESSION['usertype'] = 'admin';
                    header('location:admin/index.php');
                } else {
                    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }
            } elseif ($utype == 'staff') {
                $checker = $conn->query("select * from users where phone_number='$identifier' and password='$password'");
                if ($checker->num_rows == 1) {
                    $_SESSION['user'] = $identifier;
                    $_SESSION['usertype'] = 'staff';
                    header('location: staff/index.php');
                } else {
                    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }
            }
        } else {
            $error = '<label for="promter" class="form-label" 
   style="color:rgb(255, 62, 62);text-align:center;">
   Wrong credentials: Invalid email or password
   </label>';
        }
    } else {
        $error = '<label for="promter" class="form-label">&nbsp;</label>';
    }


    ?>

    <center>
        <div class="container">
            <table border="0" style="margin: 0;padding: 0;width: 60%;">
                <tr>
                    <td>
                        <p class="header-text">Welcome Back!</p>
                    </td>
                </tr>
                <div class="form-body">
                    <tr>
                        <td>
                            <p class="sub-text">Login with your details to continue</p>
                        </td>
                    </tr>
                    <tr>
                        <form action="" method="POST">
                            <td class="label-td">
                                <label for="useridentifier" class="form-label">Email or Phone: </label>
                            </td>
                    </tr>
                    <tr>
                        <td class="label-td">
                            <input type="text" name="useridentifier" class="input-text" placeholder="Email Address or Phone Number" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td">
                            <label for="userpassword" class="form-label">Password: </label>
                        </td>
                    </tr>

                    <tr>
                        <td class="label-td">
                            <input type="Password" name="userpassword" class="input-text" placeholder="Password" required>
                        </td>
                    </tr>


                    <tr>
                        <td><br>
                            <?php echo $error ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <input type="submit" value="Login" class="login-btn btn-primary btn">
                        </td>
                    </tr>
                </div>
                <tr>
                    <td>
                        <br>
                        <label for="" class="sub-text" style="font-weight: 280;">Don't have an account&#63; </label>
                        <a href="create-account.php" class="hover-link1 non-style-link">Sign Up</a>
                        <br>
                        <a href="forgot_password.php">Forgot password</a>
                        <br><br><br>
                    </td>
                </tr>
                </form>
            </table>

        </div>
    </center>
</body>

</html>