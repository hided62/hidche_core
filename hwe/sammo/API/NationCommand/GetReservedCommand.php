<?php

namespace sammo\API\NationCommand;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\GeneralQueryMode;
use sammo\GameConst;
use sammo\General;
use sammo\Json;
use sammo\KVStorage;
use sammo\TimeUtil;

use function sammo\checkLimit;
use function sammo\checkSecretPermission;
use function sammo\getChiefCommandTable;
use function sammo\getOfficerLevelText;
use function sammo\increaseRefresh;

class GetReservedCommand extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $db = DB::db();

        increaseRefresh("사령부", 1);

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $userID = $session->userID;

        $me = $db->queryFirstRow(
            'SELECT no,nation,officer_level,refresh_score,turntime,belong,penalty,permission FROM `general`
            LEFT JOIN general_access_log AS l ON `general`.no = l.general_id WHERE owner=%i', $userID
        );

        $nationLevel = $db->queryFirstField('SELECT level FROM nation WHERE nation = %i', $me['nation']);
        $nationID = $me['nation'];
        $limitState = checkLimit($me['refresh_score']);
        if ($limitState >= 2) {
            return "접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다. (다음 갱신 가능 시각 : {$me['turntime']})";
        }

        $permission = checkSecretPermission($me);
        if ($permission < 0) {
            return '국가에 소속되어있지 않습니다.';
        } else if ($permission < 1) {
            return '수뇌부가 아니거나 사관년도가 부족합니다.';
        }

        [$turnTerm, $year, $month, $lastExecute] = $gameStor->getValuesAsArray(['turnterm', 'year', 'month', 'turntime']);

        $generals = [];
        foreach ($db->query('SELECT no,name,turntime,npc,city,nation,officer_level FROM general WHERE nation = %i AND officer_level >= 5', $nationID) as $rawGeneral) {
            $generals[$rawGeneral['officer_level']] = new General($rawGeneral, null, null, null, null, $year, $month, false);
        }

        $nationTurnList = [];
        foreach ($db->queryAllLists(
            'SELECT officer_level, turn_idx, action, arg, brief FROM nation_turn WHERE nation_id = %i ORDER BY officer_level DESC, turn_idx ASC',
            $me['nation']
        ) as [$officer_level, $turn_idx, $action, $arg, $brief]) {
            if (!key_exists($officer_level, $nationTurnList)) {
                $nationTurnList[$officer_level] = [];
            }
            $nationTurnList[$officer_level][$turn_idx] = [
                'action' => $action,
                'brief' => $brief,
                'arg' => Json::decode($arg)
            ];
        }

        $troopList = [];
        foreach ($db->queryAllLists(
            'SELECT troop_leader, `name` FROM troop WHERE `nation` = %i',
            $nationID
        ) as [$troopID, $troopName]) {
            $troopList[$troopID] = $troopName;
        }


        $nationChiefList = [];
        foreach ($nationTurnList as $officer_level => $turnBrief) {
            if (!key_exists($officer_level, $generals)) {
                $nationChiefList[$officer_level] = [
                    'name' => null,
                    'turnTime' => null,
                    'officerLevelText' => getOfficerLevelText($officer_level, $nationLevel),
                    'npcType' => null,
                    'turn' => $turnBrief
                ];
                continue;
            }
            $general = $generals[$officer_level];
            $nationChiefList[$officer_level] = [
                'name' => $general->getName(),
                'turnTime' => $general->getTurnTime($general::TURNTIME_FULL),
                'officerLevel' => $general->getVar('officer_level'),
                'officerLevelText' => getOfficerLevelText($general->getVar('officer_level'), $nationLevel),
                'npcType' => $general->getNPCType(),
                'turn' => $turnBrief,
            ];
        }

        $generalObj = General::createObjFromDB($session->generalID);


        return [
            'result' => true,
            'lastExecute' => $lastExecute,
            'year' => $year,
            'month' => $month,
            'turnTerm' => $turnTerm,
            'date' => TimeUtil::now(true),
            'chiefList' => $nationChiefList,
            'troopList' => $troopList,
            'isChief' => ($me['officer_level'] > 4),
            'autorun_limit' => $generalObj->getAuxVar('autorun_limit'),
            'officerLevel' => $me['officer_level'],
            'commandList' => getChiefCommandTable($generalObj),
            'mapName' => GameConst::$mapName,
            'unitSet' => GameConst::$unitSet,
        ];
    }
}
