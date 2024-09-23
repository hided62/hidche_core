<?php

namespace sammo\StaticEvent;

use sammo\ActionLogger;
use sammo\CityConst;
use sammo\DB;
use sammo\Enums\GeneralLiteQueryMode;
use sammo\General;
use sammo\GeneralLite;
use sammo\JosaUtil;

class event_부대탑승즉시이동 extends \sammo\BaseStaticEvent
{
    function run(GeneralLite|General $general, null|GeneralLite|General $destGeneral, array $env, array $params): bool | string
    {

        $troopID = $params['troopID'] ?? null;
        if ($troopID === null) {
            return "troopID is null";
        }
        if (!is_int($troopID)) {
            return "troopID is not int";
        }

        $destGeneral = GeneralLite::createObjFromDB($troopID, ['nation', 'city', 'troop'], GeneralLiteQueryMode::Core);
        if ($destGeneral === null) {
            return "destGeneral is null";
        }

        if($destGeneral->getID() !== $destGeneral->getVar('troop')){
            return "destGeneral is not troop";
        }

        if($destGeneral->getNationID() !== $general->getNationID()){
            return "destGeneral is not same nation";
        }

        if($destGeneral->getCityID() === $general->getCityID()){
            return true;
        }

        $cityName = CityConst::byID($destGeneral->getCityID())->name;
        $general->setVar('city', $destGeneral->getCityID());
        $josaRo = JosaUtil::pick($cityName, '로');
        $general->getLogger()->pushGeneralActionLog("부대 주둔지인 <G><b>{$cityName}</b></>{$josaRo} 즉시 이동합니다.", ActionLogger::PLAIN);
        $general->applyDB(DB::db());

        return true;
    }
}
