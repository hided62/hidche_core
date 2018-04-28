<?php
namespace sammo;

include 'lib.php';
include "func.php";

$db = DB::db();

$reserved = $db->queryFirstRow(
    'SELECT * FROM reserved_open WHERE `date` <= %s LIMIT 1',
    (new \DateTime())->format('Y-m-d H:i:s')
);

if(!$reserved){
    Json::die([
        'result'=>true,
        'affected'=>0
    ]);
}

$options = Json::decode($reserved['options']);

$result = ResetHelper::buildScenario(
    $options['turnterm'],
    $options['sync'],
    $options['scenario'],
    $options['fiction'],
    $options['extend'],
    $options['npcmode'],
    $options['show_img_level'],
    $options['tournament_trig']
);

$result['affected']=1;

$prefix = DB::prefix();
AppConf::getList()[$prefix]->openServer();

Json::die($result);