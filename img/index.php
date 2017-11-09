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

    if (isset($_GET['id'])) {
        $img = new Image(Image::getImageData($_GET['id']));

        if ($img->isPrivate() && $img->getUID()!=$_SESSION['user']['uid']) {
            echo "This image is private, you need to be its uploader in order to view it.";
            die(0);
        }

        $whitelist_ext = array('jpeg','jpg','JPEG', 'JPG','png','PNG','gif','GIF');
        $uploadDirectory = "/var/www/ahub_uploads/";
        $fileWithDir = $uploadDirectory . $_GET['id'];

        foreach($whitelist_ext as $ext) {
            if (file_exists($fileWithDir . "." . $ext)) {
                $fileWithDir .= "." . $ext;
            }
        }

        $imginfo = getimagesize($fileWithDir);
        header("Content-type: {$imginfo['mime']}");
        readfile($fileWithDir);
    }
