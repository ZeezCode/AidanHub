<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';
    require 'Image.php';

    if (!isset($_SESSION['user'])) { //Not logged in
        header('Location: index.php');
        die(0);
    }

    $db = AppConfig::getDatabaseConnection();
    $app = new App($db);
    $app->refreshUserCache();
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
            <span id="header_links"><a href="home.php">Home</a> | <a href="upload.php">Upload</a> | <a href="profile.php">My Profile</a> | <a href="reset.php">Log Out</a></span>
        </div>
        <div id="content">
            <?php
                $getLatestSQL = "SELECT * FROM images WHERE private = 0 ORDER BY timestamp DESC LIMIT 15;";
                $getLatestQuery = mysqli_query($db, $getLatestSQL);
                if (mysqli_num_rows($getLatestQuery) == 0) {
                    echo "<span>It seems there aren't any posts yet...</span>";
                } else {
                    while ($img = mysqli_fetch_assoc($getLatestQuery)) {
                        $image = new Image($img);
                        ?>
                        <div class="gallery">
                            <a href="<?php echo $image->getPostURL(); ?>">
                                <img src="<?php echo $image->getDirectURL(); ?>" width="300" height="200">
                            </a>
                            <div class="title"><?php echo $image->getTitle(); ?></div>
                        </div>
                        <?php
                    }
                }
            ?>
        </div>
    </body>
</html>
