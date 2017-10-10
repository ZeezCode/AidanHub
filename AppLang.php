<?php
    class AppLang {

        static function getLoginErrorFromCode($code) {
            switch($code) {
                case 1:
                    return "You entered an invalid email!";
                case 2:
                    return "One or more fields were left empty!";
                case 3:
                    return "Your passwords did not match!";
                case 4:
                    return "A user already exists with that email!";
                default:
                    return "Unrecognized error.";
            }
        }

    }
