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

// type : xx,xx(불가침기간,타입)
// who : xxxx,xxxx(발신인, 수신인)
function DecodeMsg($msg, $type, $who, $date, $bg, $num=0) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    //FIXME: 폐기
    $query = "select no,nation,name,picture,level from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    // 1 : 전메, 2 : 아국->타국, 3 : 타국->아국
    // 4 : 합병, 5 : 통합, 6 : 불가침, 7 : 종전, 8 : 파기
    // 9 : 자신->타인, 10 : 타인 -> 자신, 11 : 등용
    $category = $type % 100;
    $term = intdiv($type, 100);
    $from = intdiv($who, 10000);
    $to = $who % 10000;

    $query = "select name,picture,imgsvr,nation from general where no='$from'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $sndr = MYDB_fetch_array($result);

    $sndrnation = getNationStaticInfo($sndr['nation']);

    switch($bg) {
        case 2:
        case 4: $bgcolor = "#CC6600"; break;
    }

    if($category == 6) {
        $query = "select reserved from diplomacy where me='{$sndr['nation']}' and you='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $dip = MYDB_fetch_array($result);

        ShowMsg($bgcolor, $category, $sndr['picture'], $sndr['imgsvr'], "{$sndr['name']}:{$sndrnation['name']}▶", $sndrnation['color'], $sndr['name'], $sndrnation['color'], $msg, $date, $num, $from, $term, $me['level'], $dip['reserved']);
    } elseif($category <= 8) {
        $rcvrnation = getNationStaticInfo($to);

        ShowMsg($bgcolor, $category, $sndr['picture'], $sndr['imgsvr'], "{$sndr['name']}:{$sndrnation['name']}▶", $sndrnation['color'], $rcvrnation['name'], $rcvrnation['color'], $msg, $date, $num, $from, $term, $me['level']);
    } elseif($category <= 11) {
        $query = "select name,picture,nation from general where no='$to'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $rcvr = MYDB_fetch_array($result);

        $rcvrnation = getNationStaticInfo($rcvr['nation']);
        ShowMsg($bgcolor, $category, $sndr['picture'], $sndr['imgsvr'], "{$sndr['name']}:{$sndrnation['name']}▶", $sndrnation['color'], "{$rcvr['name']}:{$rcvrnation['name']}", $rcvrnation['color'], $msg, $date, $num, $from, $term);
    }
}


function moveMsg($table, $msgtype, $msgnum, $msg, $type, $who, $when, $column, $value) {
    $db = DB::db();
    $connect=$db->get();

    //TODO: moveMsg 쓰는 곳 모두 고쳐!!!
    $query = "update {$table} set {$msgtype}{$msgnum}='$msg',{$msgtype}{$msgnum}_type='$type',{$msgtype}{$msgnum}_who='$who',{$msgtype}{$msgnum}_when='$when' where {$column}='$value'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function MsgDip($bg) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    $query = "select no,nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select dip0,dip1,dip2,dip3,dip4,dip0_who,dip1_who,dip2_who,dip3_who,dip4_who,dip0_when,dip1_when,dip2_when,dip3_when,dip4_when,dip0_type,dip1_type,dip2_type,dip3_type,dip4_type from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($nation['dip0']) { echo "\n"; DecodeMsg($nation['dip0'], $nation['dip0_type'], $nation['dip0_who'], $nation['dip0_when'], $bg, 0); }
    if($nation['dip1']) { echo "\n"; DecodeMsg($nation['dip1'], $nation['dip1_type'], $nation['dip1_who'], $nation['dip1_when'], $bg, 1); }
    if($nation['dip2']) { echo "\n"; DecodeMsg($nation['dip2'], $nation['dip2_type'], $nation['dip2_who'], $nation['dip2_when'], $bg, 2); }
    if($nation['dip3']) { echo "\n"; DecodeMsg($nation['dip3'], $nation['dip3_type'], $nation['dip3_who'], $nation['dip3_when'], $bg, 3); }
    if($nation['dip4']) { echo "\n"; DecodeMsg($nation['dip4'], $nation['dip4_type'], $nation['dip4_who'], $nation['dip4_when'], $bg, 4); }
}

function ShowMsg($bgcolor, $type, $picture, $imgsvr, $me, $mycolor, $you, $youcolor, $msg, $date, $num=0, $who=0, $when=0, $level=0, $note="") {
    if($msg == "") return;

    $msg = Tag2Code($msg);

    $site = ""; $form = ""; $form2 = "";
    if($type == 11 || ($type >= 4 && $type <= 8 && $level >= 5)) {
        $corebutton = "&nbsp;<input type=submit name=ok value=수락 onclick='return confirm(\"정말 수락하시겠습니까?\")'><input type=submit name=ok value=거절 onclick='return confirm(\"정말 거절하시겠습니까?\")'>";
    } else {
        $corebutton = "&nbsp;【수락】【거절】";
    }
    if($type == 6) {
        $corebutton .= "<br>비고: {$note}";
    }
    switch($type) {
    case  1: $sign = ""; $corebutton = ""; break;
    case  2: $sign = ""; $corebutton = ""; break;
    case  3: $sign = ""; $corebutton = ""; break;
    case  4: $sign = ""; $site = "d_surrender.php"; break;
    case  5: $sign = ""; $site = "d_merge.php";     break;
    case  6: $sign = ""; $site = "d_ally.php";      break;
    case  7: $sign = ""; $site = "d_cease.php";     break;
    case  8: $sign = ""; $site = "d_cancel.php";    break;
    case  9: $sign = ""; $corebutton = ""; break;
    case 10: $sign = ""; $corebutton = ""; break;
    case 11: $sign = ""; $site = "d_scout.php"; break;
    }
    $imageTemp = GetImageURL($imgsvr);
    $naming = "[<font color=$mycolor>$me</font>{$sign}<font color=$youcolor>$you</font>]";
    $picture = "<img width='64' height='64' src={$imageTemp}/{$picture}>";
    if($site != "") {
        $form = "<form name=scout method=post action={$site}>";
        $form2 = "</form>";
    }
    if($num >= 0) { $num = "<input type=hidden name=num value=$num>"; }
    else { $num = ""; }
    if($who > 0) { $who = "<input type=hidden name=gen value=$who>"; }
    else { $who = ""; }
    if($when > 0) { $when = "<input type=hidden name=when value=$when>"; }
    else { $when = ""; }
    echo "
        <table width=498 border=1  bordercolordark=gray bordercolorlight=black cellpadding=0 cellspacing=0 bgcolor='$bgcolor' style=font-size:13px;table-layout:fixed;word-break:break-all;>
            <tr>
                <td width=64 height=64>$picture</td>
                $form
                <td width=434 valign=top><b>$naming</b><font size=1><$date></font> <br>{$msg}{$corebutton}</td>
                $num
                $who
                $when
                $form2
            </tr>
        </table>
    ";
}
