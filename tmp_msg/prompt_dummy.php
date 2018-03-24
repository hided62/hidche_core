<?php
require('../twe/lib.php');


$jsonPost = parseJsonPost();

echo json_encode([
    'result'=>true,
    'reason'=>'success'
], JSON_UNESCAPED_UNICODE);