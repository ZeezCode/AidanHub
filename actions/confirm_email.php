<?php
    require '../AppConfig.php';

    if (isset($_GET['token'])) {

        $db = AppConfig::getDatabaseConnection();

        $getConfirmationDataSQL = sprintf("SELECT * FROM email_confirmation WHERE token = '%s';",
            mysqli_real_escape_string($db, $_GET['token']));
        $getConfirmationDataQuery = mysqli_query($db, $getConfirmationDataSQL);
        if (mysqli_num_rows($getConfirmationDataQuery) == 0) {
            ?>
            <h2>Invalid confirmation token. <a href="../index.php">Click here to return to the login page.</a></h2>
            <?php
        } else {
            $data = mysqli_fetch_assoc($getConfirmationDataQuery);

            $createChangeSQL = sprintf("UPDATE users SET email = '%s' WHERE uid = %d;",
                mysqli_real_escape_string($db, $data['email']),
                mysqli_real_escape_string($db, $data['uid']));
            mysqli_query($db, $createChangeSQL);

            $removeConfirmationDataSQL = sprintf("DELETE FROM email_confirmation WHERE token = '%s';",
                mysqli_real_escape_string($db, $_GET['token']));
            mysqli_query($db, $removeConfirmationDataSQL);
            ?>
            <h2>You've successfully changed your email address! <a href="../index.php">Click here to return to the login page.</a></h2>
            <?php
        }
    }
