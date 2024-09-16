<?php

namespace sammo\StaticEvent;

use sammo\DB;
use sammo\Enums\GeneralLiteQueryMode;
use sammo\General;
use sammo\GeneralLite;

class event_부대탑승즉시이동 extends \sammo\BaseStaticEvent
{
    function run(General $general, array $env, array $params): bool | string
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

        $general->setVar('city', $destGeneral->getCityID());
        $general->applyDB(DB::db());

        return true;
    }
}
