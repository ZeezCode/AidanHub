<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';
    require 'AppLang.php';

    if (isset($_SESSION['user'])) { //Already logged in
        header('Location: home.php');
        die(0);
    }

    $success = false;
    $email = $_POST['email'];

    if (isset($_POST['email'])) {
        $db = AppConfig::getDatabaseConnection();
        $app = new App($db);

        $user = $app->getUserFromEmail($email);
        if ($user == null) {
            die("No user exists with this email");
        }

        if ($user['account_source'] == 't') {
            die("Accounts registered through Twitch cannot change their password!");
        }

        $resetStatement = sprintf("DELETE FROM password_confirmation WHERE uid = %d;",
            mysqli_real_escape_string($db, $user['uid']));
        mysqli_query($db, $resetStatement);

        $verifyToken = $app->generateRandomString(32);
        $createStatement = sprintf("INSERT INTO password_confirmation VALUES ('%s', %d);",
            mysqli_real_escape_string($db, $verifyToken),
            mysqli_real_escape_string($db, $user['uid']));
        mysqli_query($db, $createStatement);

        $to = $email;
        $subject = "AidanHub - Confirm Password Change";
        $message = "
        Hello, " . $user['username'] . "!<br />
        <br />
        You recently requested to reset your password.<br />
        If this is correct, please click the following link to confirm the change:<br />
        <a href='http://aidanmurphey.com/hub/change_password.php?token=$verifyToken'>Click Here to Confirm Password Change</a><br />                
        <br />
        You may safely ignore and delete this email if you don't want to confirm the change.<br />
        <br />
        Regards,<br />
        AidanHub - no-reply@AidanMurphey.com
        ";
        $headers = 'From: no-reply@aidanmurphey.com' . "\r\n" .
            'Reply-To: no-reply@aidanmurphey.com' . "\r\n" .
            'Content-Type: text/html; charset=ISO-8859-1\r\n' .
            'X-Mailer: PHP/' . phpversion();
        mail($to, $subject, $message, $headers);
        $success = true;
    }
?>
<html>
    <head>
        <title>AidanHub - Forgot Password</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="css/normalize.css" >
        <link rel="stylesheet" type="text/css" href="css/forgot.css?<?php echo time(); ?>" >

        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <?php
            if ($success)
                echo "<h2 style='text-align: center'>An email has been sent to " . htmlentities($email) .  " </h2>";
        ?>
        <div id="forgot_block">
            <h2 id="logo">Forgot Password</h2>
            <form id="forgot" action="forgot_pass.php" method="post">
                <table id="forgot_table">
                    <tr>
                        <td><input type="email" name="email" placeholder="Email" /></td>
                    </tr>
                    <tr>
                        <td><input type="submit" value="Request Change" /></td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>
