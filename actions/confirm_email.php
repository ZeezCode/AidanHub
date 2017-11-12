<?php
    require '../AppConfig.php';
    require '../App.php';

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
            $uData = (new App($db))->getUserFromId($data['uid']);

            if ($uData['activated']) { //User is changing email address
                $createChangeSQL = sprintf("UPDATE users SET email = '%s' WHERE uid = %d;",
                    mysqli_real_escape_string($db, $data['email']),
                    mysqli_real_escape_string($db, $data['uid']));
            } else { //User is confirming original email address
                $createChangeSQL = sprintf("UPDATE users SET activated = %d WHERE uid = %d;",
                    mysqli_real_escape_string($db, 1),
                    mysqli_real_escape_string($db, $data['uid']));
            }
            mysqli_query($db, $createChangeSQL);

            $removeConfirmationDataSQL = sprintf("DELETE FROM email_confirmation WHERE token = '%s';",
                mysqli_real_escape_string($db, $_GET['token']));
            mysqli_query($db, $removeConfirmationDataSQL);
            ?>
            <h2>You've successfully confirmed/changed your email address! <a href="../index.php">Click here to return to the login page.</a></h2>
            <?php
        }
    }
