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

    if (!$_SESSION['user']['activated']) {
        $app->displayRequireActivationPage();
    }
?>
<html>
    <head>
        <title><?php echo htmlspecialchars($_SESSION['user']['username']); ?>'s Profile</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>

        <link rel="stylesheet" type="text/css" href="css/normalize.css" />
        <link rel="stylesheet" type="text/css" href="css/profile.css?<?php echo time(); ?>" />

        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <div id="header">
            <span id="header_links"><a href="home.php">Home</a> | <a href="upload.php">Upload</a> | <a href="profile.php">My Profile</a> | <a href="my_posts.php">My Posts</a> | <a href="reset.php">Log Out</a></span>
        </div>
        <div id="content">
            <div id="profile_block">
                <table id="profile_info">
                    <tr>
                        <td><?php echo htmlspecialchars($_SESSION['user']['username']); ?></td>
                    </tr>
                    <tr>
                        <?php
                            $url = "http://via.placeholder.com/300x150";
                            $iid = $_SESSION['user']['picture_iid'];
                            if (!empty($iid) && $app->imageHasID($iid)) {
                                $img = new Image(Image::getImageData($iid));
                                $url = $img->getDirectURL();
                            }
                        ?>
                        <td><img id="profile_picture_display" src="<?php echo $url; ?>" alt="Profile Pic" height="200"></td>
                    </tr>
                    <tr>
                        <td><input type="text" id="profile_picture" name="profile_picture" placeholder="AHub Image ID" maxlength="8" value="<?php echo $iid; ?>" /></td>
                    </tr>
                    <tr>
                        <td>
                            <input type="email" id="email_address" name="email_address" placeholder="Email" value="<?php echo $_SESSION['user']['email']; ?>" />
                            <input type="hidden" id="current_profile_picture" value="<?php echo $_SESSION['user']['picture_iid'] ?>" />
                            <input type="hidden" id="current_email" value="<?php echo $_SESSION['user']['email']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>Joined: <?php echo date("F j, Y, g:i a", $_SESSION['user']['first_seen']); ?></td>
                    </tr>
                    <tr>
                        <td>Points: <?php echo $app->getUserPoints($_SESSION['user']); ?></td>
                    </tr>
                    <tr>
                        <td>Posts: <?php echo $app->getUserPosts($_SESSION['user']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <script>
            var profilePic = $('#profile_picture');
            var emailAddress = $('#email_address');

            profilePic.change(function() {
                $.ajax({
                    type: "GET",
                    url: "actions/set_profile_image.php",
                    data: {iid: $(this).val()},
                    dataType: 'json',
                    success: function(data) {
                        if (data.status === 1) {
                            profilePic.notify(data.error, 'error');
                            profilePic.val($('#current_profile_picture').val());
                        } else {
                            profilePic.notify("You've successfully changed your profile picture!", 'success');
                            $('#profile_picture_display').attr('src', data.direct);
                        }
                    }
                });
            });

            emailAddress.change(function() {
               $.ajax({
                  type: "GET",
                  url: "actions/set_email_address.php",
                  data: {email: $(this).val()},
                  dataType: 'json',
                  success: function(data) {
                      if (data.status === 1) {
                          emailAddress.notify(data.error, "error");
                      } else {
                          emailAddress.notify("You've submitted your new email! Check your inbox to confirm the change.", 'success');
                      }
                      emailAddress.val($('#current_email').val());
                  }
               });
            });
        </script>
    </body>
</html>
