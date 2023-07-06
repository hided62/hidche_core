<?php

namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$session = Session::requireGameLogin([])->setReadOnly();
$userID = Session::getUserID();
$generalID = $session->generalID;

increaseRefresh("NPC 정책 수정", 0);

$type = Util::getPost('type', 'string');
$data = Util::getPost('data', 'string');

$availableTypes = [
    'generalPriority',
    'nationPriority',
    'nationPolicy',
];

if (!in_array($type, $availableTypes)) {
    Json::die([
        'result' => false,
        'reason' => '올바른 타입이 아닙니다.',
    ]);
}

$data =  Json::decode($data);
if (!$data || !is_array($data)) {
    Json::die([
        'result' => false,
        'reason' => '올바른 입력이 아닙니다.',
    ]);
}

$db = DB::db();

$me = $db->queryFirstRow(
    'SELECT no, name, npc, nation, city, officer_level, refresh_score, turntime, belong, permission, penalty FROM `general`
    LEFT JOIN general_access_log AS l ON `general`.no = l.general_id WHERE owner=%i', $userID
);


$nationID = $me['nation'];
if (!$nationID) {
    Json::die([
        'result' => false,
        'reason' => '국가에 소속되어있지 않습니다.',
    ]);
}

$permission = checkSecretPermission($me);
if ($permission < 3) {
    Json::die([
        'result' => false,
        'reason' => '권한이 부족합니다. 군주, 외교권자, 조언자가 아닙니다.'
    ]);
}

function applyNationPolicy($policy, $nationID, $generalName): ?string
{
    $db = DB::db();
    $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');

    $defaultPolicy = AutorunNationPolicy::$defaultPolicy;
    $troopCache = null;
    foreach ($db->queryFirstColumn('SELECT troop_leader FROM troop WHERE nation=%i', $nationID) as $troopID) {
        $troopCache[$troopID] = 'Neutral';
    }
    $cityList = CityConst::all();
    $nationPolicyRoot = $nationStor->npc_nation_policy;
    $nationPolicy = $nationPolicyRoot['values'] ?? [];
    foreach ($policy as $key => $val) {

        if ($key === 'CombatForce') {
            if (!is_array($val)) {
                return "{$key}는 올바른 정책값이 아닙니다.";
            }
            foreach ($val as $troopID => $troopTarget) {
                if (!key_exists($troopID, $troopCache)) {
                    return "{$troopID}는 국가의 부대가 아닙니다.";
                }
                if ($troopCache[$troopID] != 'Neutral') {
                    return "부대({$troopID})는 하나의 역할만 지정할 수 있습니다.";
                }
                if (!is_array($troopTarget) || count($troopTarget) != 2) {
                    return "{$troopID}의 입력양식이 올바르지 않습니다.";
                }
                [$fromCity, $toCity] = $troopCache;
                if (!key_exists($fromCity, $cityList) || !key_exists($toCity, $cityList)) {
                    return "{$troopID}의 도시 {$fromCity}, {$toCity}가 올바른 도시 번호가 아닙니다.";
                }
                $troopCache[$troopID] = $key;
            }
            $nationPolicy[$key] = $val;
            continue;
        }
        if (in_array($key, ['SupportForce', 'DevelopForce'])) {
            if (!is_array($val)) {
                return "{$key}는 올바른 정책값이 아닙니다.";
            }
            foreach ($val as $troopID) {
                if (!key_exists($troopID, $troopCache)) {
                    return "{$troopID}는 국가의 부대가 아닙니다.";
                }
                if ($troopCache[$troopID] != 'Neutral') {
                    return "부대({$troopID}는 하나의 역할만 지정할 수 있습니다.";
                }
                $troopCache[$troopID] = $key;
            }

            $nationPolicy[$key] = array_values($val);
            continue;
        }


        if (!key_exists($key, $defaultPolicy)) {
            return "{$key}는 올바른 정책값이 아닙니다.";
        }
        $defaultValue = $defaultPolicy[$key];
        if (is_numeric($defaultValue) != is_numeric($val)) {
            return "{$key}는 올바른 값이 아닙니다.";
        }
        if (is_integer($defaultValue) != is_integer($val)) {
            return "{$key}는 올바른 값이 아닙니다.";
        }
        if (is_array($defaultValue) != is_array($val)) {
            return "{$key}는 올바른 값이 아닙니다.";
        }
        if (is_integer($val) && $val < 0) {
            $val = 0;
        }
        $nationPolicy[$key] = $val;
    }

    $nationPolicyRoot['values'] = $nationPolicy;
    $nationPolicyRoot['valueSetter'] = $generalName;
    $nationPolicyRoot['valueSetTime'] = TimeUtil::now();
    $nationStor->npc_nation_policy = $nationPolicyRoot;
    return null;
}

function applyNationPriority($priority, $nationID, $generalName): ?string
{
    $db = DB::db();
    $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
    $nationPolicyRoot = $nationStor->npc_nation_policy;

    $defaultPriority = AutorunNationPolicy::$defaultPriority;
    foreach ($priority as $item) {
        if (!in_array($item, $defaultPriority)) {
            return "{$item}은 올바른 명령이 아닙니다.";
        }
    }
    $nationPolicyRoot['priority'] = $priority;
    $nationPolicyRoot['prioritySetter'] = $generalName;
    $nationPolicyRoot['prioritySetTime'] = TimeUtil::now();
    $nationStor->npc_nation_policy = $nationPolicyRoot;
    return null;
}

function applyGeneralPriority($priority, $nationID, $generalName): ?string
{
    $db = DB::db();
    $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
    $generalPolicyRoot = $nationStor->npc_general_policy;

    $defaultPriority = AutorunGeneralPolicy::$default_priority;
    $actionOrder = [
        ['출병', '일반내정']
    ];
    $mustHaveActions = ['출병' => 1, '일반내정' => 1];

    $orderMap = [];
    foreach ($priority as $item) {
        if (key_exists($item, $mustHaveActions)) {
            $mustHaveActions[$item] = 0;
        }
        if (!in_array($item, $defaultPriority)) {
            return "{$item}은 올바른 명령이 아닙니다.";
        }
        $orderMap[$item] = count($orderMap);
    }

    foreach($actionOrder as [$preItem, $postItem]){
        if(!key_exists($preItem, $orderMap)){
            continue;
        }
        if(!key_exists($postItem, $orderMap)){
            continue;
        }
        if($orderMap[$preItem] > $orderMap[$postItem]){
            return "{$preItem} 명령은 {$postItem} 명령보다 먼저여야 합니다.";
        }
    }

    foreach ($mustHaveActions as $actionKey => $unset) {
        if ($unset) {
            return "{$actionKey}은 항상 사용해야 합니다.";
        }
    }

    $generalPolicyRoot['priority'] = $priority;
    $generalPolicyRoot['prioritySetter'] = $generalName;
    $generalPolicyRoot['prioritySetTime'] = TimeUtil::now();
    $nationStor->npc_general_policy = $generalPolicyRoot;
    return null;
}

if ($type == 'nationPolicy') {
    $result = applyNationPolicy($data, $nationID, $me['name']);
} else if ($type == 'generalPriority') {
    $result = applyGeneralPriority($data, $nationID, $me['name']);
} else if ($type == 'nationPriority') {
    $result = applyNationPriority($data, $nationID, $me['name']);
} else {
    throw new MustNotBeReachedException();
}

if ($result !== null) {
    Json::die([
        'result' => false,
        'reason' => $result,
    ]);
}
Json::die([
    'result' => true,
    'reason' => 'success',
]);
