<?php
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: ../index.php');
        die(0);
    }
    if (isset($_GET['id'])) {
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
