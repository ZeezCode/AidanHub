<?php
    session_start();
    require '../App.php';
    require '../AppConfig.php';
    if (isset($_SESSION['user']) && isset($_GET['iid'])) {
        $result = array();
        $result['status'] = 0;

        $iid = $_GET['iid'];
        if (strlen($iid) !== 8) {
            $result['status'] = 1;
            $result['error'] = "Invalid IID submitted!";
        } else { //Valid IID
            $db = AppConfig::getDatabaseConnection();
            $app = new App($db);

            if (!$app->imageHasID($iid)) {
                $result['status'] = 1;
                $result['error'] = "No image exists with that IID!";
            } else {
                $setProfilePicSQL = sprintf("UPDATE users SET picture_iid = '%s' WHERE uid = %d;",
                    mysqli_real_escape_string($db, $iid),
                    mysqli_real_escape_string($db, $_SESSION['user']['uid']));
                if (mysqli_query($db, $setProfilePicSQL)) {
                    $result['direct'] = "http://aidanmurphey.com/hub/img?id=" . $iid;
                } else {
                    $result['status'] = 1;
                    $result['error'] = "An error occurred while attempting to save your profile picture!";
                }
            }
        }

        echo json_encode($result);
    }
