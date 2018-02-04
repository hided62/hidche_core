<?php

define('POPUP', 'popup');

define('LOGIN', 'login');
define('JOIN', 'join');
define('FINDPW', 'findpw');

define('ENTRANCE', 'entrance');
define('SERVERLIST', 'serverList');
define('LOGOUT', 'logout');
define('ADMIN', 'admin');
define('DONATION', 'donation');
define('MEMBER', 'member');

define('MANAGE', 'manage');
define('ICON', 'icon');

$_serverDirs = array(
    'che',
    'kwe',
    'pwe',
    'twe',
    'hwe'
);

$_serverCount = count($_serverDirs);

$_serverNames = array(
    //FIXME: color빼곤 css로 옮겨야..
    '<span style="font-weight:bold;font-size:1.4em;color:white">체섭</span>',
    '<span style="font-weight:bold;font-size:1.4em;color:yellow">퀘섭</span>',
    '<span style="font-weight:bold;font-size:1.4em;color:orange">풰섭</span>',
    '<span style="font-weight:bold;font-size:1.4em;color:magenta">퉤섭</span>',
    '<span style="font-weight:bold;font-size:1.4em;color:red">훼섭</span>'
);

$_serverLevels = array(
    1,
    1,
    1,
    1,
    1
);


