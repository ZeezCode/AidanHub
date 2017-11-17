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
                <input type="hidden" id="post_id" value="<?php echo $img->getIID(); ?>" />
                <table id="post_table">
                    <tr>
                        <td><span id="post_title"><?php echo $img->getTitle(); ?></span> <span id="post_author">by <?php echo $app->getUserFromId($img->getUID())['username']; ?></span></td>
                    </tr>
                    <tr>
                        <td align="center">
                            <div id="image_container">
                                <a href="<?php echo $img->getDirectURL(); ?>"><img src="<?php echo $img->getDirectURL(); ?>" /></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <?php
                            $vote = $app->getUserVoteOnImage($img->getIID());
                            $upStyle = "border-bottom: 16px solid #000000";
                            $downStyle = "border-top: 16px solid #000000";
                            if ($vote == 1)
                                $upStyle = "border-bottom: 16px solid #00C06A";
                            elseif ($vote == -1) {
                                $downStyle = "border-top: 16px solid #DB3535";
                            }
                        ?>
                        <td>
                            <span id="vote_counter_text">Points: <span id="vote_count"><?php echo $img->getUpvotes() - $img->getDownvotes(); ?></span></span>
                            <div id="arrow_container"><div class="arrow" id="arrow_up" style="<?php echo $upStyle; ?>"></div><div class="arrow" id="arrow_down" style="<?php echo $downStyle; ?>"></div></div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <textarea id="post_description" readonly rows="<?php echo (substr_count($img->getDescription(), "\n") + 1); ?>" ><?php echo htmlentities($img->getDescription()); ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <script>
            $('.arrow').click(function() {
                var voteType = 1;
                if ($(this).attr('id') === 'arrow_down') {
                    voteType = -1;
                }

                $.ajax({
                    type: 'GET',
                    url: '../actions/process_vote.php',
                    data: {iid:$('#post_id').val(), vote:voteType},
                    dataType: 'json',
                    success: function(data) {
                        if (data.status === 1)
                            $.notify(data.error, 'error');
                        else {
                            var upArrow = $('#arrow_up');
                            var downArrow = $('#arrow_down');
                            var type = parseInt(data.type);
                            var voteCount = $('#vote_count');

                            if (type === 1) {
                                upArrow.css("border-bottom", "16px solid #00C06A");
                                downArrow.css("border-top", "16px solid #000000");
                                if (data.old === 0)
                                    voteCount.text(parseInt(voteCount.text()) + 1);
                                else
                                    voteCount.text(parseInt(voteCount.text()) + 2);
                            } else if (type === -1) {
                                upArrow.css("border-bottom", "16px solid #000000");
                                downArrow.css("border-top", "16px solid #DB3535");
                                if (data.old === 0)
                                    voteCount.text(parseInt(voteCount.text()) - 1);
                                else
                                    voteCount.text(parseInt(voteCount.text()) - 2);
                            } else {
                                upArrow.css("border-bottom", "16px solid #000000");
                                downArrow.css("border-top", "16px solid #000000");
                                if (parseInt(data.old) === -1)
                                    voteCount.text(parseInt(voteCount.text()) + 1);
                                else
                                    voteCount.text(parseInt(voteCount.text()) - 1);
                            }
                        }
                    }
                });
            });
        </script>
    </body>
</html>
