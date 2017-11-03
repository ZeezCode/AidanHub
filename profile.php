<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';

    if (!isset($_SESSION['user'])) { //Not logged in
        header('Location: index.php');
        die(0);
    }

    $app = new App(AppConfig::getDatabaseConnection());
?>
<html>
    <head>
        <title><?php echo htmlspecialchars($_SESSION['user']['username']); ?>'s Profile</title>

        <link rel="stylesheet" type="text/css" href="css/normalize.css" />
        <link rel="stylesheet" type="text/css" href="css/profile.css?<?php echo time(); ?>" />

        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <div id="header">
            <span id="header_links"><a href="index.php">Home</a> | <a href="upload.php">Upload</a> | <a href="profile.php">My Profile</a> | <a href="reset.php">Log Out</a></span>
        </div>
        <div id="content">
            <div id="profile_block">
                <table id="profile_info">
                    <tr>
                        <td colspan="2"><img src="https://i.imgur.com/FDkrwc6.jpg" alt="Profile Pic" width="300"></td>
                    </tr>
                    <tr>
                        <td>Username:</td>
                        <td><?php echo htmlspecialchars($_SESSION['user']['username']); ?></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><?php echo htmlspecialchars($_SESSION['user']['email']); ?></td>
                    </tr>
                    <tr>
                        <td>Joined:</td>
                        <td><?php echo date("F j, Y, g:i a", $_SESSION['user']['last_seen']); ?></td>
                    </tr>
                    <tr>
                        <td>Points:</td>
                        <td><?php echo $app->getUserPoints($_SESSION['user']); ?></td>
                    </tr>
                    <tr>
                        <td>Posts:</td>
                        <td><?php echo $app->getUserPosts($_SESSION['user']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
