<?php
    session_start();
    require '../App.php';
    require '../AppConfig.php';
    if (isset($_SESSION['user']) && isset($_GET['iid']) && isset($_GET['vote'])) {
        $result = array();
        $result['status'] = 0;
        $result['type'] = $_GET['vote'];

        $vote = $_GET['vote'];
        $iid = $_GET['iid'];
        if ($vote != -1 && $vote != 1) {
            $result['status'] = 1;
            $result['error'] = "Invalid vote type";
        } else {
            $db = AppConfig::getDatabaseConnection();
            $app = new App($db);

            if (!$app->imageHasID($iid)) {
                $result['status'] = 1;
                $result['error'] = "Image does not exist";
            } else {
                $curVote = $app->getUserVoteOnImage($iid);
                $result['old'] = $curVote;
                if ($curVote == $vote) { //User is undoing vote
                    $result['type'] = 0;
                    $removeVoteSQL = sprintf("DELETE FROM votes WHERE iid = '%s' AND uid = %d;",
                        mysqli_real_escape_string($db, $iid),
                        mysqli_real_escape_string($db, $_SESSION['user']['uid']));
                    if (!mysqli_query($db, $removeVoteSQL)) {
                        $result['status'] = 1;
                        $result['error'] = "An error occurred while attempting to save your vote.";
                    }
                } elseif ($curVote !== 0) { //User is changing vote
                    $updateVoteSQL = sprintf("UPDATE votes SET type = %d, timestamp = %d WHERE iid = '%s' AND uid = %d;",
                        mysqli_real_escape_string($db, $vote),
                        mysqli_real_escape_string($db, time()),
                        mysqli_real_escape_string($db, $iid),
                        mysqli_real_escape_string($db, $_SESSION['user']['uid']));
                    if (!mysqli_query($db, $updateVoteSQL)) {
                        $result['status'] = 1;
                        $result['error'] = "An error occurred while attempting to save your vote.";
                    }
                } else { //User is adding new vote
                    $newVoteSQL = sprintf("INSERT INTO votes VALUES (%d, '%s', %d, %d, %d, '%s');",
                        mysqli_real_escape_string($db, 0),
                        mysqli_real_escape_string($db, $iid),
                        mysqli_real_escape_string($db, $_SESSION['user']['uid']),
                        mysqli_real_escape_string($db, $vote),
                        mysqli_real_escape_string($db, time()),
                        mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR']));
                    if (!mysqli_query($db, $newVoteSQL)) {
                        $result['status'] = 1;
                        $result['error'] = "An error occurred while attempting to save your vote.";
                    }
                }
            }
        }
        echo json_encode($result);
    }
