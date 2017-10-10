<?php
    session_start();
    require 'AppConfig.php';
    require 'App.php';

    if (isset($_SESSION['user'])) { //Already logged in
        header('Location: home.php');
        die(0);
    }
?>

<html>
    <head>
        <title>Register to AidanHub</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    </head>
    <body>
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
