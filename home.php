<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';

    if (!isset($_SESSION['user'])) { //Not logged in
        header('Location: index.php');
        die(0);
    }
    echo "Welcome to AidanHub, " . $_SESSION['user']['username'] . "!";
