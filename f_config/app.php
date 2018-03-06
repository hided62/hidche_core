<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._Setting.php');

define('POPUP', 'popup');

define('LOGIN', 'login');
define('JOIN', 'join');
define('FINDPW', 'findpw');

define('ENTRANCE', 'entrance');
define('SERVERLIST', 'serverList');
define('LOGOUT', 'logout');
define('ADMIN', 'admin');
define('MEMBER', 'member');

define('MANAGE', 'manage');
define('ICON', 'icon');

$serverList = [
    'che'=>['체', 'white', new _Setting(__DIR__.ROOT.W.'che')],
    'kwe'=>['퀘', 'yellow', new _Setting(__DIR__.ROOW.W.'kwe')],
    'pwe'=>['풰', 'orange', new _Setting(__DIR__.ROOW.W.'pwe')],
    'twe'=>['퉤', 'magenta', new _Setting(__DIR__.ROOW.W.'twe')],
    'hwe'=>['훼', 'red', new _Setting(__DIR__.ROOW.W.'hwe')]
];
