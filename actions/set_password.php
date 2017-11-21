<?php
    require '../AppConfig.php';
    require '../App.php';

    if (isset($_SESSION['user'])) { //Already logged in
        header('Location: home.php');
        die(0);
    }

    if (isset($_POST['token']) && isset($_POST['pass1']) && isset($_POST['pass2'])) {
        if ($_POST['pass1'] !== $_POST['pass2']) {
            echo "Passwords don't match.<br /><a href=../change_password.php?token=" . $_POST['token'] . "'>Click here to go back</a>";
            die(0);
        }
        $db = AppConfig::getDatabaseConnection();

        $getUidSQL = sprintf("SELECT uid FROM password_confirmation WHERE token = '%s';",
            mysqli_real_escape_string($db, $_POST['token']));
        $getUidQuery = mysqli_query($db, $getUidSQL);

        if (mysqli_num_rows($getUidQuery) > 0) {
            $uid = mysqli_fetch_assoc($getUidQuery)['uid'];
            $app = new App($db);
            $user = $app->getUserFromId($uid);

            $newPass = md5( md5($user['salt']) . md5($_POST['pass1']) );

            $setPassSQL = sprintf("UPDATE users SET password = '%s' WHERE uid = %d;",
                mysqli_real_escape_string($db, $newPass),
                mysqli_real_escape_string($db, $uid));
            mysqli_query($db, $setPassSQL);

            $removeTokenSQL = sprintf("DELETE FROM password_confirmation WHERE token = '%s';",
                mysqli_real_escape_string($db, $_POST['token']));
            mysqli_query($db, $removeTokenSQL);
            echo "You have successfully changed your password!<br />";
            echo "<a href='../index.php'>Click Here to Log In</a>";
        }
    }

