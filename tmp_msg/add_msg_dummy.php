<?php
require('../hwe/lib.php');


$jsonPost = WebUtil::parseJsonPost();

Json::die([
    'result'=>true,
    'reason'=>'success',
    'msgID'=>1997
]);