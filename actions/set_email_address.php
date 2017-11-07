<?php
    session_start();
    require '../App.php';
    require '../AppConfig.php';
    if (isset($_SESSION['user']) && isset($_GET['email'])) {
        $result = array();
        $result['status'] = 0;

        if (!filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
            $result['status'] = 1;
            $result['error'] = "Invalid email address!";
        } else {
            $db = AppConfig::getDatabaseConnection();
            $app = new App($db);
            if ($app->getUserFromEmail($_GET['email']) != null) {
                $result['status'] = 1;
                $result['error'] = "A user already exists with this email address!";
            } else {
                $resetStatement = sprintf("DELETE FROM email_confirmation WHERE uid = %d;",
                    mysqli_real_escape_string($db, $_SESSION['user']['uid']));
                mysqli_query($db, $resetStatement);

                $verifyToken = $app->generateRandomString(32);
                $createStatement = sprintf("INSERT INTO email_confirmation VALUES ('%s', %d, '%s');",
                    mysqli_real_escape_string($db, $verifyToken),
                    mysqli_real_escape_string($db, $_SESSION['user']['uid']),
                    mysqli_real_escape_string($db, $_GET['email']));

                if (!mysqli_query($db, $createStatement)) {
                    $result['status'] = 1;
                    $result['error'] = "An error occurred while attempting to save your new email address!";
                } else {
                    $to = $_GET['email'];
                    $subject = "AidanHub - Confirm Email Change";
                    $message = "
                        Hello, " . $_SESSION['user']['username'] . "! <br />
                        <br />
                        You recently requested to have the email address on your account changed.<br />
                        If this is correct, please click the following link to confirm the change:<br />
                        <a href='http://aidanmurphey.com/hub/actions/confirm_email.php?token=$verifyToken'>Click Here to Confirm Email Change</a><br />
                        
                        You may safely ignore and delete this email if you don't want to confirm the change.<br />
                        <br />
                        Regards,<br />
                        AidanHub - no-reply@AidanMurphey.com<br />
                    ";
                    $headers = 'From: no-reply@aidanmurphey.com' . "\r\n" .
                        'Reply-To: no-reply@aidanmurphey.com' . "\r\n" .
                        'Content-Type: text/html; charset=ISO-8859-1\r\n' .
                        'X-Mailer: PHP/' . phpversion();
                    mail($to, $subject, $message, $headers);
                }
            }
        }
        echo json_encode($result);
    }
