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

    function registerThroughLocalSite($email, $username, $password) {
        $user = $this->getUserFromEmail($email);
        if ($user != null) {
            return $user;
        } else {
            $timestamp = time();
            $ip = $_SERVER['REMOTE_ADDR'];
            $salt = $this->getUniqueSalt();
            $pass = md5( md5($salt) . md5($password) );

            $createUserSQL = sprintf("INSERT INTO users VALUES (%d, '%s', '%s', '%s', '%s', %d, %d, '%s', '%s', '%s');",
                mysqli_real_escape_string($this->dbconnect, 0), //uid
                mysqli_real_escape_string($this->dbconnect, $username), //username
                mysqli_real_escape_string($this->dbconnect, $pass), //password
                mysqli_real_escape_string($this->dbconnect, $this->getUniqueSalt()), //salt
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

}
