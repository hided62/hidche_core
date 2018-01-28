<?php
require_once(__dir__.'/d_setting/conf.php');
require(__dir__.'/../vendor/autoload.php');

class Message{
    //기본 정보
    public $id;
    public $mailbox;
    public $type;
    public $isSender;
    public $src;
    public $dest;
    public $time;
    public $message;

    function __construct($row){
        $this->id = $row['id'];
        $this->mailbox = $row['mailbox'];
        $this->type = $row['type'];
        $this->isSender = $row['is_sender'] != 0;
        $this->src = [
            'id' => $row['src'],
            'name' => $row['src_name'],
            'nation' => $row['src_nation'],
            'color' => $row['src_color'],
            'nation_id' => $row['src_nation_id']
        ];

        if($this->src['nation'] === null){
            $this->src['nation'] = '재야';
            $this->src['color'] = '#FFFFFF';
            $this->src['nation_id'] = null;
        }

        $this->dest = [
            'id' => $row['dest'],
            'name' => $row['dest_name'],
            'nation' => $row['dest_nation'],
            'color' => $row['dest_color'],
            'nation_id' => $row['dest_nation_id']
        ];

        if($this->dest['nation'] === null){
            $this->dest['nation'] = '재야';
            $this->dest['color'] = '#FFFFFF';
            $this->dest['nation_id'] = null;
        }

        $this->datetime = $row['time'];
        $this->message = $row['message'];
    }
}



function getRawMessage($mailbox, $limit=30, $fromTime=NULL){
    //'select * from `message` where `mailbox` = 90 and `time` < "2018-01-21 04:47:20" ORDER BY `time` desc LIMIT 3 ';
    

    $sql = 'select * from `message` where `mailbox` = %i_mailbox';
    if($fromTime !== NULL){
        $sql .= ' and `time` <= %s_time';
    }
    $sql .= ' ORDER BY `time` desc';
    if($limit > 0){
        $sql .= ' LIMIT %i_limit';
    }

    //TODO: table 네임의 prefix를 처리할 수 있도록 개선
    $result = getDB()->query($sql, [
        'mailbox' => $mailbox,
        'limit' => $limit,
        'time' => $fromTime
    ]);
    
    
    return array_map(function ($row){
        return new Message($row);
    }, $result);
}

function getMessage($msgType, $limit=30, $fromTime=NULL){
    $generalID = getGeneralID();
    if($generalID === NULL){
        return [];
    }

    if($msgType === 'public'){
        return getRawMessage(9999, $limit, $fromTime);
    }
    else if($msgType === 'private'){
        return getRawMessage($genID, $limit, $fromTime);
    }
    else if($msgType === 'national'){
        $nationID = getDB()->queryFirstField(
            'select `nation` from `general` where user_id = %i',
            $genID
        );
        return getRawMessage(9000 + $nationID, $limit, $fromTime);
    }
    else{
        return [];
    }
}

function sendRawMessage($msgType, $isSender, $mailbox, $src, $dest, $msg, $date){
    
    getDB()->insert('message', array(
        'address' => $dest,
        'type' => 'receive',
        'src' => $src['id'],
        'dest' => $dest['id'],
        'time' => $date,
        'src_nation_id' => util::array_get($src['nation_id'], null),
        'src_name' => util::array_get($src['name'], null),
        'src_nation' => util::array_get($src['nation'], null),
        'src_color' => util::array_get($src['color'], null),
        'src_icon' => util::array_get($src['icon'], null),
        'dest_nation_id' => util::array_get($dest['nation_id'], null),
        'dest_name' => util::array_get($dest['name'], null),
        'dest_nation' => util::array_get($dest['nation'], null),
        'dest_color' => util::array_get($dest['color'], null),
        'message' => $msg
    ));
}

function sendMessage($msgType, $src, $dest, $msg, $date = null){
    if($date === null){
        $date = $datetime->format('Y-m-d H:i:s');
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
    else{
        //dest는 id, name이 필수
        $srcMailbox = $src['id'];
        $destMailbox = $dest['id'];
    }

    if($srcMailbox !== null){
        sendRawMessage($msgType, true, $srcMailbox, $src, $dest, $msg, $date);
    }
    sendRawMessage($msgType, false, $destMailbox, $src, $dest, $msg, $date);
}

function getMailboxList(){
    $result = [];

    $generalID = getGeneralID();
    $db = getDB();
    $me = $db->queryFirstRow('select no,nation,level from general where user_id=%i', $generalID);

    //가장 최근에 주고 받은 사람.
    $latestMessage = util::array_get(getMessage('private', 1)[0], null);
    if($latestMessage !== null){
        if($latestMessage->src == $generalID){
            $latestMessage = $latestMessage->dest;
        }
        else{
            $latestMessage = $latestMessage->src;
        }
    }

    $nations = [];
    foreach ($db->query('select nation, name, color from nation') as $nation) {
        $nations[$nation['nation']] = $nation;
    }
    $nations[0] = [
        'nation' => 0,
        'name' => '재야',
        'color' => '#ffffff'
    ];

    $generals = $db->query('select no, nation, name, level from general where npc < 2 and no != %i order by name asc', $generalID);
    foreach ($generals as $general) {
        $nations[$general['nation']] = $general;
    }

    return [
        'me' => $me,
        'latest' => $latestMessage,
        'nations' => $nations
    ];
}

function genList($connect) {
    $query = "select no,nation,level,msgindex,userlevel from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);
    $you = [];
    
    $query = "select msg{$me['msgindex']}_who as reply,msg{$me['msgindex']}_type as type from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $reply = MYDB_fetch_array($result);
    if($reply['type'] % 100 == 9) {
        $reply['reply'] %= 10000;
        $query = "select no,name from general where no={$reply['reply']}";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $you = MYDB_fetch_array($result);
    } elseif($reply['type'] % 100 == 10) {
        $reply['reply'] = floor($reply['reply']/10000);
        $query = "select no,name from general where no={$reply['reply']}";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $you = MYDB_fetch_array($result);
    }

    $query = "select nation,color,name from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    echo "
<select name=genlist size=1 style=color:white;background-color:black;font-size:13>
    <optgroup label='즐겨찾기'>";
    if($me['nation'] != 0) {
        echo "
    <option selected style=color:".newColor($nation['color']).";background-color:{$nation['color']} value="; echo $nation['nation']+9000; echo ">【 아국 메세지 】</option>";
    } else {
        echo "
    <option selected style=color:".newColor($nation['color']).";background-color:{$nation['color']} value="; echo 9000; echo ">【 재야 】</option>";
    }
    echo "
    <option value=9999>【 전체&nbsp;&nbsp;&nbsp;메세지 】</option>";
    if($you) {
        echo "
    <option value={$you['no']}>{$you['name']}</option>";
    }
    echo "
    <option value=1>운영자</option>";

    if($me['level'] >= 5 || $me['userlevel'] >= 5) {
        echo "
    </optgroup>
    <optgroup label='국가메세지'>
    <option value=9000>【 재야 】</option>";

        $query = "select nation,name,color from nation order by binary(name)";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);
        for($i=1; $i <= $count; $i++) {
            $nation = MYDB_fetch_array($result);
            $nationNation[$nation['nation']] = $nation['nation'];
            $nationName[$nation['nation']]   = $nation['name'];
            $nationColor[$nation['nation']]  = $nation['color'];
            echo "
    <option style=color:".newColor($nation['color']).";background-color:{$nation['color']} value="; echo $nation['nation']+9000; echo ">【 {$nation['name']} 】</option>";
        }
        echo "
    </optgroup>";
    }

    echo "
    <optgroup label='개인메세지'>
    <optgroup label='재야'>";
    $query = "select no,name,npc from general where nation=0 and user_id!='{$_SESSION['p_id']}' and npc<2 order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if($general['npc'] >= 2)     { $color = "cyan"; }
        elseif($general['npc'] == 1) { $color = "skyblue"; }
        else                       { $color = "white"; }
        echo "
    <option value={$general['no']} style=color:{$color};background-color:black;>{$general['name']}</option>";
    }
    echo "
    </optgroup>";

    $query = "select nation,name,color from nation order by binary(name)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=1; $i <= $count; $i++) {
        $nation = MYDB_fetch_array($result);
        echo "
    <optgroup label='【{$nation['name']}】' style=color:".newColor($nation['color']).";background-color:{$nation['color']};>";

        $query = "select no,name,npc,level from general where nation='{$nation['nation']}' and user_id!='{$_SESSION['p_id']}' and npc<2 order by npc,binary(name)";
        $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);
        for($j=0; $j < $gencount; $j++) {
            $general = MYDB_fetch_array($genresult);
            if($general['level'] >= 12) { $general['name'] = "*{$general['name']}*"; }
            if($general['npc'] >= 2)     { $color = "cyan"; }
            elseif($general['npc'] == 1) { $color = "skyblue"; }
            else                       { $color = "white"; }
            echo "
    <option value={$general['no']} style=color:{$color};background-color:black;>{$general['name']}</option>";
        }
        echo "
    </optgroup>";
    }
    echo "
</select>
";
}


function MsgFile($skin, $bg, $nation=0, $level=0) {
    switch($bg) {
        case 1: $bgcolor = "000055"; $count = 10; $fl = "_all_msg.txt"; break;
        case 3: $bgcolor = "336600"; $count = 20; $fl = "_nation_msg{$nation}.txt"; break;
    }

    if(!file_exists("logs/{$fl}")){
        return;
    }
    $fp = @fopen("logs/{$fl}", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $code = explode("\n",$file);
    for($i=0; $i < $count; $i++) {
        $msg = isset($code[count($code)-2-$i]) ? explode("|", $code[count($code)-2-$i]) : array();

        $cnt = count($msg);
		if(!empty($cnt)){
	        for($k=0; $k < $cnt; $k++) {
	        	 $msg[$k] = trim($msg[$k]); 
			}
		}
        if(!empty($msg)){
            ShowMsg($skin, $bgcolor, $msg[0], $msg[7], $msg[8], $msg[1], $msg[5], $msg[2], $msg[6], $msg[4], $msg[3]);
        }
    }
}


// type : xx,xx(불가침기간,타입)
// who : xxxx,xxxx(발신인, 수신인)
function DecodeMsg($connect, $msg, $type, $who, $date, $bg, $num=0) {
    $query = "select skin,no,nation,name,picture,level from general where user_id='{$_SESSION['p_id']}'";
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
        $sndrnation['name'] = '재야';
        $sndrnation['color'] = 'FFFFFF';
    } else {
        $query = "select name,color from nation where nation='{$sndr['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $sndrnation = MYDB_fetch_array($result);
    }

    switch($bg) {
        case 2:
        case 4: $bgcolor = "CC6600"; break;
    }

    if($category == 6) {
        $query = "select reserved from diplomacy where me='{$sndr['nation']}' and you='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $dip = MYDB_fetch_array($result);

        ShowMsg($me['skin'], $bgcolor, $category, $sndr['picture'], $sndr['imgsvr'], "{$sndr['name']}:{$sndrnation['name']}▶", $sndrnation['color'], $sndr['name'], $sndrnation['color'], $msg, $date, $num, $from, $term, $me['level'], $dip['reserved']);
    } elseif($category <= 8) {
        $query = "select name,color from nation where nation='$to'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $rcvrnation = MYDB_fetch_array($result);

        ShowMsg($me['skin'], $bgcolor, $category, $sndr['picture'], $sndr['imgsvr'], "{$sndr['name']}:{$sndrnation['name']}▶", $sndrnation['color'], $rcvrnation['name'], $rcvrnation['color'], $msg, $date, $num, $from, $term, $me['level']);
    } elseif($category <= 11) {
        $query = "select name,picture,nation from general where no='$to'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $rcvr = MYDB_fetch_array($result);

        if($rcvr['nation'] == 0) {
            $rcvrnation['name'] = '재야';
            $rcvrnation['color'] = 'FFFFFF';
        } else {
            $query = "select name,color from nation where nation='{$rcvr['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $rcvrnation = MYDB_fetch_array($result);
        }
        ShowMsg($me['skin'], $bgcolor, $category, $sndr['picture'], $sndr['imgsvr'], "{$sndr['name']}:{$sndrnation['name']}▶", $sndrnation['color'], "{$rcvr['name']}:{$rcvrnation['name']}", $rcvrnation['color'], $msg, $date, $num, $from, $term);
    }
}


function MsgMe($connect, $bg) {
    $query = "select no,nation,msgindex,
        msg0,msg1,msg2,msg3,msg4,msg5,msg6,msg7,msg8,msg9,
        msg0_type,msg1_type,msg2_type,msg3_type,msg4_type,msg5_type,msg6_type,msg7_type,msg8_type,msg9_type,
        msg0_who,msg1_who,msg2_who,msg3_who,msg4_who,msg5_who,msg6_who,msg7_who,msg8_who,msg9_who,
        msg0_when,msg1_when,msg2_when,msg3_when,msg4_when,msg5_when,msg6_when,msg7_when,msg8_when,msg9_when
        from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $index = $me['msgindex'];
    for($i=0; $i < 10; $i++) {
        if($me["msg{$index}"]) { echo "\n"; DecodeMsg($connect, $me["msg{$index}"], $me["msg{$index}_type"], $me["msg{$index}_who"], $me["msg{$index}_when"], $bg, $index); }
        $index--;
        if($index < 0) { $index = 9; }
    }
}




function moveMsg($connect, $table, $msgtype, $msgnum, $msg, $type, $who, $when, $column, $value) {
    //TODO: moveMsg 쓰는 곳 모두 고쳐!!!
    $query = "update {$table} set {$msgtype}{$msgnum}='$msg',{$msgtype}{$msgnum}_type='$type',{$msgtype}{$msgnum}_who='$who',{$msgtype}{$msgnum}_when='$when' where {$column}='$value'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function MsgDip($connect, $bg) {
    $query = "select no,nation from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select dip0,dip1,dip2,dip3,dip4,dip0_who,dip1_who,dip2_who,dip3_who,dip4_who,dip0_when,dip1_when,dip2_when,dip3_when,dip4_when,dip0_type,dip1_type,dip2_type,dip3_type,dip4_type from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($nation['dip0']) { echo "\n"; DecodeMsg($connect, $nation['dip0'], $nation['dip0_type'], $nation['dip0_who'], $nation['dip0_when'], $bg, 0); }
    if($nation['dip1']) { echo "\n"; DecodeMsg($connect, $nation['dip1'], $nation['dip1_type'], $nation['dip1_who'], $nation['dip1_when'], $bg, 1); }
    if($nation['dip2']) { echo "\n"; DecodeMsg($connect, $nation['dip2'], $nation['dip2_type'], $nation['dip2_who'], $nation['dip2_when'], $bg, 2); }
    if($nation['dip3']) { echo "\n"; DecodeMsg($connect, $nation['dip3'], $nation['dip3_type'], $nation['dip3_who'], $nation['dip3_when'], $bg, 3); }
    if($nation['dip4']) { echo "\n"; DecodeMsg($connect, $nation['dip4'], $nation['dip4_type'], $nation['dip4_who'], $nation['dip4_when'], $bg, 4); }
}

function ShowMsgEx($msgType, $src, $dest, $msg, $datetime){
    new Plates('templates');
}

function ShowMsg($skin, $bgcolor, $type, $picture, $imgsvr, $me, $mycolor, $you, $youcolor, $msg, $date, $num=0, $who=0, $when=0, $level=0, $note="") {
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
    if($skin == 0) {
        $bgcolor = "000000"; $picture = "";
        $naming = "[{$me}{$sign}{$you}]";
    } else {
        $imageTemp = GetImageURL($imgsvr);
        $naming = "[<font color=$mycolor>$me</font>{$sign}<font color=$youcolor>$you</font>]";
        $picture = "<img src={$imageTemp}/{$picture}>";
    }
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
        <table width=498 border=1  bordercolordark=gray bordercolorlight=black cellpadding=0 cellspacing=0 bgcolor='$bgcolor' style=font-size:13;table-layout:fixed;word-break:break-all;>
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
