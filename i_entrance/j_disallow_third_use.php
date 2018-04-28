<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();

$db = RootDB::db();
$db->update('member', [
    'third_use'=>0
], 'no=%i', $userID);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);