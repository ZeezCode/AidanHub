<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';
    require 'AppLang.php';

    if (isset($_SESSION['user'])) { //Already logged in
        header('Location: home.php');
        die(0);
    }

    if (isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password2'])) {
        $email = trim($_POST['email']);
        $user = trim($_POST['username']);
        $pass = trim($_POST['password']);
        $pass2 = trim($_POST['password2']);
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            header('Location: register.php?e=1');
            die(0);
        }

        if (empty($user) || empty($pass) || empty($pass2)) {
            header('Location: register.php?e=2');
            die(0);
        }

        if ($pass != $pass2) {
            header('Location: register.php?e=3');
            die(0);
        }

        $app = new App(AppConfig::getDatabaseConnection());
        if ($app->getUserFromEmail($email) != null) {
            header('Location: register.php?e=4');
            die(0);
        }

        $_SESSION['user'] = $app->registerThroughLocalSite($email, $user, $pass);
        header('Location: home.php');
    }
?>

<html>
    <head>
        <title>Register to AidanHub</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    </head>
    <body>
        <p><?php if (isset($_GET['e'])) echo AppLang::getLoginErrorFromCode($_GET['e']); ?></p>
        <form id="register" action="register.php" method="post">
                <table id="register_table">
                <tr>
                    <td><label for="email">Email</label></td>
                    <td><input type="text" id="email" name="email" /></td>
                </tr>
                <tr>
                    <td><label for="username">Username</label></td>
                    <td><input type="text" id="username" name="username" /></td>
                </tr>
                <tr>
                    <td><label for="password">Password</label></td>
                    <td><input type="password" id="password" name="password" /></td>
                </tr>
                <tr>
                    <td><label for="password2">Password (again)</label></td>
                    <td><input type="password" id="password2" name="password2" /></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Register" /></td>
                </tr>
            </table>
        </form>
    </body>
</html>
