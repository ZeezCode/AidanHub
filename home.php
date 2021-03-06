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

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>

        <link rel="stylesheet" type="text/css" href="css/normalize.css" />
        <link rel="stylesheet" type="text/css" href="css/home.css?<?php echo time(); ?>" />
        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <div id="header">
            <span id="header_links"><a href="home.php">Home</a> | <a href="upload.php">Upload</a> | <a href="profile.php">My Profile</a> | <a href="my_posts.php">My Posts</a> | <a href="reset.php">Log Out</a></span>
        </div>
        <div id="content">
            <div id="image_block">
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
                                    <img src="<?php echo $image->getDirectURL(); ?>" width="300" height="200" class="uploaded_image">
                                    <?php
                                        $user = $app->getUserFromId($image->getUID());
                                        $profilePicId = $user['picture_iid'];
                                        $direct = "http://aidanmurphey.com/hub/img?id=" . $profilePicId;
                                    ?>
                                </a>
                                <img class="uploaded_profile_pic" src="<?php echo $direct; ?>">
                                <div class="title"><?php echo $image->getTitle(); ?></div>
                            </div>
                            <?php
                        }
                    }
                ?>
            </div>
        </div>
        <?php
            if (isset($_SESSION['firstlogin'])) {
                unset($_SESSION['firstlogin']);
                ?>
                <script>
                    $.notify("Activate your account to gain access to all of the site's features!", "info");
                </script>
                <?php
            }
        ?>
    </body>
</html>
