<?php
namespace sammo\GeneralPool;

use sammo\AbsFromUserPool;
use sammo\GameConst;
use sammo\Json;
use sammo\Util;

class SPoolUnderU30 extends AbsFromUserPool{
    public static function getPoolName():string{return "체30기 이벤트";}
    public static function initPool(\MeekroDB $db){

        $jsonData=Json::decode(file_get_contents(__DIR__."/Pool/UnderS30.json"));
        $columns = $jsonData['columns'];
        $sqlValues = [];
        foreach($jsonData['data'] as $rawItem){
            if(count($rawItem) != count($columns)){
                throw new \RuntimeException($rawItem[0]."Error");
            }
            $item = array_combine($columns, $rawItem);
            $uniqueName = $item['generalName'];
            $item['uniqueName'] = $uniqueName;
            $sqlValues[] = [
                'unique_name'=>$uniqueName,
                'info'=>Json::encode($item)
            ];
        }
        $db->insert('select_pool', $sqlValues);
    }
}