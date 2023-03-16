<?php

namespace sammo\API\Global;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;

use function sammo\getAllNationStaticInfo;
use function sammo\getNationStaticInfo;

class GetNationList extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $db = DB::db();

        $nations = getAllNationStaticInfo();
        uasort($nations, function ($lhs, $rhs) {
            return $rhs['power'] <=> $lhs['power'];
        });

        $nations[0] = getNationStaticInfo(0);

        foreach ($db->query('SELECT npc,name,nation,officer_level,permission FROM `general` ORDER BY dedication DESC') as $general) {
            $nationID = $general['nation'];

            $permission = $general['permission'];
            if($permission != 'auditor' && $permission != 'ambassador'){
                unset($general['permission']);
            }

            if($general['officer_level'] < 5){
                $general['officer_level'] = 1;
            }

            if (!key_exists('generals', $nations[$nationID])) {
                $nations[$nationID]['generals'] = [];
            }
            $nations[$nationID]['generals'][] = $general;
        }

        foreach ($db->queryAllLists('SELECT city, name, nation FROM city') as [$cityID, $cityName, $nationID]) {
            if (!key_exists('cities', $nations[$nationID])) {
                $nations[$nationID]['cities'] = [];
            }
            $nations[$nationID]['cities'][$cityID] = $cityName;
        }

        return [
            'result' => true,
            'nations' => $nations,
        ];
    }
}
