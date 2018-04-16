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


//Legacy

function moveMsg($table, $msgtype, $msgnum, $msg, $type, $who, $when, $column, $value) {
    $db = DB::db();
    $connect=$db->get();

    //TODO: moveMsg 쓰는 곳 모두 고쳐!!!
    $query = "update {$table} set {$msgtype}{$msgnum}='$msg',{$msgtype}{$msgnum}_type='$type',{$msgtype}{$msgnum}_who='$who',{$msgtype}{$msgnum}_when='$when' where {$column}='$value'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}
