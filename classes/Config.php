<?php

namespace PhotosLeaves;

class Config {

    public static function get($name) {
        $env_value = getenv($name);
        if ($env_value !== FALSE) {
            return $env_value;
        }
        global $CONFIG;
        if (empty($CONFIG)) {
            require_once('config.php');
        }
        if (isset($CONFIG->{$name})) {
            return $CONFIG->{$name};
        }
        return FALSE;
    }

}

date_default_timezone_set('UTC');
