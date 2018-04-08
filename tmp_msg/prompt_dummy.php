<?php
namespace sammo;

require('../hwe/lib.php');


$jsonPost = WebUtil::parseJsonPost();

Json::die([
    'result'=>true,
    'reason'=>'success'
]);