<?php
require('../twe/lib.php');


$jsonPost = parseJsonPost();

echo json_encode([
    'result'=>true,
    'reason'=>'success',
    'msgID'=>1997
], JSON_UNESCAPED_UNICODE);