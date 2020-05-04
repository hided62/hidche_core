<?php

namespace sammo;

require(__DIR__ . '/vendor/autoload.php');

$session = Session::requireLogin('./')->setReadOnly();
if (key_exists('from', $_REQUEST) && is_numeric($_REQUEST['from'])) {
    $from = (int) $_REQUEST['from'];
    if ($from < 0) {
        $from = 0;
    }
} else {
    $from = 0;
}

$allowUpdate = false;

foreach ($session->acl as $eachAcl) {
    if (in_array('fullUpdate', $eachAcl)) {
        $allowUpdate = true;
        break;
    }
    if (in_array('update', $eachAcl)) {
        $allowUpdate = true;
        break;
    }
}
$allowUpdate |= $session->userGrade >= 5;

if (!$allowUpdate) {
    header('Location:./');
}

$fdb = FileDB::db(ROOT . '/d_log/err_log.sqlite3', ROOT . '/f_install/sql/err_log.sql');

$err_logs = $fdb->select('err_log', [
    'date',
    'err',
    'errstr',
    'errpath',
    'trace',
    'webuser'
], [
    'ORDER' => ['id' => 'DESC'],
    'LIMIT' => [$from, 100]
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
    <?= WebUtil::printCSS('e_lib/bootstrap.min.css') ?>
    <?= WebUtil::printCSS('d_shared/common.css') ?>

    <?= WebUtil::printJS('e_lib/jquery-3.3.1.min.js') ?>
    <?= WebUtil::printJS('d_shared/common_path.js') ?>
    <?= WebUtil::printJS('e_lib/bootstrap.bundle.min.js') ?>
</head>

<body>
    <div class="container">
        <?php foreach ($err_logs as $err) : ?>
            <div class="card">
                <div class="card-header">
                    <?= htmlspecialchars($err['err']) ?> - <?= $err['date'] ?>
                </div>
                <div class="card-body">
                    <div class="card-title"><?= htmlspecialchars($err['errstr']) ?></div>
                    <ul class="list-group list-group-flush">
                        <?php if ($err['errpath']) : ?>
                            <li class="list-group-item"><?= htmlspecialchars($err['errpath']) ?></li>
                        <?php endif; ?>
                        <?php foreach (Json::decode($err['trace']) as $trace) : ?>
                            <li class="list-group-item"><?= htmlspecialchars($trace) ?></li>
                        <?php endforeach; ?>
                        <li class="list-group-item"><?= htmlspecialchars($err['webuser']) ?></li>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
        <a href="showErrorLog.php?from=<?= $from + 100 ?>" class="btn btn-primary btn-lg active" role="button">+100</a>
    </div>
</body>

</html>