<?php
namespace sammo;
require('vendor/autoload.php');

Json::die([
    'haha',
    RootDB::getServerBasepath(),
    'RootDB'=>class_exists('\\sammo\\RootDB')
]);