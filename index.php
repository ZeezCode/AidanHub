<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';

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
    }
?>
<html>
    <head>
        <title>Login to AidanHub</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <style>
            .login_button {
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <img class="login_button" id="tlogin" src="https://i.imgur.com/vL4sjXo.png" />
        <a href="register.php">Register</a>

        <script>
            $(".login_button").click(function() {
                window.location.href = "index.php?" + $(this).attr('id');
            });
        </script>
    </body>
</html>
