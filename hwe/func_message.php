<?php
namespace sammo;

class Message{
    //기본 정보
    public $id;
    public $mailbox;
    public $type;
    public $isSender;
    /** @var mixed[] */
    public $src;
    /** @var mixed[] */
    public $dest;
    public $time;
    public $text;
    public $option;

    function __construct($row){
        $db_message = $row['message'];
        $this->id = $row['id'];
        $this->mailbox = $row['mailbox'];
        $this->type = $row['type'];
        $this->isSender = $row['is_sender'] != 0;
        $this->src = $db_message['src'];

        if($this->src['nation'] === null){
            $this->src['nation'] = '재야';
            $this->src['color'] = '#FFFFFF';
            $this->src['nation_id'] = null;
        }

        $this->dest = $db_message['dest'];

        if($this->dest['nation'] === null){
            $this->dest['nation'] = '재야';
            $this->dest['color'] = '#FFFFFF';
            $this->dest['nation_id'] = null;
        }

        $this->time = $row['time'];
        $this->text = $db_message['text'];
        $this->option = $db_message['option'];
    }
}

function getSingleMessage($messageID){
    $messageInfo = DB::db()->queryFirstRow('select * from `message` where `id` = %i', $messageID);

    if (!$messageInfo) {
        return [false, '존재하지 않는 메시지'];
    }
    
    return [true, $messageInfo];
}

function getRawMessage($mailbox, $msgType, $limit=30, $fromSeq=null){


    $sql = 'select * from `message` where `mailbox` = %i_mailbox and `type` = %s_type and `valid_until` > now()';
    if($fromSeq !== null){
        $sql .= ' and `id` > %i_id';
    }
    $sql .= ' ORDER BY `id` desc';
    if($limit > 0){
        $sql .= ' LIMIT %i_limit';
    }

    //TODO: table 네임의 prefix를 처리할 수 있도록 개선
    $result = DB::db()->query($sql, [
        'mailbox' => $mailbox,
        'type' => $msgType,
        'limit' => $limit,
        'time' => $fromTime
    ]);
    
    
    return array_map(function ($row){
        return new Message($row);
    }, $result);
}

function getMessage($msgType, $nationID=null, $limit=30, $fromSeq=null){
    $generalID = Session::getInstance()->generalID;
    if($generalID === null){
        return [];
    }

    if($msgType === 'public'){
        return getRawMessage(9999, 'public', $limit, $fromSeq);
    }
    else if($msgType === 'private'){
        return getRawMessage($generalID, 'private', $limit, $fromSeq);
    }
    else if($msgType === 'national'){
        if($nationID === null){
            return [];
        }

        return getRawMessage($nationID + 9000, 'national', $limit, $fromSeq);
    }
    else if($msgType === 'diplomacy'){
        if($nationID === null){
            return [];
        }
        
        return getRawMessage($nationID + 9000, 'diplomacy', $limit, $fromSeq);
    }
    else{
        return [];
    }
}

function sendRawMessage($msgType, $isSender, $mailbox, $src, $dest, $msg, $date, $validUntil, $msgOption){
    
    $srcNation = getNationStaticInfo($src['nation_id']);
    $destNation = getNationStaticInfo($dest['nation_id']);

    $src['nation'] = Util::array_get($srcNation['name'], '재야');
    $src['color'] = Util::array_get($srcNation['color'], '#ffffff');

    $dest['nation'] = Util::array_get($destNation['name'], '재야');
    $dest['color'] = Util::array_get($destNation['color'], '#ffffff');

    if(!$isSender && $mailbox < 9000 && Util::array_get($msgOption['alert'], false)){
        //TODO:newmsg보단 lastmsg로 datetime을 넣는게 더 나아보임
        DB::db()->update('general', array(
            'newmsg' => true
        ), 'no=%i', $dest['id']);
    }

    if(isset($msgOption['alert'])){
        unset($msgOption['alert']);
    }

    DB::db()->insert('message', [
        'address' => $dest,
        'type' => $msgType,
        'is_sender' => $isSender,
        'src' => $src['id'],
        'dest' => $dest['id'],
        'time' => $date,
        'valid_until' => $validUntil,
        'message' => Json::encode([
            'src' => $src,
            'dest' =>$dest,
            'text' => $msg,
            'option' => $msgOption
        ], Json::DELETE_NULL)
    ]);
}
/**
 * @param string $msgType
 * @param mixed[] $src
 * @param mixed[] $dest
 * @param null|string $date
 * @param null|string $validUntil
 */
function sendMessage($msgType, array $src, array $dest, $msg, $date = null, $validUntil = null, $msgOption = null){
    if($date === null){
        $date = (new \Datetime())->format('Y-m-d H:i:s');
    }

    if($validUntil === null){
        $validUntil = '9999-12-31 12:59:59';
    }

    if($msgType === 'public'){
        //dest는 필요하지 않음
        $srcMailbox = null;
        $destMailbox = 9999;
        $dest['id'] = 9999;
    }
    else if($msgType === 'national'){
        //dest는 nation_id만 필요함
        $dest['id'] = $dest['nation_id'] + 9000;

        if($src['nation_id'] === $dest['nation_id']){
            $srcMailbox = null;
        }
        else{
            $srcMailbox = $src['nation_id'] + 9000;
        }
        $destMailbox = $dest['nation_id'] + 9000;
    }
    else if($msgType === 'diplomacy'){
        //NOTE:외교 서신의 경우 '동일한 내용'이 두번 가지 않으므로, 별도 처리가 필요함
        $src['id'] = $dest['nation_id'] + 9000;
        $dest['id'] = $dest['nation_id'] + 9000;
        $destMailbox = $dest['id'];
    }
    else{
        //dest는 id, name이 필수
        $srcMailbox = $src['id'];
        $destMailbox = $dest['id'];
    }

    if($srcMailbox !== null){
        sendRawMessage($msgType, true, $srcMailbox, $src, $dest, $msg, $date, $validUntil, null);
    }
    sendRawMessage($msgType, false, $destMailbox, $src, $dest, $msg, $date, $validUntil, null);

    return true;
}


function sendScoutMsg($src, $dest, $date) {

    //$msgType, $isSender, $mailbox, $src, $dest, $msg, $date, $validUntil, $msgOption

    if(!$src || !$src['id'] || !$src['nation_id']){
        return false;
    }

    $nation = getNationStaticInfo($src['nation_id']);
    $nationName = Util::array_get($nation['name'], '재야');

    if(!$dest || !$dest['id']){
        return false;
    }

    $msgType = 'private';
    $option = [
        "action" => "scout"
    ];

    $msg = "{$src['nation']}(으)로 망명 권유 서신";
    $validUntil = "9999-12-31 12:59:59";//등용장의 시간 제한 없음
    
    sendRawMessage('private', false, $dest['id'], $src, $dest, $msg, $date, $validUntil, $option);

    return true;
}


function getMailboxList(){
        
    $generalNations = [];

    foreach(DB::db()->query('select `no`, `name`, `nation`, `level`, `npc` from `general` where `npc` < 2') as $general)
    {
        list($generalID, $generalName, $nationID, $level, $npc) = $general;
        if(!isset($generalNations[$nationID])){
            $generalNations[$nationID] = [];
        }

        $isChief = ($level == 12);

        $obj = [$generalID, $generalName];
        if($isChief){
            $obj[] = 1;
        }

        //TODO: 빙의장 정보 추가
        $generalNations[$nationID][] = $obj;
    }

    $neutral = [
        "nation"=>0,
        "name"=>"재야",
        "color"=>"#ffffff"
    ];

    $result = array_map(function($nation) use ($generalNations) {
        $nationID = $nation['nation'];
        $mailbox = $nationID + 9000;
        $nation = $nation['name'];
        $color = $nation['color'];
        $generals = Util::array_get($generalNations[$nationID], []);

        return [
            "nationID"=>$nationID,
            "mailbox"=>$mailbox,
            "nation"=>$nationID,
            "color"=>$color,
            "general"=>$generals
        ];
    }, array_merge([$neutral], getAllNationStaticInfo()));

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
    $term = floor($type / 100);
    $from = floor($who / 10000);
    $to = $who % 10000;

    $query = "select name,picture,imgsvr,nation from general where no='$from'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $sndr = MYDB_fetch_array($result);

    if($sndr['nation'] == 0) {
        $sndrnation = [];
        $sndrnation['name'] = '재야';
        $sndrnation['color'] = '#FFFFFF';
    } else {
        $sndrnation = getNationStaticInfo($sndr['nation']);
    }

    switch($bg) {
        case 2:
        case 4: $bgcolor = "CC6600"; break;
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

        if($rcvr['nation'] == 0) {
            $rcvrnation = [];
            $rcvrnation['name'] = '재야';
            $rcvrnation['color'] = '#FFFFFF';
        } else {
            $rcvrnation = getNationStaticInfo($rcvr['nation']);
        }
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
    $picture = "<img src={$imageTemp}/{$picture}>";
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
