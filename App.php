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

    function registerThroughTwitch($profile) {
        $user = $this->getUserFromEmail($profile->email);
        if ($user != null) {
            return $user;
        } else {
            $timestamp = time();
            $ip = $_SERVER['REMOTE_ADDR'];

            $createUserSQL = sprintf("INSERT INTO users VALUES (%d, '%s', NULL, NULL, '%s', %d, %d, '%s', '%s', '%s');",
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

}
