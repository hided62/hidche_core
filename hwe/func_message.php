<?php
namespace sammo;

function getMailboxList(){
        
    $generalNations = [];

    foreach(DB::db()->queryAllLists('select `no`, `name`, `nation`, `level`, `npc` from `general` where `npc` < 2') as $general)
    {
        list($generalID, $generalName, $nationID, $level, $npc) = $general;
        if(!isset($generalNations[$nationID])){
            $generalNations[$nationID] = [];
        }

        $obj = [$generalID, $generalName, 0];

        if($level == 12){
            $obj[2] |= 1;
        }

        if($npc == 1){
            $obj[2] |= 2;
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
            "general"=>$generals
        ];
    }, array_merge([getNationStaticInfo(0)], getAllNationStaticInfo()));

    return $result;

}
