<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';

    if (!isset($_SESSION['user'])) { //Not logged in
        header('Location: index.php');
        die(0);
    }
?>
<html>
    <head>
        <title>AidanHub Home</title>

        <link rel="stylesheet" type="text/css" href="css/normalize.css" />
        <link rel="stylesheet" type="text/css" href="css/home.css?<?php echo time(); ?>" />

        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <div id="header">
            <span id="header_links"><a href="#">Home</a> | <a href="upload.php">Upload</a> | <a href="profile.php">My Profile</a> | <a href="reset.php">Log Out</a></span>
        </div>
        <div id="content">
            <div class="gallery">
                <a target="_blank" href="https://i.imgur.com/hXFeHXW.jpg">
                    <img src="https://i.imgur.com/hXFeHXW.jpg" alt="Fjords" width="300" height="200">
                </a>
                <div class="desc">Cute doggo</div>
            </div>
        </div>
    </body>
</html>
