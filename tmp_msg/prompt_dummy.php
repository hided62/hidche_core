<?php
require('../twe/lib.php');


$jsonPost = WebUtil::parseJsonPost();

echo json_encode([
    'result'=>true,
    'reason'=>'success'
], JSON_UNESCAPED_UNICODE);