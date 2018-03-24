<?php
include "lib.php";
include "func.php";

if(Session::getUserGrade(true) < 5){
    die('관리자 아님');
}

?>
<!DOCTYPE html>
<html>
<head>
<title>설치</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script src="../e_lib/jquery-3.2.1.min.js"></script>
<script src="../e_lib/bootstrap.bundle.min.js"></script>
<script src="../e_lib/jquery.validate.min.js"></script>
<script src="js/common.js"></script>
<script src="js/base_map.js"></script>
<script src="js/map.js"></script>
<script src="js/install.js"></script>
<link href="css/normalize.css" rel="stylesheet">
<link type="text/css" rel="stylesheet" href="../e_lib/bootstrap.min.css">
<link href="css/map.css" rel="stylesheet">
<link href="css/install.css" rel="stylesheet">
</head>
<body>
<div id="scenario_map" style="width:698px;height:520px;">
<?=getMapHtml()?>
</div>
</body>
</html>