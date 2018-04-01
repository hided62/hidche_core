<?php
namespace sammo;

include "lib.php";
include "func.php";



$name       = $_POST['name'];
$name       = StringUtil::NoSpecialCharacter($name);
$pic        = Util::array_get($_POST['pic'],'');
$character  = $_POST['character'];

$leader = Util::array_get($_POST['leader'], 50);
$power = Util::array_get($_POST['power'], 50);
$intel = Util::array_get($_POST['intel'], 50);

$mylog = [];

$userID = Session::getUserID();

if(!$userID) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}


//회원 테이블에서 정보확인
$member = RootDB::db()->queryFirstRow("select no,id,picture,imgsvr,grade from MEMBER where no = %i", $userID);

if(!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$date = date('Y-m-d H:i:s');
//등록정보
RootDB::db()->query("update MEMBER set reg_num=reg_num+1,reg_date=%s where no=%i", $date, $userID);

$connect = dbConn();
$db = DB::db();
########## 동일 정보 존재여부 확인. ##########

$admin = $db->queryFirstRow("select year,month,scenario,maxgeneral,turnterm,genius,img from game limit 1");
$gencount = $db->queryFirstField("select count(no) from general where npc<2");
$id_num = $db->queryFirstField("select count(no) from general where owner= %i", $userID);
$name_num = $db->queryFirstField("select count(no) from general where name= %s", $name);

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
} elseif(StringUtil::GetStrLen($name) < 1) {
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
} elseif(StringUtil::GetStrLen($name) > 6) {
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

        $db->query("update game set genius=genius-1");
    } else {
        $genius = 0;
    }

    //중, 소 공백지 개수
    $citycount = Util::toInt($db->queryFirstField("select count(city) from city where level>=5 and level<=6 and nation=0"));

    // 공백지에서만 태어나게
    if($citycount > 0) {
        $city = Util::toInt($db->queryFirstField("select city from city where level>=5 and level<=6 and nation=0 order by rand() limit 0,1"));
    } else {
        $city = Util::toInt($db->queryFirstField("select city from city where level>=5 and level<=6 order by rand() limit 0,1"));
    }

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
    if($admin['show_img_level'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "" && $pic == 1) {
        $face = $member['picture'];
        $imgsvr = $member['imgsvr'];
    } else {
        $face = "default.jpg";
        $imgsvr = 0;
    }

    //성격 랜덤시
    if($character == 11) $character = rand()%10;
    //상성 랜덤
    $affinity = rand()%150 + 1;

    ########## 회원정보 테이블에 입력값을 등록한다. ##########
    $db->insert('general', [
        'owner' => $userID,
        'name' => $name,
        'picture' => $face,
        'imgsvr' => $imgsvr,
        'nation' => 0,
        'city' => $city,
        'troop' => 0,
        'affinity' => $affinity,
        'leader' => $leader,
        'power' => $power,
        'intel' => $intel,
        'experience' => 0,
        'dedication' => 0,
        'gold' => 1000,
        'rice' => 1000,
        'crew' => 0,
        'train' => 0,
        'atmos' => 0,
        'level' => 0,
        'turntime' => $turntime,
        'killturn' => 6,
        'lastconnect' => $lastconnect,
        'makelimit' => 0,
        'age' => $age,
        'startage' => $age,
        'personal' => $character,
        'specage' => $specage,
        'special' => $special,
        'specage2' => $specage2,
        'special2' => $special2
    ]);

    $me = $db->queryFirstRow("select no,name from general where owner= %i", $userID);

    if($me['name'] == "") {
        $r = rand() % 999 + 1;
        $me['name'] = '장수-'.$r;
        
        $db->query("update general set name=%s where owner=%i", $me['name'], $userID);
    }
    $cityname = getCity($connect, $city, "name");
    if($genius == 1) {
        $log[0] = "<C>●</>{$admin['month']}월:<G><b>{$cityname['name']}</b></>에서 <Y>{$me['name']}</>(이)라는 기재가 천하에 이름을 알립니다.";
        $log[1] = "<C>●</>{$admin['month']}월:<C>".getGenSpecial($special2)."</> 특기를 가진 <C>천재</>의 등장으로 온 천하가 떠들썩합니다.";

        $history[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【천재】</b></><G><b>{$cityname['name']}</b></>에 천재가 등장했습니다.";
        pushWorldHistory($history, $admin['year'], $admin['month']);
    } else {
        $log[0] = "<C>●</>{$admin['month']}월:<G><b>{$cityname['name']}</b></>에서 <Y>{$me['name']}</>(이)라는 호걸이 천하에 이름을 알립니다.";
    }
    addHistory($me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$me['name']}</>, <G>{$cityname['name']}</>에서 큰 뜻을 품다.");
    $mylog[] = "<C>●</>삼국지 모의전투 PHP의 세계에 오신 것을 환영합니다 ^o^";
    $mylog[] = "<C>●</>처음 하시는 경우에는 <D>도움말</>을 참고하시고,";
    $mylog[] = "<C>●</>문의사항이 있으시면 게시판에 글을 남겨주시면 되겠네요~";
    $mylog[] = "<C>●</>부디 즐거운 삼모전 되시길 바랍니다 ^^";
    $mylog[] = "<C>●</>통솔 <C>$pleader</> 무력 <C>$ppower</> 지력 <C>$pintel</> 의 보너스를 받으셨습니다.";
    $mylog[] = "<C>●</>연령은 <C>$age</>세로 시작합니다.";
    if($genius == 1) {
        $mylog[] = "<C>●</>축하합니다! 천재로 태어나 처음부터 <C>".getGenSpecial($special2)."</> 특기를 가지게 됩니다!";
        addHistory($me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C>".getGenSpecial($special2)."</> 특기를 가진 천재로 탄생.");
    }
    pushGenLog($me, $mylog);
    pushGeneralPublicRecord($log, $admin['year'], $admin['month']);

    $adminLog[0] = "가입 : {$name} // {$me['name']} // {$id} // ".getenv("REMOTE_ADDR");
    pushAdminLog($adminLog);

    MYDB_close($connect);

    echo("<script>
        window.alert('정상적으로 회원 가입되었습니다. ID : $id \n튜토리얼을 꼭 읽어보세요!');
        </script>");
    //echo("<script>location.replace('index.php');</script>");
    echo 'index.php';//TODO:debug all and replace
}

