<?php
    if (isset($_GET['email'])) {
        require '../AppConfig.php';
        require '../App.php';

        $email = $_GET['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "2";
        } else {
            $app = new App(AppConfig::getDatabaseConnection());
            echo($app->getUserFromEmail($_GET['email']) == null ? "0" : "1");
        }
    }