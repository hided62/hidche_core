<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$admin = getAdmin($connect);

$query = "select userlevel from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['userlevel'] < 5) {
    //echo "<script>location.replace('_admin1.php');</script>";
    echo '_admin1.php';//TODO:debug all and replace
}

switch($btn) {
    case "변경":
        $msg = addslashes(SQ2DQ($msg));
        $query = "update game set msg='$msg' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "요청":
        $query = $q;
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "로그쓰기":
        $lognum = $admin['historyindex'] + 1;
        if($lognum >= 29) { $lognum = 0; }
        $history[0] = "<R>★</><S>{$log}</>";
        pushHistory($connect, $history);
        break;
    case "변경1":
        $query = "update game set starttime='$starttime' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "변경2":
        $query = "update game set maxgeneral='$maxgeneral' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "변경3":
        $query = "update game set maxnation='$maxnation' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "변경4":
        $query = "update game set startyear='$startyear' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "변경5":
        $query = "update game set normgeneral='$gen_rate' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "1분턴":
    case "2분턴":
    case "5분턴":
    case "10분턴":
    case "20분턴":
    case "30분턴":
    case "60분턴":
    case "120분턴":
        switch($btn) {
        case   "1분턴": $turnterm = 7; $unit =   60; break;
        case   "2분턴": $turnterm = 6; $unit =  120; break;
        case   "5분턴": $turnterm = 5; $unit =  300; break;
        case  "10분턴": $turnterm = 4; $unit =  600; break;
        case  "20분턴": $turnterm = 3; $unit = 1200; break;
        case  "30분턴": $turnterm = 2; $unit = 1800; break;
        case  "60분턴": $turnterm = 1; $unit = 3600; break;
        case "120분턴": $turnterm = 0; $unit = 7200; break;
        }
        $turn = ($admin['year'] - $admin['startyear']) * 12 + $admin['month'] - 1;
        $starttime = date("Y-m-d H:i:s", strtotime($admin['turntime']) - $turn * $unit);
        $starttime = cutTurn($starttime, $turnterm);
        $query = "update game set turnterm='$turnterm',starttime='$starttime' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 턴시간이 길어지는 경우 랜덤턴 배정
        if($turnterm < $admin['turnterm']) {
            $query = "select no from general";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $count = MYDB_num_rows($result);
            for($i=0; $i < $count; $i++) {
                $gen = MYDB_fetch_array($result);
                $turntime = getRandTurn($turnterm);
                $query = "update general set turntime='$turntime' where no='{$gen['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        // 턴시간이 너무 멀리 떨어진 선수 제대로 보정
        } else {
            $query = "select no,turntime from general";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $count = MYDB_num_rows($result);
            for($i=0; $i < $count; $i++) {
                $gen = MYDB_fetch_array($result);
                $num = floor((strtotime($gen['turntime']) - strtotime($admin['turntime'])) / $unit);
                if($num > 0) {
                    $gen['turntime'] = date("Y-m-d H:i:s", strtotime($gen['turntime']) - $unit * $num);
                    $query = "update general set turntime='{$gen['turntime']}' where no='{$gen['no']}'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                }
            }
        }
        $history[count($history)] = "<R>★</>턴시간이 <C>$btn</>으로 변경됩니다.";
        pushHistory($connect, $history);
        break;
    case "변경6":
        $query = "update game set att0='$att0',def0='$def0',spd0='$spd0',avd0='$avd0',ric0='$ric0',cst0='$cst0' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att1='$att1',def1='$def1',spd1='$spd1',avd1='$avd1',ric1='$ric1',cst1='$cst1' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att2='$att2',def2='$def2',spd2='$spd2',avd2='$avd2',ric2='$ric2',cst2='$cst2' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att3='$att3',def3='$def3',spd3='$spd3',avd3='$avd3',ric3='$ric3',cst3='$cst3' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att4='$att4',def4='$def4',spd4='$spd4',avd4='$avd4',ric4='$ric4',cst4='$cst4' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att5='$att5',def5='$def5',spd5='$spd5',avd5='$avd5',ric5='$ric5',cst5='$cst5' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update game set att10='$att10',def10='$def10',spd10='$spd10',avd10='$avd10',ric10='$ric10',cst10='$cst10' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att11='$att11',def11='$def11',spd11='$spd11',avd11='$avd11',ric11='$ric11',cst11='$cst11' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att12='$att12',def12='$def12',spd12='$spd12',avd12='$avd12',ric12='$ric12',cst12='$cst12' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att13='$att13',def13='$def13',spd13='$spd13',avd13='$avd13',ric13='$ric13',cst13='$cst13' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att14='$att14',def14='$def14',spd14='$spd14',avd14='$avd14',ric14='$ric14',cst14='$cst14' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update game set att20='$att20',def20='$def20',spd20='$spd20',avd20='$avd20',ric20='$ric20',cst20='$cst20' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att21='$att21',def21='$def21',spd21='$spd21',avd21='$avd21',ric21='$ric21',cst21='$cst21' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att22='$att22',def22='$def22',spd22='$spd22',avd22='$avd22',ric22='$ric22',cst22='$cst22' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att23='$att23',def23='$def23',spd23='$spd23',avd23='$avd23',ric23='$ric23',cst23='$cst23' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att24='$att24',def24='$def24',spd24='$spd24',avd24='$avd24',ric24='$ric24',cst24='$cst24' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att25='$att25',def25='$def25',spd25='$spd25',avd25='$avd25',ric25='$ric25',cst25='$cst25' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att26='$att26',def26='$def26',spd26='$spd26',avd26='$avd26',ric26='$ric26',cst26='$cst26' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att27='$att27',def27='$def27',spd27='$spd27',avd27='$avd27',ric27='$ric27',cst27='$cst27' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update game set att30='$att30',def30='$def30',spd30='$spd30',avd30='$avd30',ric30='$ric30',cst30='$cst30' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att31='$att31',def31='$def31',spd31='$spd31',avd31='$avd31',ric31='$ric31',cst31='$cst31' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att32='$att32',def32='$def32',spd32='$spd32',avd32='$avd32',ric32='$ric32',cst32='$cst32' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att33='$att33',def33='$def33',spd33='$spd33',avd33='$avd33',ric33='$ric33',cst33='$cst33' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att34='$att34',def34='$def34',spd34='$spd34',avd34='$avd34',ric34='$ric34',cst34='$cst34' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att35='$att35',def35='$def35',spd35='$spd35',avd35='$avd35',ric35='$ric35',cst35='$cst35' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att36='$att36',def36='$def36',spd36='$spd36',avd36='$avd36',ric36='$ric36',cst36='$cst36' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att37='$att37',def37='$def37',spd37='$spd37',avd37='$avd37',ric37='$ric37',cst37='$cst37' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att38='$att38',def38='$def38',spd38='$spd38',avd38='$avd38',ric38='$ric38',cst38='$cst38' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update game set att40='$att40',def40='$def40',spd40='$spd40',avd40='$avd40',ric40='$ric40',cst40='$cst40' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att41='$att41',def41='$def41',spd41='$spd41',avd41='$avd41',ric41='$ric41',cst41='$cst41' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att42='$att42',def42='$def42',spd42='$spd42',avd42='$avd42',ric42='$ric42',cst42='$cst42' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update game set att43='$att43',def43='$def43',spd43='$spd43',avd43='$avd43',ric43='$ric43',cst43='$cst43' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
}

//echo "<script>location.replace('_admin1.php');</script>";
echo '_admin1.php';//TODO:debug all and replace

