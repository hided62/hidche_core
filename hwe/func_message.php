<?php
namespace sammo;

function getMailboxList()
{
        
    $generalNations = [];

    foreach(DB::db()->query('select `no`, `name`, `nation`, `officer_level`, `npc`, `permission`, `penalty` from `general` where `npc` < 2') as $general)
    {
        [$generalID, $generalName, $nationID, $officerLevel, $npc] = [$general['no'], $general['name'], $general['nation'], $general['officer_level'], $general['npc']];
        if(!isset($generalNations[$nationID])){
            $generalNations[$nationID] = [];
        }

        $obj = [$generalID, $generalName, 0];
        $permission = checkSecretPermission($general, false);

        if($officerLevel == 12){
            $obj[2] |= 1;
        }

        if($npc == 1){
            $obj[2] |= 2;
        }

        if($permission == 4){
            $obj[2] |= 4;
        }

        $generalNations[$nationID][] = $obj;
    }

    $result = array_map(function($nation) use ($generalNations) {
        $nationID = $nation['nation'];

        $mailbox = $nationID + Message::MAILBOX_NATIONAL;
        $nationName = $nation['name'];
        $color = $nation['color'];
        $generals = Util::array_get($generalNations[$nationID], []);

        return [
            "mailbox"=>$mailbox,
            "name"=>$nationName,
            "color"=>$color,
            "general"=>$generals,
        ];
    }, array_merge([getNationStaticInfo(0)], getAllNationStaticInfo()));

    return $result;

}
