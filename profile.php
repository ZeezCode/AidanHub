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
            <span id="header_links"><a href="index.php">Home</a> | <a href="upload.php">Upload</a> | <a href="profile.php">My Profile</a> | <a href="reset.php">Log Out</a></span>
        </div>
        <div id="content">
            <div id="profile_block">
                <table id="profile_info">
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
                        <td><?php echo htmlspecialchars($_SESSION['user']['username']); ?></td>
                    </tr>
                    <tr>
                        <td>
                            <input type="email" id="email_address" name="email_address" placeholder="Email" value="<?php echo $_SESSION['user']['email']; ?>" />
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
            $('#profile_picture').change(function() {
                $.ajax({
                    type: "GET",
                    url: "actions/set_profile_image.php",
                    data: {iid: $(this).val()},
                    dataType: 'json',
                    success: function(data) {
                        if (data.status === 1) {
                            $('#profile_picture').notify(data.error, 'error');
                        } else {
                            $('#profile_picture').notify("You've successfully changed your profile picture!", 'success');
                            $('#profile_picture_display').attr('src', data.direct);
                        }
                    }
                });
            });

            $('#email_address').change(function() {
               $.ajax({
                  type: "GET",
                  url: "actions/set_email_address.php",
                  data: {email: $(this).val()},
                  dataType: 'json',
                  success: function(data) {
                      if (data.status === 1) {
                          $('#email_address').notify(data.error, "error");
                      } else {
                          $('#email_address').notify("You've submitted your new email! Check your inbox to confirm the change.", 'success');
                      }
                      $('#email_address').val($('#current_email').val());
                  }
               });
            });
        </script>
    </body>
</html>
