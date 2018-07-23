<?php
namespace sammo;

require(__dir__.'/vendor/autoload.php');

$session = Session::requireLogin('./')->setReadOnly();

$allowUpdate = false;

foreach($session->acl as $eachAcl){
    if(in_array('fullUpdate', $eachAcl)){
        $allowUpdate = true;
        break;
    }
    if(in_array('update', $eachAcl)){
        $allowUpdate = true;
        break;
    }
}
$allowUpdate |= $session->userGrade >= 5;

if(!$allowUpdate){
    header('Location:./');
}

$fdb = FileDB::db(ROOT.'/d_log/err_log.sqlite3', ROOT.'/f_install/sql/err_log.sql');

$err_logs = $fdb->select('err_log', [
    'date',
    'err',
    'errstr',
    'trace'
], [
    'ORDER'=>['id'=>'DESC'],
    'LIMIT'=>100
]);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=1024" />
        <title>에러 로그</title>

        <!-- 스타일 -->
        <?=WebUtil::printCSS('e_lib/bootstrap.min.css')?>
        <?=WebUtil::printCSS('d_shared/common.css')?>

        <?=WebUtil::printJS('e_lib/jquery-3.3.1.min.js')?>
        <?=WebUtil::printJS('d_shared/common_path.js')?>
        <?=WebUtil::printJS('e_lib/bootstrap.bundle.min.js')?>
</head>
<body>
<div class="container">
<?php foreach($err_logs as $err): ?>
    <div class="card">
        <div class="card-header">
            <?=htmlspecialchars($err['err'])?> - <?=$err['date']?>
        </div>
        <div class="card-body">
            <div class="card-title"><?=htmlspecialchars($err['errstr'])?></div>
            <ul class="list-group list-group-flush">
                <?php foreach(Json::decode($err['trace']) as $trace): ?>
                <li class="list-group-item"><?=htmlspecialchars($trace)?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endforeach; ?>
</div>
</body>
</html>