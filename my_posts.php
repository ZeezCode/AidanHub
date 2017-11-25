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

    if (!$_SESSION['user']['activated']) {
        $app->displayRequireActivationPage();
    }
?>
<html>
    <head>
        <title>AidanHub My Posts</title>

        <link rel="stylesheet" type="text/css" href="css/normalize.css" />
        <link rel="stylesheet" type="text/css" href="css/my.css?<?php echo time(); ?>" />

        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <div id="header">
            <span id="header_links"><a href="home.php">Home</a> | <a href="upload.php">Upload</a> | <a href="profile.php">My Profile</a> | <a href="my_posts.php">My Posts</a> | <a href="reset.php">Log Out</a></span>
        </div>
        <div id="content">
            <div id="image_block">
                <?php
                $getLatestSQL = sprintf("SELECT * FROM images WHERE uid = %d ORDER BY timestamp DESC;",
                    mysqli_real_escape_string($db, $_SESSION['user']['uid']));
                $getLatestQuery = mysqli_query($db, $getLatestSQL);
                if (mysqli_num_rows($getLatestQuery) == 0) {
                    echo "<span>You haven't made any posts yet!</span>";
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
        </div>
    </body>
</html>
