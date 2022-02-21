<?php
namespace sammo\ActionItem;

use sammo\DB;
use sammo\GameConst;
use \sammo\iAction;
use \sammo\General;
use sammo\KVStorage;
use sammo\Util;

class che_능력치_무력_두강주 extends \sammo\BaseItem{

    protected $rawName = '두강주';
    protected $name = '두강주(무력)';
    protected $info = '[능력치] 무력 +5 +(5년마다 +1)';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'strength'){
            $gameStor = KVStorage::getStorage(DB::db(), 'game_env');
            [$year, $startYear] = $gameStor->getValuesAsArray(['year', 'startyear']);
            $relYear = $year - $startYear;
            return $value + 5 + Util::valueFit(intdiv($relYear, 5), 0, GameConst::$maxTechLevel);
        }
        return $value;
    }
}
