<?php

namespace sammo;

require(__DIR__ . '/vendor/autoload.php');


if (!class_exists('\\sammo\\RootDB')) {
    Json::dieWithReason('No DB');
}

APIHelper::launch(dirname(__FILE__));