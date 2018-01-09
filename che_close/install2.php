<?
  require "lib.php";
?>

<html>
<head>
    <meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
    <link rel=StyleSheet HREF=style.css type=text/css title=style>
</head>
<body bgcolor=#000000 text=#ffffff>
<br><br><br>
<div align=center>
<table cellpadding=0 cellspacing=0 width=600 border=0>
    <tr>
        <td height=30 colspan=3><img src=<?=$images;?>/inst_top.gif></td>
    </tr>
    <tr>
        <td>
            <br>
        </td>
    </tr>
</table>

<table border=0 cellpadding=2 cellspacing=0 width=600>
<form name=write method=post action=install2_ok.php>

    <tr>
        <td colspan=2>
            <img src=<?=$images;?>/inst_step3.gif>
        </td>
    </tr>
    <tr>
        <td align=right style=font-family:Tahoma;font-size:8pt>TurnTerm&nbsp;</td>
        <td>
            <input type=text name=turnterm size=20 value='1' maxlength=20 style=font-family:Tahoma;font-size:8pt>0:120분, 1:60분, 2:30분, 3:20분, 4:10분, 5:5분, 6:2분, 7:1분
        </td>
    </tr>

    <tr>
        <td align=right style=font-family:Tahoma;font-size:8pt>TurnTerm&nbsp;</td>
        <td>
            <input type=text name=sync size=20 value='0' maxlength=20 style=font-family:Tahoma;font-size:8pt>0:무동기, 1:13시 시작으로 동기
        </td>
    </tr>

    <tr>
        <td align=right style=font-family:Tahoma;font-size:8pt>시나리오</td>
        <td>
            <input type=text name=scenario size=20 value='0' maxlength=20 style=font-family:Tahoma;font-size:8pt>
        </td>
    </tr>

    <tr>
        <td align=right style=font-family:Tahoma;font-size:8pt>가상상성</td>
        <td>
            <input type=text name=fiction size=20 value='0' maxlength=20 style=font-family:Tahoma;font-size:8pt>0:사실상성, 1:가상상성
        </td>
    </tr>

    <tr>
        <td align=right style=font-family:Tahoma;font-size:8pt>확장장수</td>
        <td>
            <input type=text name=extend size=20 value='0' maxlength=20 style=font-family:Tahoma;font-size:8pt>0:비확장, 1:확장장수등장
        </td>
    </tr>

    <tr>
        <td align=right style=font-family:Tahoma;font-size:8pt>NPC유저</td>
        <td>
            <input type=text name=npcmode size=20 value='0' maxlength=20 style=font-family:Tahoma;font-size:8pt>0:불가능, 1:가능
        </td>
    </tr>

    <tr>
        <td align=right style=font-family:Tahoma;font-size:8pt>이미지&nbsp;</td>
        <td>
            <input type=text name=img size=20 value='0' maxlength=20 style=font-family:Tahoma;font-size:8pt>0:없음, 1:전콘, 2:병종, 3:엔장
        </td>
    </tr>

    <tr>
        <td align=right style=font-family:Tahoma;font-size:8pt>시나리오 종류</td>
        <td>
            역사모드<br>
             0 : 180년 공백지<br>
             1 : 184년 황건적의 난<br>
             2 : 190년 반동탁연합<br>
             3 : 194년 군웅할거<br>
             4 : 196년 황제는 허도로<br>
             5 : 200년 관도대전<br>
             6 : 202년 원가의 분열<br>
             7 : 207년 적벽대전<br>
             8 : 213년 익주 공방전<br>
             9 : 219년 삼국정립<br>
            10 : 225년 칠종칠금<br>
            11 : 228년 출사표<br>
            IF모드<br>
            12 : 191년 백마장군의 위세<br>

            가상모드<br>
            20 : 180년 영웅 난무<br>
            21 : 180년 영웅 집결<br>
            22 : 179년 훼신 집결<br>
            23 : 180년 영웅 시대<br>
            24 : 180년 결사항전<br>
            25 : 180년 영웅독존<br>
            26 : 180년 무풍지대<br>
            27 : 180년 가요대잔치<br>
            28 : 180년 확산성 밀리언 아서<br>
        </td>
    </tr>

    <tr>
        <td align=center colspan=2><br><br>
            <input type=image src=<?=$images;?>/inst_b_3.gif border=0 align=absmiddle>
        </td>
    </tr>
</form>
</table>
</body>
</html>

