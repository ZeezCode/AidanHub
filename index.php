<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';
    require 'AppLang.php';

    if (isset($_SESSION['user'])) { //Already logged in
        header('Location: home.php');
        die(0);
    } elseif (isset($_GET['tlogin'])) { //Redirect for Twitch login
        $sendToURL = "https://api.twitch.tv/kraken/oauth2/authorize"
        ."?response_type=code"
        ."&client_id=" . AppConfig::getDataValue("api_key")
        ."&redirect_uri=" . AppConfig::getDataValue("redirect_uri")
        ."&scope=channel_read+user_read"
        ."&state=" . session_id();
        header('Location: ' . $sendToURL);
        die(0);
    } else if (isset($_GET['code']) && isset($_GET['scope']) && isset($_GET['state'])) { //Authorized login via Twitch
        $url = 'https://api.twitch.tv/kraken/oauth2/token';
        $data = array('client_id' => AppConfig::getDataValue("api_key"), 'client_secret' => AppConfig::getDataValue("secret"), 'grant_type' => 'authorization_code',
            'redirect_uri' => AppConfig::getDataValue("redirect_uri"), 'code' => $_GET['code'], 'state' => session_id());

        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context = stream_context_create($options);
        $result = json_decode(file_get_contents($url, false, $context));
        if ($result === false) {
            die("Failed to retrieve OAuth Token from Twitch API.");
        }

        $profileInfo = json_decode(file_get_contents("https://api.twitch.tv/kraken/user?oauth_token=" . $result->access_token));
        $app = new App(AppConfig::getDatabaseConnection());
        $user = $app->registerThroughTwitch($profileInfo);
        if ($user['account_source'] == "l") {
            die("There is already a locally registered account using this email address. Log in through that.");
        }
        $_SESSION['user'] = $user;
        header('Location: home.php');
    } else if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $pass = trim($_POST['password']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: index.php?e=1');
            die(0);
        }

        if (empty($email) || empty($pass)) {
            header('Location: index.php?e=2');
            die(0);
        }

        $app = new App(AppConfig::getDatabaseConnection());
        $user = $app->getUserFromEmail($email);

        if ($user == null) {
            header('Location: index.php?e=3');
            die(0);
        }

        $salted_pass = md5( md5($user['salt']) . md5($pass) );
        if ($salted_pass==$user['password']) {
            $_SESSION['user'] = $user;
            header('Location: home.php');
        } else {
            header('Location: index.php?e=3');
            die(0);
        }

    }
?>
<html>
    <head>
        <title>Log In to AidanHub</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="css/normalize.css" >
        <link rel="stylesheet" type="text/css" href="css/index.css?<?php echo date('l jS \of F Y h:i:s A'); ?>" >

        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
        <style>
            .login_button {
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <script>
            $(".login_button").click(function() {
                window.location.href = "index.php?" + $(this).attr('id');
            });
        </script>
        <div id="login_block">
            <h2 id="login_logo">AidanHub</h2>
            <span id="login_error"><?php if (isset($_GET['e'])) echo AppLang::getLoginErrorFromCode($_GET['e']); ?></span>
            <form id="login" action="index.php" method="post">
                <table id="login_table">
                    <tr>
                        <td colspan="2"><input type="text" id="email" name="email" required placeholder="Email" /></td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="password" id="password" name="password" required placeholder="Password" /></td>
                    </tr>
                    <tr>
                        <td><input type="submit" value="Log In" /></td>
                        <td><img class="login_button" id="tlogin" src="https://i.imgur.com/vL4sjXo.png" /></td>
                    </tr>
                    <tr>
                        <td colspan="2"><span id="login_bottom_links"><a href="#">Forgot Password</a> | <a href="register.php">Register</a></span></td>
                    </tr>
                </table>
            </form>
        </div>
        <script>
            $(".login_button").click(function() {
                window.location.href = "index.php?" + $(this).attr('id');
            });
        </script>
    </body>
</html>
