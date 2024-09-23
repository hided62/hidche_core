<?php

namespace sammo\StaticEvent;

use sammo\ActionLogger;
use sammo\CityConst;
use sammo\DB;
use sammo\Enums\GeneralLiteQueryMode;
use sammo\General;
use sammo\GeneralLite;
use sammo\JosaUtil;

class event_부대발령즉시집합 extends \sammo\BaseStaticEvent
{
    function run(GeneralLite|General $general, null|GeneralLite|General $destGeneral, array $env, array $params): bool | string
    {
        if($destGeneral === null){
            return "destGeneral is null";
        }

        $destCityID = $params['destCityID'] ?? null;
        if ($destCityID === null) {
            return "destCityID is null";
        }
        if (!is_int($destCityID)) {
            return "destCityID is not int";
        }

        if($destGeneral->getID() !== $destGeneral->getVar('troop')){
            //부대장 발령이 아니므로 무시
            return true;
        }

        if($destGeneral->getNationID() !== $general->getNationID()){
            return "destGeneral is not same nation";
        }

        $db = DB::db();
        $cityName = CityConst::byID($destCityID)->name;
        $josaRo = JosaUtil::pick($cityName, '로');

        $troopID = $destGeneral->getID();
        $troopName = $db->queryFirstField('SELECT name FROM troop WHERE troop_leader = %i', $troopID);

        $generalList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND city!=%i AND troop=%i AND no!=%i', $destGeneral->getNationID(), $destCityID, $troopID, $destGeneral->getID());
        if($generalList){
            $db->update('general', [
                'city'=>$destCityID
            ], 'no IN %li', $generalList);
        }
        if($general->getVar('troop') === $troopID){
            $general->setVar('city', $destCityID);
        }

        foreach($generalList as $targetGeneralID){
            $targetLogger = new ActionLogger($targetGeneralID, $general->getNationID(), $env['year'], $env['month']);
            $targetLogger->pushGeneralActionLog("{$troopName} 부대원들은 <G><b>{$cityName}</b></>{$josaRo} 즉시 집합되었습니다.", ActionLogger::PLAIN);
            $targetLogger->flush();
        }

        return true;
    }
}
