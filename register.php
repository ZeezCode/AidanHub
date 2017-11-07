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
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>
        <script src="js/Utilities.js?<?php echo time(); ?>"></script>

        <link rel="stylesheet" type="text/css" href="css/normalize.css" >
        <link rel="stylesheet" type="text/css" href="css/index.css?<?php echo time(); ?>" >
        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    </head>
    <body>
        <div id="login_block">
            <h2 id="login_logo">Register to AidanHub</h2>
            <span id="login_error"><?php if (isset($_GET['e'])) echo AppLang::getRegisterErrorFromCode($_GET['e']); ?></span>
            <form id="login" action="index.php" method="post">
                <table id="login_table">
                    <tr>
                        <td><input type="text" id="email" name="email" required placeholder="Email" /></td>
                    </tr>
                    <tr>
                        <td><input type="text" id="username" name="username" required placeholder="Username" /></td>
                    </tr>
                    <tr>
                        <td><input type="password" id="password" name="password" required placeholder="Password" /></td>
                    </tr>
                    <tr>
                        <td><input type="password" id="password2" name="password2" required placeholder="Password (again)" /></td>
                    </tr>
                    <tr>
                        <td><input type="submit" value="Register" /></td>
                    </tr>
                    <tr>
                        <td><span id="login_bottom_links"><a href="index.php">Log In to Existing Account</a></span></td>
                    </tr>
                </table>
            </form>
        </div>
        <script>
            var pass2 = $('#password2');
            var email = $('#email');

            //On character type, check if passwords match
            $('#password2, #password').on('input', function() {
                if (pass2.val() !== $('#password').val()) {
                    pass2.css('border-bottom', '2px solid #ff0000');
                } else {
                    pass2.css("border-bottom", "2px solid #6C3295");
                }
            });

            //on element oufocused after change, check if passwords match
            pass2.change(function () {
                if ($(this).val() !== $('#password').val()) {
                    $(this).notify("Passwords do not match!", "error");
                }
            });

            //On character type, check if valid email
            email.on('input', function() {
                if (!isValidEmailAddress(email.val())) {
                    email.css('border-bottom', '2px solid #ff0000');
                } else {
                    email.css("border-bottom", "2px solid #6C3295");
                }
            });

            //On element unfocused after change, check if valid email
            email.change(function () {
                var input = email.val();
                if (!isValidEmailAddress(input))
                    email.notify("Invalid email address", "error");
                else
                    isEmailTaken(input);
            });

            //Notifies you whether the inputted email is taken or not
            function isEmailTaken(input) {
                $.ajax({
                    type: "GET",
                    url: "actions/check_email_taken.php",
                    data: {email:input},
                    dataType: 'text',
                    success: function(data) {
                        if (data==="0")
                            email.notify("This email is available!", "success");
                        else
                            email.notify("This email is already being used!", "error");
                    }
                });
            }
        </script>
    </body>
</html>
