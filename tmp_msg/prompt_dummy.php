<?php
require('../twe/lib.php');


$jsonPost = WebUtil::parseJsonPost();

Json::die([
    'result'=>true,
    'reason'=>'success'
]);