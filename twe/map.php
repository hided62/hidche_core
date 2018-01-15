<?php
include "lib.php";

if($graphic == 2) {
    echo "
<body bgcolor=black leftmargin=0 marginwidth=0 topmargin=0 marginheight=0 oncontextmenu='return false' onselectstart='return false' ondragstart='return false'>
<font color=white>
지 도 생 략<br>
(개인설정 또는 과부하로 인한 서버의 긴급모드)
</font>
</body>
";
    exit();
}
$connect=dbConn();
//<link rel=stylesheet href=stylesheet.php type=text/css>
?>

<html>
<head>
<title>지도</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<script type="text/javascript">
function cityselect(city) {
    parent.document.form1.double.value = city;
}

function showLayer(obj, city, nation, event) {
    x1 = event.clientX;         x2 = event.clientX;
    y1 = event.clientY + 10;    y2 = event.clientY + 28;
    layerObj1 = document.getElementById("city_name");
    layerObj2 = document.getElementById("nation_name");
    layerObj1.style.left = x1;  layerObj2.style.left = x2;
    layerObj1.style.top  = y1;  layerObj2.style.top  = y2;

    cityname = city.substr(6, city.length-1);
    if(cityname == "남만" || cityname == "남영" || cityname == "교지" || cityname == "남해" || cityname == "대") {
        layerObj1.style.top  = y1 - 60;
        layerObj2.style.top  = y2 - 60;
    } else if(cityname == "오환" || cityname == "졸본" || cityname == "평양" || cityname == "위례" || cityname == "사비"
            || cityname == "계림" || cityname == "탐라" || cityname == "이도" || cityname == "왜" || cityname == "유구") {
        layerObj1.style.left = x1 - 110;
        layerObj2.style.left = x2 - 110;
    } else {
        layerObj1.style.left = x1 + 10;
        layerObj2.style.left = x2 + 10;
    }
    document.city_form.city.value = city;
    document.nation_form.nation.value = nation;
    if(nation == "") { layerObj2.style.visibility = "hidden"; }
    else             { layerObj2.style.visibility = "visible"; }
    layerObj1.style.visibility = "visible";
}

function hideLayer() {
    layerObj1 = document.getElementById("city_name");
    layerObj2 = document.getElementById("nation_name");

    document.city_form.city.value = "";
    document.nation_form.nation.value = "";

    layerObj1.style.visibility = "hidden";
    layerObj2.style.visibility = "hidden";
}
</script>
<?php require('analytics.php'); ?>
</head>
<body bgcolor=black leftmargin=0 marginwidth=0 topmargin=0 marginheight=0 oncontextmenu='return false'>
<div id=city_name class=balloon style=background-color:1EA4FF;position:absolute;visibility:hidden;z-index:6;>
        <table border=1 cellspacing=0 cellpadding=0>
            <form name=city_form>
                <tr><td style=border-width:0;><input type=text size=13 style=background-color:1EA4FF;color:white;border:0; name=city></td></tr>
            </form>
        </table>
</div>
<div id=nation_name class=balloon style=background-color:1EA4FF;position:absolute;visibility:hidden;z-index:6;>
        <table border=1 cellspacing=0 cellpadding=0>
            <form name=nation_form>
                <tr><td style=border-width:0;><input type=text size=13 style=background-color:1EA4FF;color:white;text-align:right;border:0; name=nation></td></tr>
            </form>
        </table>
</div>
<?php showMap($connect, $type, $graphic); ?>
</body>
</html>

<?php
function showMap($connect, $type, $graphic) {
    global $images;

    if(strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE')) {
        if(strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 10')) {
            $brouserIE = 0;
        } else {
            $brouserIE = 1;
        }
    } else {
        $brouserIE = 0;
    }

    // 맵
    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error("showMap ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,nation,userlevel,level,city from general where user_id='$_SESSION['p_id']'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select spy from nation where nation='$me['nation']'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $myNation = MYDB_fetch_array($result);

    //도시수
    $allcount = 94;

    if($admin['year'] < $admin['startyear'] + 1) {
        $color = "magenta";
    } elseif($admin['year'] < $admin['startyear'] + 2) {
        $color = "orange";
    } elseif($admin['year'] < $admin['startyear'] + 3) {
        $color = "yellow";
    } else {
        $color = "white";
    }
    if($admin['month'] < 4) {         $season = "spring"; }
    elseif($admin['month'] < 7) {     $season = "summer"; }
    elseif($admin['month'] < 10) {    $season = "fall";   }
    else {                          $season = "winter"; }
    if($admin['month'] < 4) {         $mapType = "map1"; }
    elseif($admin['month'] < 7) {     $mapType = "map2"; }
    elseif($admin['month'] < 10) {    $mapType = "map3"; }
    else {                          $mapType = "map4"; }
    if($graphic == 0) {
        $ltitle = "<img src={$images}/ltitle.jpg></img>";
        $ad = "background={$images}/ad.gif";
        $season = "background={$images}/{$season}.gif";
        $rtitle = "<img src={$images}/rtitle.jpg></img>";
    } else {
        $ltitle = "&nbsp;";
        $ad = "";
        $season = "";
        $rtitle = ""&nbsp;";";
    }
    echo "
<table border=0 width=700 cellpadding=0 cellspacing=0 style=font-size:13;>
    <tr height=20>
        <td width=268 align=left>{$ltitle}</td>
        <td width=38 {$ad}></td>
        <td width=98>&nbsp;<font color={$color}><b>{$admin['year']}年 {$admin['month']}月</b></font></td>
        <td width=18 {$season}></td>
        <td width=268 align=right>{$rtitle}</td>
    </tr>
    <tr>
        <td width=700 height=500 colspan=5 background={$images}/{$mapType}.jpg valign=top>
            <div style=position:relative;font-size:0;>";

    $query = "select nation,name,color,capital from nation";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=1; $i <= $count; $i++) {
        $nation = MYDB_fetch_array($result);
        $nationcolor[$nation['nation']] = $nation['color'];
        $nationname[$nation['nation']] = $nation['name'];
        $nationCapital[$nation['nation']] = $nation['capital'];
    }

    if($type == 0) {
        // 운영자의 경우 다 볼 수 있음
        if($me['userlevel'] >= 5) {
            for($i=1; $i <= $allcount; $i++) {
                $valid[$i] = 1;
            }
        } elseif($me['level'] == 0) {
            // 재야는 내 도시만
            $query = "select city,name,nation from city where city='$me['city']'";
            $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $cityvalid = MYDB_fetch_array($cityresult);
            $valid[$cityvalid['city']] = 1;
        } else {
            // 아국 도시
            $query = "select city,name from city where nation='$me['nation']'";
            $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $citycount = MYDB_num_rows($cityresult);
            for($i=0; $i < $citycount; $i++) {
                $cityvalid = MYDB_fetch_array($cityresult);
                $valid[$cityvalid['city']] = 1;
            }
            // 아국 장수가 있는 타국 도시들 선택
            $query = "select distinct A.city,B.name,B.nation from general A,city B where A.city=B.city and A.nation='$me['nation']' and B.nation!='$me['nation']'";
            $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $citycount = MYDB_num_rows($cityresult);
            for($i=0; $i < $citycount; $i++) {
                $cityvalid = MYDB_fetch_array($cityresult);
                $valid[$cityvalid['city']] = 1;
            }
            // 첩보된 도시
            $citys = explode("|", $myNation['spy']);
            for($i=0; $i < count($citys); $i++) {
                $valid[floor($citys[$i]/10)] = 1;
            }
        }
    }

    $x = array(
//   업 허창 낙양 장안 성도 양양 건업 북평 남피   완 수춘 서주 강릉 장사 시상 위례
    345, 330, 275, 145,  25, 255, 505, 465, 395, 270, 395, 440, 245, 255, 360, 620,
//   계 복양 진류 여남 하비 서량 하내 한중 상용 덕양 강주 건녕 남해 계양   오 평양
    365, 410, 365, 330, 480,  25, 230, 135, 185,  85,  70,  80, 245, 230, 510, 590,
// 사비 계림 진양 평원 북해   초   패 천수 안정 홍농 하변 자동 영안 귀양 주시 운남
    605, 655, 295, 440, 470, 365, 430,  70,  95, 210,  45,  75, 115,  90,  30,  30,
// 남영 교지 신야 강하 무릉 영릉 상동 여강 회계 고창   대 안평 졸본 이도   강   저
    135, 130, 250, 315, 195, 190, 210, 380, 480, 350, 450, 530, 570, 680,  95,  25,
// 흉노 남만 산월 오환   왜 호관 호로 사곡 함곡 사수 양평 가맹 역경 계교 동황 관도
    180,  80, 425, 610, 680, 285, 285, 240, 180, 310,  90,  45, 410, 405, 515, 340,
// 정도 합비 광릉 적도 가정 기산 면죽 이릉 장판 백랑 적벽 파양 탐라 유구
    400, 435, 490, 130,  40, 110,  35, 215, 280, 530, 330, 430, 605, 625
    );

    $y = array(
//   업 허창 낙양 장안 성도 양양 건업 북평 남피   완 수춘 서주 강릉 장사 시상 위례
    130, 215, 180, 165, 290, 290, 305,  65,  95, 235, 270, 250, 335, 375, 360, 145,
//   계 복양 진류 여남 하비 서량 하내 한중 상용 덕양 강주 건녕 남해 계양   오 평양
     35, 170, 185, 260, 235,  50, 150, 205, 225, 275, 310, 400, 480, 400, 345, 100,
// 사비 계림 진양 평원 북해   초   패 천수 안정 홍농 하변 자동 영안 귀양 주시 운남
    205, 200,  60, 115, 155, 230, 220, 105, 145, 175, 190, 245, 295, 360, 345, 415,
// 남영 교지 신야 강하 무릉 영릉 상동 여강 회계 고창   대 안평 졸본 이도   강   저
    405, 480, 260, 295, 355, 395, 435, 315, 395, 405, 480,  80,  65, 260,  35, 120,
// 흉노 남만 산월 오환   왜 호관 호로 사곡 함곡 사수 양평 가맹 역경 계교 동황 관도
     95, 455, 430,  20, 320, 140, 205, 175, 175, 185, 220, 225,  65, 135, 145, 165,
// 정도 합비 광릉 적도 가정 기산 면죽 이릉 장판 백랑 적벽 파양 탐라 유구
    210, 285, 275,  75, 160, 180, 255, 295, 315, 30, 325, 350, 260, 435
    );

    $query = "select name,level,nation,city,state,region,supply from city";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    for($i=0; $i < $allcount; $i++) {
        $city = MYDB_fetch_array($result);
        $name = $city['name'];
        $nation = $nationname[$city['nation']];
        $color = $nationcolor[$city['nation']];
        $capital = $nationCapital[$city['nation']];
        $level = $city['level'];
        $state = $city['state'];
        $region = $city['region'];

        if($me['city'] == $city['city']) { $myCity = 1; }
        else                         { $myCity = 0; }

        if($capital == $city['city']) { $cap = 1; }
        else                        { $cap = 0; }

        city($graphic, $brouserIE,
            $type, $name, $nation, $color, $level, $region, $i+1, $valid[$i+1], $x[$i], $y[$i],
            $state, $myCity, $city['supply'], $cap);
    }

    echo "
            </div>
        </td>
    </tr>
</table>
";
}

function city($graphic, $brouser, $type, $name, $nation, $color, $level, $region, $city, $valid, $x, $y, $state, $mycity, $supply, $capital) {
    global $images;

    if($graphic == 0) {
        switch($level) {
        case 1: $width = 16; $height = 15; break;
        case 2: $width = 20; $height = 14; break;
        case 3: $width = 14; $height = 14; break;
        case 4: $width = 20; $height = 15; break;
        case 5: $width = 24; $height = 16; break;
        case 6: $width = 26; $height = 18; break;
        case 7: $width = 28; $height = 20; break;
        case 8: $width = 32; $height = 24; break;
        }
    } else {
        switch($level) {
        case 1: $width = 12; $height = 12; break;
        case 2: $width = 12; $height = 12; break;
        case 3: $width = 14; $height = 14; break;
        case 4: $width = 16; $height = 14; break;
        case 5: $width = 18; $height = 16; break;
        case 6: $width = 20; $height = 16; break;
        case 7: $width = 22; $height = 18; break;
        case 8: $width = 24; $height = 18; break;
        }
    }
    $x -= floor($width  / 2);
    $y -= floor($height / 2);

    $backwidth = floor($width * 3.0);  $backheight = floor($height * 3.0);

    $backx = $x - floor(($backwidth - $width)/2);
    $backy = $y - floor(($backheight - $height)/2);
    $fx = $x + $width - 6;
    $fy = $y - 3;
    $cx = $x + $width - 4;
    $cy = $y - 2;
    $ex = $x - 5;
    $ey = $y;

    // 0 명령화면의 지도(본국 장수 있는 도시 링크),
    // 1 전쟁,이동,계략시 지도(전도시 클릭되게),
    // 2 사령부 태수임명(본국만 클릭되게)

    // 0인 경우 현재도시 링크 생성
    if($type == 0 && $valid == 1) { echo "<form name=form$city method=post target=_parent action=b_currentCity.php>"; }
    //메인화면
    if($graphic == 0) {
        if($type == 0 && $valid == 1) {
            $tag   = "input type=image";
            $tail  = "><input type=hidden name=citylist value=$city";
        //명령중
        } elseif($type == 1) {
            $tag   = "img";
            $tail  = "onclick='cityselect($city)'";
        } else {
            $tag   = "img";
            $tail  = "";
        }
        //성표시
        $mid = "src={$images}/cast_{$level}.gif style=width:{$width};height:{$height}; onmousemove='showLayer(this,\"".getCityLevel($region, $level)."$name\",\"$nation\", event)' onmouseout='hideLayer()'";
        echo "<div name=city$city style=position:absolute;left:{$x}px;top:{$y}px;z-index:2;font-size:1;><{$tag} {$mid} {$tail}></div>";
        //재난표시
        if($state > 0) {
            $mid2  = "src={$images}/event{$state}.gif style=width:15;height:15; onmousemove='showLayer(this,\"".getCityLevel($region, $level)."$name\",\"$nation\", event)' onmouseout='hideLayer()'";
            echo "<div name=state$city style=position:absolute;left:{$ex}px;top:{$ey}px;z-index:3;font-size:1;><{$tag} {$mid2} {$tail}></div>";
        }
    } else {
        //메인화면
        if($type == 0 && $valid == 1) {
            $tail  = " onclick='form{$city}.submit()'><input type=hidden name=citylist value=$city";
        //명령중
        } elseif($type == 1) {
            $tail  = "onclick='cityselect($city)'";
        } else {
            $tail  = "";
        }
        if($color != "") {
            $color2 = $color;
        } else {
            $color2 = "white";
        }
        //성표시
        $mid = "width:{$width};height:{$height};background-color:{$color2}; onmousemove='showLayer(this,\"".getCityLevel($region, $level)."$name\",\"$nation\", event)' onmouseout='hideLayer()'";
        echo "<div name=city$city style=position:absolute;left:{$x}px;top:{$y}px;z-index:2;font-size:1;{$mid} {$tail}></div>";
        //재난표시
        if($state > 0) {
            if($state < 10) { $color3 = "blue"; }
            elseif($state < 40) { $color3 = "orange"; }
            elseif($state < 50) { $color3 = "red"; }
            $mid2  = "width:10;height:10;background-color:{$color3}; onmousemove='showLayer(this,\"".getCityLevel($region, $level)."$name\",\"$nation\", event)' onmouseout='hideLayer()'";
            echo "<div name=state$city style=position:absolute;left:{$ex}px;top:{$ey}px;z-index:3;font-size:1;{$mid2} {$tail}></div>";
        }
    }
    //공백지 아니면
    if($color != "") {
        if($graphic == 0) {
            //배경국가색
            if($brouser == 1) {
                echo "<div name=back$city style=position:absolute;left:{$backx}px;top:{$backy}px;z-index:1;background-color:$color;filter:alpha(Opacity:100,style:2,finishOpacity:0);width:{$backwidth};height:{$backheight};></div>";
            } else {
                echo "
                    <div name=backlayer$city style=position:absolute;left:{$backx}px;top:{$backy}px;z-index:1;width:{$backwidth};height:{$backheight};>
                        <img src={$images}/b{$color}.png style=width:{$backwidth};height:{$backheight};></img>
                    </div>";
            }
            //깃발표시
            if($supply == 1) { $mid3  = "src={$images}/f{$color}.gif style=width:12;height:12; onmousemove='showLayer(this,\"".getCityLevel($region, $level)."$name\",\"$nation\", event)' onmouseout='hideLayer()'"; }
            else             { $mid3  = "src={$images}/d{$color}.gif style=width:12;height:12; onmousemove='showLayer(this,\"".getCityLevel($region, $level)."$name\",\"$nation\", event)' onmouseout='hideLayer()'"; }
            echo "<div name=flag$city style=position:absolute;left:{$fx}px;top:{$fy}px;z-index:4;><{$tag} {$mid3} {$tail}></div>";
            //수도표시
            if($capital == 1) {
                $mid4  = "src={$images}/event51.gif style=width:10;height:10; onmousemove='showLayer(this,\"".getCityLevel($region, $level)."$name\",\"$nation\", event)' onmouseout='hideLayer()'";
                echo "<div name=cap$city style=position:absolute;left:{$cx}px;top:{$cy}px;z-index:5;><{$tag} {$mid4} {$tail}></div>";
            }
        } else {
            //수도표시
            if($capital == 1) {
                echo "<div name=cap$city style=position:absolute;left:{$cx}px;top:{$cy}px;z-index:5;width:5;height:5;background-color:yellow; onmousemove='showLayer(this,\"".getCityLevel($region, $level)."$name\",\"$nation\", event)' onmouseout='hideLayer()';></div>";
            }
        }
    }
    // 0인 경우 현재도시 링크 생성했던거 종료
    if($type == 0 && $valid == 1) {
        echo "</form>";
    }

    // 내 도시인 경우 빨간 네모 그리기
    if($mycity == 1) {
        $width += 8;
        $height += 8;
        $x -= 4;
        $y -= 4;

        echo "
            <div>
                <table border=4 bordercolor=red cellspacing=0 style=position:absolute;left:{$x}px;top:{$y}px;z-index:1;width:{$width};height:{$height};border-style:dotted;font-size:1;>
                    <tr><td></td></tr>
                </table>
            </div>";
    }
}

function getCityLevel($region, $level) {
    switch($region) {
        case 1: $str = "【하북|"; break;
        case 2: $str = "【중원|"; break;
        case 3: $str = "【서북|"; break;
        case 4: $str = "【서촉|"; break;
        case 5: $str = "【남중|"; break;
        case 6: $str = "【　초|"; break;
        case 7: $str = "【오월|"; break;
        case 8: $str = "【동이|"; break;
    }

    switch($level) {
        case 1: $str .= "수】"; break;
        case 2: $str .= "진】"; break;
        case 3: $str .= "관】"; break;
        case 4: $str .= "이】"; break;
        case 5: $str .= "소】"; break;
        case 6: $str .= "중】"; break;
        case 7: $str .= "대】"; break;
        case 8: $str .= "특】"; break;
    }

    return $str;
}

