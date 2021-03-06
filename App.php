<?php

class App {

    public $dbconnect = null;

    function __construct($dbconnect)
    {
        $this->dbconnect = $dbconnect;
    }

    function getUserFromEmail($email) {
        $getUserSQL = sprintf("SELECT * FROM users WHERE email = '%s';",
            mysqli_real_escape_string($this->dbconnect, $email));
        $getUserQuery = mysqli_query($this->dbconnect, $getUserSQL);

        if (mysqli_num_rows($getUserQuery) > 0) {
            return mysqli_fetch_assoc($getUserQuery);
        }
    }

    function getUserFromId($id) {
        $getUserSQL = sprintf("SELECT * FROM users WHERE uid = %d;",
            mysqli_real_escape_string($this->dbconnect, $id));
        $getUserQuery = mysqli_query($this->dbconnect, $getUserSQL);

        if (mysqli_num_rows($getUserQuery) > 0) {
            return mysqli_fetch_assoc($getUserQuery);
        }
    }

    function userHasSalt($salt) {
        $getUserSQL = sprintf("SELECT uid FROM users WHERE salt = '%s';",
            mysqli_real_escape_string($this->dbconnect, $salt));
        $getUserQuery = mysqli_query($this->dbconnect, $getUserSQL);

        return (mysqli_num_rows($getUserQuery) > 0);
    }

    function imageHasID($iid) {
        $getImageSQL = sprintf("SELECT iid FROM images WHERE iid = '%s';",
            mysqli_real_escape_string($this->dbconnect, $iid));
        $getImageQuery = mysqli_query($this->dbconnect, $getImageSQL);

        return (mysqli_num_rows($getImageQuery) > 0);
    }

    function registerThroughTwitch($profile) {
        $user = $this->getUserFromEmail($profile->email);
        if ($user != null) {
            return $user;
        } else {
            $timestamp = time();
            $ip = $_SERVER['REMOTE_ADDR'];

            $createUserSQL = sprintf("INSERT INTO users VALUES (%d, '%s', NULL, NULL, '%s', %d, %d, '%s', '%s', '%s', NULL, %d);",
                mysqli_real_escape_string($this->dbconnect, 0), //uid
                mysqli_real_escape_string($this->dbconnect, $profile->display_name), //username
                mysqli_real_escape_string($this->dbconnect, $profile->email), //email
                mysqli_real_escape_string($this->dbconnect, $timestamp), //first seen
                mysqli_real_escape_string($this->dbconnect, $timestamp), //last seen
                mysqli_real_escape_string($this->dbconnect, $ip), //reg ip
                mysqli_real_escape_string($this->dbconnect, $ip), //last ip
                mysqli_real_escape_string($this->dbconnect, 't'), //account source
                mysqli_real_escape_string($this->dbconnect, 1)); //activated

            if (mysqli_query($this->dbconnect, $createUserSQL)) {
                return $this->getUserFromEmail($profile->email);
            } else {
                return null;
            }
        }
    }

    function registerThroughLocalSite($email, $username, $password) {
        $user = $this->getUserFromEmail($email);
        if ($user != null) {
            return $user;
        } else {
            $timestamp = time();
            $ip = $_SERVER['REMOTE_ADDR'];
            $salt = $this->getUniqueSalt();
            $pass = md5( md5($salt) . md5($password) );

            $createUserSQL = sprintf("INSERT INTO users VALUES (%d, '%s', '%s', '%s', '%s', %d, %d, '%s', '%s', '%s', NULL, %d);",
                mysqli_real_escape_string($this->dbconnect, 0), //uid
                mysqli_real_escape_string($this->dbconnect, $username), //username
                mysqli_real_escape_string($this->dbconnect, $pass), //password
                mysqli_real_escape_string($this->dbconnect, $salt), //salt
                mysqli_real_escape_string($this->dbconnect, $email), //email
                mysqli_real_escape_string($this->dbconnect, $timestamp), //first seen
                mysqli_real_escape_string($this->dbconnect, $timestamp), //last seen
                mysqli_real_escape_string($this->dbconnect, $ip), //reg ip
                mysqli_real_escape_string($this->dbconnect, $ip), //last ip
                mysqli_real_escape_string($this->dbconnect, 'l'), //account source
                mysqli_real_escape_string($this->dbconnect, 0)); //activated

            if (mysqli_query($this->dbconnect, $createUserSQL)) {
                $finalUser = $this->getUserFromEmail($email);
                $verifyToken = $this->generateRandomString(32);
                $createStatement = sprintf("INSERT INTO email_confirmation VALUES ('%s', %d, '%s');",
                    mysqli_real_escape_string($this->dbconnect, $verifyToken),
                    mysqli_real_escape_string($this->dbconnect, $finalUser['uid']),
                    mysqli_real_escape_string($this->dbconnect, $_GET['email']));
                mysqli_query($this->dbconnect, $createStatement);

                $subject = "AidanHub - Account Confirmation";
                $message = "
                Hello, " . $username .  "!<br />
                <br />
                Thank you for registering an account on AidanHub!<br />
                To complete the registration process, click the following link:<br />
                <a href='http://aidanmurphey.com/hub/actions/confirm_email.php?token=$verifyToken'>Click Here to Activate your Account</a><br />
                <br />
                Regards,<br />
                AidanHub - no-reply@AidanMurphey.com<br />
                ";
                $headers = 'From: no-reply@aidanmurphey.com' . "\r\n" .
                    'Reply-To: no-reply@aidanmurphey.com' . "\r\n" .
                    'Content-Type: text/html; charset=ISO-8859-1\r\n' .
                    'X-Mailer: PHP/' . phpversion();
                mail($email, $subject, $message, $headers);

                return $finalUser;
            } else {
                return null;
            }
        }
    }

    function generateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getUniqueSalt($length = 10) {
        $salt = $this->generateRandomString($length);
        while ($this->userHasSalt($salt)) {
            $salt = $this->generateRandomString($length);
        }
        return $salt;
    }

    function getUniqueImageID($length = 8) {
        $iid = $this->generateRandomString($length);
        while ($this->imageHasID($iid)) {
            $iid = $this->generateRandomString($length);
        }
        return $iid;
    }

    function registerImageUpload($iid, $title, $description, $private) {
        $db = $this->dbconnect;
        $registerImageSQL = sprintf("INSERT INTO images VALUES ('%s', %d, '%s', '%s', %d, %d, '%s');",
            mysqli_real_escape_string($db, $iid), //Image ID
            mysqli_real_escape_string($db, $_SESSION['user']['uid']), //User ID
            mysqli_real_escape_string($db, $title), //Title
            mysqli_real_escape_string($db, $description), //Description
            mysqli_real_escape_string($db, $private ? 1 : 0), //Private
            mysqli_real_escape_string($db, time()), //Timestamp
            mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR'])); //IP
        mysqli_query($db, $registerImageSQL);
    }

    function getUserPoints($user) {
        $db = $this->dbconnect;
        $getPostIdsSQL = sprintf("SELECT iid FROM images WHERE uid = %d;",
            mysqli_real_escape_string($db, $user['uid']));
        $getPostIdsQuery = mysqli_query($db, $getPostIdsSQL);
        if (mysqli_num_rows($getPostIdsQuery) > 0) {
            $list = array();
            while ($image = mysqli_fetch_assoc($getPostIdsQuery)) {
                array_push($list, $image['iid']);
            }
            array_walk($list, function(&$elem, $key) {
               $elem = mysqli_real_escape_string(AppConfig::getDatabaseConnection(), $elem); //won't let me use $db here
            });
            $getPointsSQL = 'SELECT SUM(type) AS points FROM votes WHERE iid IN ("' . implode('", "', $list) . '");';
            $getPointsQuery = mysqli_query($db, $getPointsSQL);
            return mysqli_fetch_assoc($getPointsQuery)['points'];
        }
        return 0;
    }

    function getUserPosts($user) {
        $db = $this->dbconnect;
        $getScoreSQL = sprintf("SELECT COUNT(*) AS posts FROM images WHERE uid = %d;",
            mysqli_real_escape_string($db, $user['uid']));
        $getScoreQuery = mysqli_query($db, $getScoreSQL);
        if (mysqli_num_rows($getScoreQuery) > 0) {
            return mysqli_fetch_assoc($getScoreQuery)['posts'];
        }
        return 0;
    }

    function refreshUserCache() {
        if (isset($_SESSION['user'])) {
            $db = $this->dbconnect;
            $uid = $_SESSION['user']['uid'];
            $setLastSeenSQL = sprintf("UPDATE users SET last_seen = %d, last_ip = '%s' WHERE uid = %d;",
                mysqli_real_escape_string($db, time()),
                mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR']),
                mysqli_real_escape_string($db, $uid));
            mysqli_query($db, $setLastSeenSQL);

            $_SESSION['user'] = $this->getUserFromId($uid);
        }
    }

    function displayRequireActivationPage() {
        ?>
        <h1>You can not access this page as your account has not yet been activated!</h1>
        <h2>Check your inbox for the confirmation email to activate your account and gain access to all features!</h2>
        <h3>Your email: <?php echo htmlentities($_SESSION['user']['email']); ?></h3>
        <?php
        die(0);
    }

    function getUserVoteOnImage($iid) {
        $db = $this->dbconnect;
        $vote = 0;

        $getVoteSQL = sprintf("SELECT type FROM votes WHERE iid = '%s' AND uid = %d;",
            mysqli_real_escape_string($db, $iid),
            mysqli_real_escape_string($db, $_SESSION['user']['uid']));
        $getVoteQuery = mysqli_query($db, $getVoteSQL);
        if (mysqli_num_rows($getVoteQuery) > 0) {
            $vote = mysqli_fetch_assoc($getVoteQuery)['type'];
        }
        return $vote;
    }

}
