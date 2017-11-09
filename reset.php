<?php
    session_start();

    if (isset($_SESSION['user'])) {
        require 'AppConfig.php';
        require 'App.php';
        $app = new App(AppConfig::getDatabaseConnection());
        $app->refreshUserCache();
    }

    session_unset();
    header('Location: index.php');
