<?php

namespace sammo\API\Nation;

use sammo\DB;
use sammo\General;
use sammo\Session;
use sammo\Util;

use function sammo\calcLeadershipBonus;
use function sammo\checkLimit;
use function sammo\checkSecretPermission;
use function sammo\getBillByLevel;
use function sammo\getDedLevelText;
use function sammo\getHonor;
use function sammo\getNationStaticInfo;
use function sammo\getOfficerLevelText;
use function sammo\increaseRefresh;

class GeneralList extends \sammo\BaseAPI
{
    private int $permission;

    static $viewColumns = [
        'no' => 0,
        'name' => 0,
        'nation' => 0,
        'npc' => 0,
        'injury' => 0,
        'leadership' => 0,
        'strength' => 0,
        'intel' => 0,
        'explevel' => 0,
        'dedlevel' => 0,
        'gold' => 0,
        'rice' => 0,
        'killturn' => 0,
        'picture' => 0,
        'imgsvr' => 0,
        'age' => 0,
        'special' => 0,
        'special2' => 0,
        'personal' => 0,
        'belong' => 0,
        'connect' => 0,

        'con' => 1,
        'specage' => 1,
        'specage2' => 1,
        'leadership_exp' => 1,
        'strength_exp' => 1,
        'intel_exp' => 1,
        'dex1' => 1,
        'dex2' => 1,
        'dex3' => 1,
        'dex4' => 1,
        'dex5' => 1,

        'city' => 1,
        'experience' => 1,
        'dedication' => 1,

        'officer_level' => 1,
        'officer_city' => 1,
        'defence_train' => 1,
        'troop' => 1,
        'crewtype' => 1,
        'crew' => 1,
        'train' => 1,
        'atmos' => 1,
        'turntime' => 1,
        'horse' => 1,
        'weapon' => 1,
        'book' => 1,
        'item' => 1,
        'recent_war' => 1,

        'aux' => 1,


        'owner_name' => 9, //안씀.

        //RANK
        'warnum' => 1,
        'killnum' => 1,
        'deathnum' => 1,
        'killcrew' => 1,
        'deathcrew' => 1,
        'firenum' => 1,
    ];

    static $columnRemap = [
        'special' => 'specialDomestic',
        'special2' => 'specialWar',
        'aux' => null,
    ];

    static $customViewColumns = [
        'officerLevel' => 0,
        'officerLevelText' => 0,
        'lbonus' => 0,
        'ownerName' => 0,
        'honorText' => 0,
        'dedLevelText' => 0,
        'bill' => 0,
        'reservedCommand' => 1,

        'autorun_limit' => 1,
    ];

    public function validateArgs(): ?string
    {
        //TODO: 장기적으로는 요청에 따라 반환해야...
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    private function getOfficerLevel($rawGeneral)
    {
        $level = $rawGeneral['officer_level'];
        if ($level >= 5) {
            return $level;
        }
        if ($this->permission > 1) {
            return $level;
        }
        return 1;
    }

    public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        increaseRefresh("세력장수", 1);

        $db = DB::db();

        $gameStor = \sammo\KVStorage::getStorage($db, 'game_env');
        $env = $gameStor->getValues(['year', 'month', 'turntime', 'turnterm', 'autorun_user', 'killturn']);

        $me = $db->queryFirstRow('SELECT con, turntime, belong, nation, officer_level, permission, penalty FROM general WHERE owner=%i', $session->getUserID());
        $con = checkLimit($me['con']);
        if ($con >= 2) {
            return '접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다.';
        }

        $nationID = $me['nation'];
        $this->permission = checkSecretPermission($me, true);

        $nationArr = getNationStaticInfo($nationID);



        [$queryColumns, $rankColumns] = General::mergeQueryColumn(array_keys(static::$viewColumns), 1);

        $rawGeneralList = Util::convertArrayToDict($db->query('SELECT %l from general WHERE nation = %i ORDER BY turntime ASC', Util::formatListOfBackticks($queryColumns), $nationID), 'no');

        $reservedCommand = [];
        if ($this->permission >= 1) {
            $nonNPCGeneralIDList = [];
            foreach ($rawGeneralList as $rawGeneral) {
                if ($rawGeneral['npc'] < 2) {
                    $nonNPCGeneralIDList[] = $rawGeneral['no'];
                }
            }

            $rawTurnList = $db->query(
                'SELECT general_id, turn_idx, action, arg, brief FROM general_turn WHERE general_id IN %li AND turn_idx < 5 ORDER BY general_id asc, turn_idx asc',
                $nonNPCGeneralIDList
            );

            foreach ($rawTurnList as $rawTurn) {
                [
                    'general_id' => $generalID,
                    'action' => $action,
                    'arg' => $arg,
                    'brief' => $brief,
                ] = $rawTurn;
                if (!key_exists($generalID, $reservedCommand)) {
                    $reservedCommand[$generalID] = [];
                }
                $reservedCommand[$generalID][] = [
                    'action' => $action,
                    'arg' => $arg,
                    'brief' => $brief
                ];
            }
        }

        $rankList = [];
        if ($rankColumns) {
            $rankColumns = Util::valuesFromEnumArray($rankColumns);
            $rawRankList = $db->query('SELECT general_id, `type`, `value` FROM rank_data WHERE nation_id = %i AND `type` IN %ls', $nationID, $rankColumns);
            foreach ($rawRankList as $rawRank) {
                [
                    'general_id' => $generalID,
                    'type' => $type,
                    'value' => $value
                ] = $rawRank;

                if (!key_exists($generalID, $rankList)) {
                    $rankList[$generalID] = [];
                }
                $rankList[$generalID][$type] = $value;
            }
        }

        $getRankVar = fn ($key) => (fn ($rawGeneral) => (($rankList[$rawGeneral['no']] ?? [])[$key] ?? 0));

        $specialViewFilter = [
            'officerLevel' => fn ($rawGeneral) => $this->getOfficerLevel($rawGeneral),
            'officerLevelText' => fn ($rawGeneral) => getOfficerLevelText($this->getOfficerLevel($rawGeneral), $nationArr['level']),
            'lbonus' => fn ($rawGeneral) => calcLeadershipBonus($rawGeneral['officer_level'], $nationArr['level']),
            'ownerName' => fn ($rawGeneral) => ($rawGeneral['npc'] != 1) ? null : $rawGeneral['owner_name'],
            'honorText' => fn ($rawGeneral) => getHonor($rawGeneral['experience']),
            'dedLevelText' => fn ($rawGeneral) => getDedLevelText($rawGeneral['dedlevel']),
            //'0000-00-00 11:23';
            'turntime' => fn ($rawGeneral) => substr($rawGeneral['turntime'], 0, 19),
            'recent_war' => fn ($rawGeneral) => substr($rawGeneral['recent_war'], 0, 19),
            'bill' => fn ($rawGeneral) => getBillByLevel($rawGeneral['dedlevel']),
            'reservedCommand' => fn ($rawGeneral) => $reservedCommand[$rawGeneral['no']] ?? null,
            'autorun_limit' => fn ($rawGeneral) => ($rawGeneral['aux'] ?? [])['autorun_limit'] ?? 0,
        ];

        foreach ($rankColumns as $rankKey) {
            $specialViewFilter[$rankKey] = $getRankVar($rankKey);
        }


        $resultColumns = [];
        foreach (static::$viewColumns as $column => $reqPermission) {
            if ($reqPermission > $this->permission) {
                continue;
            }
            if (key_exists($column, static::$columnRemap)) {
                $newColumn = static::$columnRemap[$column];
                if ($newColumn !== null) {
                    $resultColumns[$newColumn] = $column;
                }
            } else {
                $resultColumns[$column] = $column;
            }
        }

        foreach (static::$customViewColumns as $column => $reqPermission) {
            if ($reqPermission > $this->permission) {
                continue;
            }
            $resultColumns[$column] = $column;
        }

        $generalList = [];
        foreach ($rawGeneralList as $rawGeneral) {
            //General 생성?
            if (key_exists('aux', $rawGeneral)) {
                $rawGeneral['aux'] = \sammo\JSON::decode($rawGeneral['aux']);
            }

            $item = [];
            foreach ($resultColumns as $column) {
                if (key_exists($column, $specialViewFilter)) {
                    $value = $specialViewFilter[$column]($rawGeneral);
                } else {
                    $value = $rawGeneral[$column];
                }
                $item[] = $value;
            }

            $generalList[] = $item;
        }

        if ($this->permission >= 1) {
            $troops = $db->queryAllLists('SELECT troop_leader,name FROM troop WHERE nation = %i', $nationID);
        } else {
            $troops = null;
        }


        $result = [
            'result' => true,
            'permission' => $this->permission,
            'column' => array_keys($resultColumns),
            'list' => $generalList,
            'troops' => $troops,
            'env' => $env,
        ];

        return $result;
    }
}
