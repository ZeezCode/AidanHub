<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';
    require 'Image.php';

    if (!isset($_SESSION['user'])) { //Not logged in
        header('Location: index.php');
        die(0);
    }

    $app = new App(AppConfig::getDatabaseConnection());
    $app->refreshUserCache();

    if (isset($_FILES['image']['tmp_name'])) {
        $whitelist_type = array('image/jpeg', 'image/jpg', 'image/png','image/gif');
        $whitelist_ext = array('jpeg','jpg','JPEG', 'JPG','png','PNG','gif','GIF');

        $verifyImage = getimagesize($_FILES['image']['tmp_name']);
        if (!in_array($verifyImage['mime'], $whitelist_type)) { //File is not of valid type
            echo "You've submitted a disallowed file type!";
            die(1);
        }

        $fileInfo = pathinfo($_FILES['image']['name']);
        $ext = $fileInfo['extension'];
        if (!in_array($ext, $whitelist_ext)) { //File has illegal extension
            echo "You've submitted a disallowed file type!";
            die(1);
        }

        $imageID = $app->getUniqueImageID();
        $uploadDirectory = "/var/www/ahub_uploads/";
        $uploadFile = $uploadDirectory . $imageID . "." . $ext;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $app->registerImageUpload($imageID, $_POST['title'], $_POST['description'], $_POST['private'] == "on" ? true : false);
            echo "Image upload succeeded!<br />";
            echo $imageID;
        } else {
            echo "Image upload failed!";
            die(1);
        }
    }
?>
<html>
    <head>
        <title>AidanHub Upload</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="css/normalize.css" />
        <link rel="stylesheet" type="text/css" href="css/upload.css?<?php echo time(); ?>" />

        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <div id="header">
            <span id="header_links"><a href="home.php">Home</a> | <a href="upload.php">Upload</a> | <a href="profile.php">My Profile</a> | <a href="my_posts.php">My Posts</a> | <a href="reset.php">Log Out</a></span>
        </div>
        <div id="content">
            <div id="upload_block">
                <form id="upload_form" action="upload.php" method="post" enctype="multipart/form-data">
                    <table id="upload_table">
                        <tr>
                            <td align="center"><img id="preview" src="http://via.placeholder.com/150x150" /></td>
                        </tr>
                        <tr>
                            <td align="center">
                                <input type="file" name="image" accept="image/*" id="upload_input" />
                                <label for="upload_input">Choose a File</label>
                            </td>
                        </tr>
                        <tr>
                            <td align="center"><input type="text" name="title" maxlength="64" placeholder="Title" /></td>
                        </tr>
                        <tr>
                            <td align="center"><textarea name="description" form="upload_form" placeholder="Description" cols="30" rows="6" wrap="off" ></textarea></td>
                        </tr>
                        <tr>
                            <td align="center">Private: <input type="checkbox" name="private" /></td>
                        </tr>
                        <tr>
                            <td align="center"><input type="submit" value="Upload"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <script>
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#upload_input").change(function() {
                readURL(this);
            });
        </script>
    </body>
</html>
