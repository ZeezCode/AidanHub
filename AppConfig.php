<?php

    class AppConfig {

        static protected $cfgFile;

        private static function setupIfNull() {
            if (self::$cfgFile == null) {
                self::$cfgFile = parse_ini_file("/var/www/cfg/config.ini");
            }
        }

        private static function getHost() {
            self::setupIfNull();

            return self::$cfgFile['database']['host'];
        }

        private static function getUsername() {
            self::setupIfNull();

            return self::$cfgFile['database']['username'];
        }

        private static function getPassword() {
            self::setupIfNull();

            return self::$cfgFile['database']['password'];
        }

        private static function getName() {
            self::setupIfNull();

            return self::$cfgFile['database']['name'];
        }

        public static function getDatabaseConnection() {
            self::setupIfNull();

            return mysqli_connect(
                self::getHost(),
                self::getUsername(),
                self::getPassword(),
                self::getName()
            );
        }

        public static function getDataValue($value) {
            self::setupIfNull();

            return self::$cfgFile['twitch'][$value];
        }

    }
