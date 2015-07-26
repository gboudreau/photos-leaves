<?php

$CONFIG = new \stdClass();

$CONFIG->DB_ENGINE = 'mysql';
$CONFIG->DB_HOST   = '127.0.0.1';
$CONFIG->DB_PORT   = 3306;
$CONFIG->DB_NAME   = 'photos_leaves';
$CONFIG->DB_USER   = 'pl_user';
$CONFIG->DB_PWD    = 'skjhdghsfgkjdfgh';

$CONFIG->PHOTOS_DIR  = '/mnt/samba/Photos/Photos';
$CONFIG->ALBUMS_DIR  = '/mnt/samba/Photos/Albums';

// Who will own photos that will be imported from the disk (instead of being uploaded); specify an email address
$CONFIG->DEFAULT_OWNER = 'you@gmail.com';
