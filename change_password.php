<?php
    session_start();
    require 'AppConfig.php';

    if (isset($_SESSION['user'])) { //Already logged in
        header('Location: home.php');
        die(0);
    }

    if (!isset($_GET['token'])) {
        echo "No token supplied!";
        die(0);
    }

    $db = AppConfig::getDatabaseConnection();

    $getTokenSQL = sprintf("SELECT uid FROM password_confirmation WHERE token = '%s';",
        mysqli_real_escape_string($db, $_GET['token']));
    $getTokenQuery = mysqli_query($db, $getTokenSQL);
    if (mysqli_num_rows($getTokenQuery) == 0) {
        echo "Invalid token";
        die(0);
    }
    $uid = mysqli_fetch_assoc($getTokenQuery)['uid'];

?>
<html>
    <head>
        <title>AidanHub - Change Password</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="css/normalize.css" >
        <link rel="stylesheet" type="text/css" href="css/forgot.css?<?php echo time(); ?>" >

        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <div id="forgot_block">
            <h2 id="logo">Change Password</h2>
            <form id="forgot" action="actions/set_password.php" method="post">
                <table id="forgot_table">
                    <tr>
                        <td><input type="password" name="pass1" placeholder="Password" required /></td>
                    </tr>
                    <tr>
                        <td><input type="password" name="pass2" placeholder="Password (again)" required /></td>
                    </tr>
                    <tr>
                        <td>
                            <input type="hidden" name="token" value="<?php echo htmlentities($_GET['token']); ?>" />
                            <input type="submit" value="Change Password" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>
