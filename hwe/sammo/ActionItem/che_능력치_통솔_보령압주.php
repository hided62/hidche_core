<?php
namespace sammo\ActionItem;

use sammo\DB;
use sammo\GameConst;
use \sammo\iAction;
use \sammo\General;
use sammo\KVStorage;
use sammo\Util;

class che_능력치_통솔_보령압주 extends \sammo\BaseItem{

    protected $rawName = '보령압주';
    protected $name = '보령압주(통솔)';
    protected $info = '[능력치] 통솔 +5 +(4년마다 +1)';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'leadership'){
            $gameStor = KVStorage::getStorage(DB::db(), 'game_env');
            [$year, $startYear] = $gameStor->getValuesAsArray(['year', 'startyear']);
            $relYear = $year - $startYear;
            return $value + 5 + Util::valueFit(intdiv($relYear, 4), 0, GameConst::$maxTechLevel);
        }
        return $value;
    }
}
