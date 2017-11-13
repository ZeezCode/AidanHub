<?php
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: ../index.php');
        die(0);
    }

    require '../AppConfig.php';
    require '../App.php';
    require '../Image.php';

    $app = new App(AppConfig::getDatabaseConnection());
    $app->refreshUserCache();

    if (!isset($_GET['id'])) {
        echo "The specified image does not exist";
        die(0);
    }

    $img = new Image(Image::getImageData($_GET['id']));
    if ($img->isPrivate() && $img->getUID() != $_SESSION['user']['uid']) {
        echo "This image is private, you need to be its uploader in order to view it.";
        die(0);
    }
?>
<html>
    <head>
        <title>AidanHub Home</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>

        <link rel="stylesheet" type="text/css" href="../css/normalize.css" />
        <link rel="stylesheet" type="text/css" href="../css/post.css?<?php echo time(); ?>" />
        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <div id="header">
            <span id="header_links"><a href="../home.php">Home</a> | <a href="../upload.php">Upload</a> | <a href="../profile.php">My Profile</a> | <a href="../my_posts.php">My Posts</a> | <a href="../reset.php">Log Out</a></span>
        </div>
        <div id="content">
            <div id="post_block">
                <table id="post_table">
                    <tr>
                        <td><?php echo $img->getTitle(); ?> by <?php echo $app->getUserFromId($img->getUID())['username']; ?></td>
                    </tr>
                    <tr>
                        <td align="center">
                            <div id="image_container">
                                <a href="<?php echo $img->getDirectURL(); ?>"><img src="<?php echo $img->getDirectURL(); ?>" /></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Upvote/Downvote</td>
                    </tr>
                    <tr>
                        <td><?php echo $img->getDescription(); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
