<?php
require('../twe/lib.php');
use utilphp\util as util;

$jsonPost = parseJsonPost();

echo json_encode([
    'result'=>true,
    'reason'=>'success'
], JSON_UNESCAPED_UNICODE);