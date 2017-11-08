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

            $createUserSQL = sprintf("INSERT INTO users VALUES (%d, '%s', NULL, NULL, '%s', %d, %d, '%s', '%s', '%s', NULL);",
                mysqli_real_escape_string($this->dbconnect, 0), //uid
                mysqli_real_escape_string($this->dbconnect, $profile->display_name), //username
                mysqli_real_escape_string($this->dbconnect, $profile->email), //email
                mysqli_real_escape_string($this->dbconnect, $timestamp), //first seen
                mysqli_real_escape_string($this->dbconnect, $timestamp), //last seen
                mysqli_real_escape_string($this->dbconnect, $ip), //reg ip
                mysqli_real_escape_string($this->dbconnect, $ip), //last ip
                mysqli_real_escape_string($this->dbconnect, 't')); //account source

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

            $createUserSQL = sprintf("INSERT INTO users VALUES (%d, '%s', '%s', '%s', '%s', %d, %d, '%s', '%s', '%s', NULL);",
                mysqli_real_escape_string($this->dbconnect, 0), //uid
                mysqli_real_escape_string($this->dbconnect, $username), //username
                mysqli_real_escape_string($this->dbconnect, $pass), //password
                mysqli_real_escape_string($this->dbconnect, $salt), //salt
                mysqli_real_escape_string($this->dbconnect, $email), //email
                mysqli_real_escape_string($this->dbconnect, $timestamp), //first seen
                mysqli_real_escape_string($this->dbconnect, $timestamp), //last seen
                mysqli_real_escape_string($this->dbconnect, $ip), //reg ip
                mysqli_real_escape_string($this->dbconnect, $ip), //last ip
                mysqli_real_escape_string($this->dbconnect, 'l')); //account source

            if (mysqli_query($this->dbconnect, $createUserSQL)) {
                return $this->getUserFromEmail($email);
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
        $registerImageSQL = sprintf("INSERT INTO images VALUES ('%s', %d, '%s', '%s', %d, %d, %d, %d, '%s');",
            mysqli_real_escape_string($db, $iid), //Image ID
            mysqli_real_escape_string($db, $_SESSION['user']['uid']), //User ID
            mysqli_real_escape_string($db, $title), //Title
            mysqli_real_escape_string($db, $description), //Description
            mysqli_real_escape_string($db, 0), //Upvotes
            mysqli_real_escape_string($db, 0), //Downvotes
            mysqli_real_escape_string($db, $private ? 1 : 0), //Private
            mysqli_real_escape_string($db, time()), //Timestamp
            mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR'])); //IP
        mysqli_query($db, $registerImageSQL);
    }

    function getUserPoints($user) {
        $db = $this->dbconnect;
        $getScoreSQL = sprintf("SELECT SUM(upvotes) AS points FROM images WHERE uid = %d;",
            mysqli_real_escape_string($db, $user['uid']));
        $getScoreQuery = mysqli_query($db, $getScoreSQL);
        if (mysqli_num_rows($getScoreQuery) > 0) {
            $points = mysqli_fetch_assoc($getScoreQuery)['points'];
            return ($points == null ? 0 : $points);
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

}
