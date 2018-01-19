<?php
include "lib.php";
include "func.php";
require "../e_lib/util.php";

$id = $_POST['id'];
$pw = $_POST['pw'];
$name       = $_POST['name'];
$name       = _String::NoSpecialCharacter($name);
$pic        = util::array_get($_POST['pic'],'');
$character  = $_POST['character'];

$pwTemp = substr($pw, 0, 32);
$mylog = [];
$connect = dbConn("sammo");

//회원 테이블에서 정보확인
$query = "select no,id,picture,imgsvr,grade from MEMBER where id='$id' and pw='$pwTemp'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$member = MYDB_fetch_array($result);

if(!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$date = date('Y-m-d H:i:s');
//등록정보
$query = "update MEMBER set reg_num=reg_num+1,reg_date='$date' where no='{$member['no']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$connect = dbConn();

########## 동일 정보 존재여부 확인. ##########

$query = "select year,month,scenario,maxgeneral,turnterm,genius,img from game where no='1'";
$result = MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query  = "select no from general where npc<2";
$result = MYDB_query($query,$connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($result);

$query  = "select no from general where user_id='{$member['id']}'";
$result = MYDB_query($query,$connect) or Error(__LINE__.MYDB_error($connect),"");
$id_num = MYDB_num_rows($result);

$query  = "select no from general where name='$name'";
$result = MYDB_query($query,$connect) or Error(__LINE__.MYDB_error($connect),"");
$name_num = MYDB_num_rows($result);

$query  = "select * from token where id='{$member['id']}'";
$result = MYDB_query($query,$connect) or Error(__LINE__.MYDB_error($connect),"");
$token_num = MYDB_num_rows($result);

if($token_num == 1) {
    $token = MYDB_fetch_array($result);
    $leader = $token['leader'];
    $power = $token['power'];
    $intel = $token['intel'];
} else {
    $leader = 0;
    $power = 0;
    $intel = 0;
}

if($id_num) {
    echo("<script>
      window.alert('이미 등록하셨습니다!')
      history.go(-1)
      </script>");
    exit;
} elseif($name_num) {
    echo("<script>
      window.alert('이미 있는 장수입니다. 다른 이름으로 등록해 주세요!')
      history.go(-1)
      </script>");
    exit;
} elseif($token_num != 1) {
    echo("<script>
      window.alert('능력치 정보가 잘못되었습니다. 운영자에게 문의해 주세요!')
      history.go(-1)
      </script>");
    exit;
} elseif($admin['maxgeneral'] <= $gencount) {
    echo("<script>
      window.alert('더이상 등록할 수 없습니다!')
      history.go(-1)
      </script>");
    exit;
} elseif($id == "") {
    echo("<script>
      window.alert('ID가 없습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
} elseif(_String::GetStrLen($name) < 1) {
    echo("<script>
      window.alert('이름이 짧습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
} elseif($name == "") {
    echo("<script>
      window.alert('이름이 없습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
} elseif(_String::GetStrLen($name) > 6) {
    echo("<script>
      window.alert('이름이 유효하지 않습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
} elseif($leader + $power + $intel > 150) {
    echo("<script>
      window.alert('능력치가 150을 넘어섰습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
} elseif($leader > 75 || $leader < 10 || $power > 75 || $power < 10 || $intel > 75 || $intel < 10) {
    echo("<script>
      window.alert('능력치가 비정상적입니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
} elseif($character < 0 || $character > 11) {
    echo("<script>
      window.alert('성격이 비정상적입니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
} else {
    $ratio = rand() % 100;
    // 현재 1%
    if($ratio == 50 && $admin['genius'] > 0) {
        $genius = 1;

        $query = "update game set genius=genius-1 where no='1'";
        MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");
    } else {
        $genius = 0;
    }

    //중, 소 공백지 개수
    $query = "select city from city where level>=5 and level<=6 and nation=0";
    $result = MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    // 공백지에서만 태어나게
    if($citycount > 0) {
        $query = "select city from city where level>=5 and level<=6 and nation=0 order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");
        $city = MYDB_fetch_array($result);
    } else {
        $query = "select city from city where level>=5 and level<=6 order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");
        $city = MYDB_fetch_array($result);
    }
    $city = $city['city'];

    $total  = rand() % 6;
    $pleader = rand() % 100;
    $ppower  = rand() % 100;
    $pintel  = rand() % 100;
    $rate = $pleader + $ppower + $pintel;
    $pleader = floor($pleader / $rate * $total + 0.5);
    $ppower  = floor($ppower  / $rate * $total + 0.5);
    $pintel  = floor($pintel  / $rate * $total + 0.5);

    $leader = $leader + $pleader;
    $power = $power + $ppower;
    $intel = $intel + $pintel;

    $age = 20 + ($pleader + $ppower + $pintel) * 2 + (rand() % 2);
    // 아직 남았고 천재등록상태이면 특기 부여
    if($genius == 1) {
        $specage2 = $age;
        $special2 = getSpecial2($connect, $leader, $power, $intel);
    } else {
        $specage2 = round((80 - $age)/3) + $age;
        $special2 = 0;
    }
    //내특
    $specage = round((80 - $age)/12) + $age;
    $special = 0;

    if($admin['scenario'] > 0) {
        $specage2 = $age + 3;
        $specage = $age + 3;
    }

    $turntime = getRandTurn($admin['turnterm']);

    $lastconnect = date('Y-m-d H:i:s');
    if($lastconnect >= $turntime) {
        $turntime = addTurn($turntime, $admin['turnterm']);
    }

    //특회 전콘
    if($admin['img'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "" && $pic == 1) {
        $face = $member['picture'];
        $imgsvr = $member['imgsvr'];
    } else {
        $face = "default.jpg";
        $imgsvr = 0;
    }
    //특회
    $userlevel = $member['grade'];

    //성격 랜덤시
    if($character == 11) $character = rand()%10;
    //상성 랜덤
    $npcmatch = rand()%150 + 1;

    ########## 회원정보 테이블에 입력값을 등록한다. ##########
    $query = "
        insert into general (
            user_id, password, name, picture, imgsvr, nation, city, troop, npcmatch,
            leader, power, intel, experience, dedication, gold, rice, crew, train, atmos,
            userlevel, level, turntime, killturn, lastconnect, makelimit, age, startage, personal, specage, special, specage2, special2
        ) values (
            '$id', '$pwTemp', '$name', '$face', '$imgsvr', '0', '$city', '0', '$npcmatch',
            '$leader', '$power', '$intel', '0', '0', '1000', '1000', '0', '0', '0',
            '$userlevel', '0', '$turntime', '6', '$lastconnect', '0', '$age', '$age', '$character', '$specage', '$special', '$specage2', '$special2'
        )";
    $result = MYDB_query($query,$connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "select no,name,history from general where user_id='$id'";
    $result = MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['name'] == "") {
        $r = rand() % 999 + 1;
        $me['name'] = '장수-'.$r;
        
        $query = "update general set name='{$me['name']}' where user_id='$id'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    $cityname = getCity($connect, $city, "name");
    if($genius == 1) {
        $log[0] = "<C>●</>{$admin['month']}월:<G><b>{$cityname['name']}</b></>에서 <Y>{$me['name']}</>(이)라는 기재가 천하에 이름을 알립니다.";
        $log[1] = "<C>●</>{$admin['month']}월:<C>".getGenSpecial($special2)."</> 특기를 가진 <C>천재</>의 등장으로 온 천하가 떠들썩합니다.";

        $history[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【천재】</b></><G><b>{$cityname['name']}</b></>에 천재가 등장했습니다.";
        pushHistory($connect, $history);
    } else {
        $log[0] = "<C>●</>{$admin['month']}월:<G><b>{$cityname['name']}</b></>에서 <Y>{$me['name']}</>(이)라는 호걸이 천하에 이름을 알립니다.";
    }
    $me = addHistory($connect, $me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$me['name']}</>, <G>{$cityname['name']}</>에서 큰 뜻을 품다.");
    $mylog[count($mylog)] = "<C>●</>삼국지 모의전투 PHP의 세계에 오신 것을 환영합니다 ^o^";
    $mylog[count($mylog)] = "<C>●</>처음 하시는 경우에는 <D>도움말</>을 참고하시고,";
    $mylog[count($mylog)] = "<C>●</>문의사항이 있으시면 게시판에 글을 남겨주시면 되겠네요~";
    $mylog[count($mylog)] = "<C>●</>부디 즐거운 삼모전 되시길 바랍니다 ^^";
    $mylog[count($mylog)] = "<C>●</>통솔 <C>$pleader</> 무력 <C>$ppower</> 지력 <C>$pintel</> 의 보너스를 받으셨습니다.";
    $mylog[count($mylog)] = "<C>●</>연령은 <C>$age</>세로 시작합니다.";
    if($genius == 1) {
        $mylog[count($mylog)] = "<C>●</>축하합니다! 천재로 태어나 처음부터 <C>".getGenSpecial($special2)."</> 특기를 가지게 됩니다!";
        $me = addHistory($connect, $me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C>".getGenSpecial($special2)."</> 특기를 가진 천재로 탄생.");
    }
    pushGenLog($me, $mylog);
    pushAllLog($log);

    $adminLog[0] = "가입 : {$name} // {$me['name']} // {$id} // ".getenv("REMOTE_ADDR");
    pushAdminLog($connect, $adminLog);

    MYDB_close($connect);

    echo("<script>
        window.alert('정상적으로 회원 가입되었습니다. ID : $id \n튜토리얼을 꼭 읽어보세요!');
        </script>");
    //echo("<script>location.replace('index.php');</script>");
    echo 'index.php';//TODO:debug all and replace
}

