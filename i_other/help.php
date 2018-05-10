<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

WebUtil::setHeaderNoCache();
$category = Util::getReq('category', 'int', 0);
//FIXME: 겨우 category 구분을 위해 php를 써야하는가? JavaScript로 바꾸자
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>튜토리얼</title>
<link href="../d_shared/common.css" rel="stylesheet">
<link href="css/common.css?180511" rel="stylesheet">
<style type="text/css">

.intro {
  font-size: 15px;
  color: orange;
}

.title {
  font-size: 20px;
  font-weight: bold;
  color: cyan;
}

.bullet {
  font-weight: bold;
  color: orange;
}

.leftFloat {
  float: left;
}

.rightFloat {
  float: right;
}

.clear {
  clear: both;
}

</style>

    </head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><font size=5 color=skyblue><b>도 움 말</b></font></td></tr>
</table>
<table class='tb_layout bg1'>
    <tr>
        <td align=center><input type=button style=background-color:<?=$category==0?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='시작하기' onclick=location.replace('help.php?category=0')></td>
        <td align=center><input type=button style=background-color:<?=$category==1?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='회원가입' onclick=location.replace('help.php?category=1')></td>
        <td align=center><input type=button style=background-color:<?=$category==2?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='접속관리' onclick=location.replace('help.php?category=2')></td>
        <td align=center><input type=button style=background-color:<?=$category==3?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='캐릭터생성' onclick=location.replace('help.php?category=3')></td>
        <td align=center><input type=button style=background-color:<?=$category==4?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='명령입력' onclick=location.replace('help.php?category=4')></td>
        <td align=center><input type=button style=background-color:<?=$category==5?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='인터페이스' onclick=location.replace('help.php?category=5')></td>
        <td align=center><input type=button style=background-color:<?=$category==6?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='일반장수' onclick=location.replace('help.php?category=6')></td>
        <td align=center><input type=button style=background-color:<?=$category==7?"red":"#225500"?>;color:white;width:123px;height:50px;font-weight:bold;font-size:13px; value='FAQ' onclick=location.replace('help.php?category=7')></td>
    </tr>
</table>



<?php
if ($category == 0) {
    ?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td height=50 bgcolor=yellow align=center><b><font color=black size=5>시 작 하 기</font></b></td></tr>
    <tr>
        <td>
<font class=intro>◈ 본 튜토리얼은 『삼국지 모의전투』(이하 삼모전) 『HiDCHe』(구 유기체서버, 이하 체섭)를 처음 접하시는 초보 유저(이하 뉴비)분들을 위한 길잡이입니다^^ 위에서부터 아래로 읽어나가며 따라하다보면 금방 뉴비신세는 벗어날 수 있답니다~ 세부사항은 『레퍼런스 게시판』을 참고하시면 됩니다! 그럼 시작해볼까요?<br>
<br>
◈ 우선 예약턴제 전략 웹게임인 『체섭』의 컨셉 &amp; 모토를 이해해봅시다.</font><br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_01_01.jpg class=leftFloat>
<font class=title>『웹게임』이란?</font><br>
　<font class=bullet>☞</font> 온라인게임과 달리 특별한 프로그램 설치 없이도 인터넷 브라우저상에서 바로 즐길 수 있는 게임들을 칭합니다. 그렇기 때문에 어딜가든 인터넷이 되는 곳이라면 언제든지 접속해서 플레이가 가능합니다. 심지어 모바일기기(휴대폰, PMP)에서도 가능합니다!<br>
<br>
<font class=title>『예약턴제 전략 웹게임』?</font><br>
　<font class=bullet>☞</font> 『갱신』(새로고침)에 의해 새로운 정보가 나오고, 또한 실행할 명령을 예약하고 실시간으로 명령이 진행되는 독특한 개념의 게임입니다. <br>
<br>
　<font class=bullet>☞</font> 미리 자신이 예약해둔 명령에 따라 본인이 접속하고 있지 않더라도 게임의 진행에 따라서 턴이 실행되는 특성으로 인해 가끔씩 접속하여 턴만 예약(이하 예턴)하는 것만으로도 플레이가 가능하므로 하루종일 게임에 얽매일 필요가 없고 플레이가 간단합니다.<br>
<br>
　<font class=bullet>☞</font> 멍청한 컴퓨터와 대결하는 일반적인 전략게임과 달리 경쟁하는 상대가 모두 실제 사람이므로 치열한 두뇌싸움이 펼쳐지므로 흥미진진합니다.<br>
<br>
　<font class=bullet>☞</font> 앞선 설명처럼, 시간투자가 적고 플레이가 간단하면서도 다양한 전략대결의 특징을 가지는 체섭은 바쁜 현대인들에게 적합한 게임입니다. 실제로 대다수의 유저들이 고등학생, 대학생, 직장인, 자영업자로 이루어져 있습니다.<br>
<br>
　<font class=bullet>☞</font> 체섭 입문을 권유하는 분들이 항상 하는 말씀 : 하루 5분 투자만으로도 가능한 게임!<br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_01_03.jpg class=rightFloat>
<font class=title>『삼국지 모의전투』?</font><br>
　<font class=bullet>☞</font> 동양인들이 열광하는 중국 삼국시대(서기 184년~280년)를 배경으로 하여, 유저 자신이 한명의 장수가 되어 다른 장수들과 각축을 벌이며 중원에서 활약하는 게임입니다.<br>
<br>
　<font class=bullet>☞</font> 여러국가들의 혈전 중에, 약하거나 정책이 실패한 국가들은 좌초되고 최후까지 살아남아 중국 전토를 통일(이하 천통)하는 국가가 황제국으로서 인정받고 왕조일람에 영원히 기억됩니다.<br>
<br>
　<font class=bullet>☞</font> 통일 후에는 다시 초기상태로 돌아가(이하 리셋) 새로운 기수를 시작하게 됩니다. 일반적으로 1기수는 1개월 정도가 소요됩니다.<br>
<br>
<font class=title>어떤 재미로 삼모전을 하나요?</font><br>
　<font class=bullet>☞</font> 멍청한 컴퓨터와 대결하며 혼자 플레이하는 전략게임에 흥미를 잃은 전략가들은 체섭에서의 전략 대결이 결코 녹록치 않음을 느끼고 치열한 두뇌싸움에 열중하게 될겁니다!<br>
<br>
　<font class=bullet>☞</font> 일반 MMORPG에서 길드장, 혈맹장을 즐겨 하신분들이라면 체섭에서의 수뇌부나 군주를 하면서 휘하 장수들을 거느리며 통솔하는 것에서 재미를 찾을 수 있을지도 모릅니다!<br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_01_02.jpg class=leftFloat>
　<font class=bullet>☞</font> 힘들이지 않고 여유롭게, 캐릭터를 육성하거나 전투하는 재미를 찾을 수 있습니다!<br>
<br>
　<font class=bullet>☞</font> 온라인 게임을 하면서도 뭔지 모르게 외롭다거나, 심심하거나, 멍때리며 칼질만 하는 당신을 발견했다면... 삼모전만의 매력인 IRC 채팅(공개채널, 국가채널 등)을 즐기며 여유로운 게임을 겸해보세요! 몇시간이 훌쩍 지나는 것을 체험할 수 있습니다!<br>
<br>
<font class=intro>◈ 이정도면 기본적인 웹게임과 삼모전의 개념을 이해하실 수 있으리라 생각합니다. 그럼 이제 본격적인 게임을 배워볼까요?</font><br>
        </td>
    </tr>
</table>



<?php
} elseif ($category == 1) {
        ?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td height=50 bgcolor=yellow align=center><b><font color=black size=5>회 원 가 입</font></b></td></tr>
    <tr>
        <td>
<font class=intro>◈ 회원 가입을 해봅시다!</font><br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_02_01.jpg class=leftFloat>
　<font class=bullet>☞</font> 아직 회원가입을 하지 않았다면 계정생성을 눌러봅시다.<br>
<br>
　<font class=bullet>☞</font> 원하는 ID를 입력합니다.<br>
<br>
　<font class=bullet>☞</font> 원하는 PW를 입력합니다.<br>
<br>
　<font class=bullet>☞</font> 확인을 위해 다시한번 PW를 입력합니다.<br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_02_02.jpg class=rightFloat>
　<font class=bullet>※</font> 비밀번호는 가입하는 순간 암호화되어 저장되므로 운영자도 알 수 없습니다. 그래서 비밀번호 찾기 기능이 없지요. 비밀번호를 잊었을 경우, 운영자에게 요청하여 초기화는 가능합니다. 안심하셔도 좋습니다.<br>
<br>
　<font class=bullet>☞</font> 주민등록번호를 입력합니다.<br>
<br>
　<font class=bullet>※</font> 주민등록번호는 중복계정 방지와 악성유저 방지를 위한 목적입니다. 주민등록번호는 가입 즉시 뒷자리가 암호화되어 저장되므로 개인정보 도용의 위험이 없습니다. 통계정보를 위해 생년월일과 성별만 기록합니다. 남의 주민번호를 도용하지 맙시다<br>
<br>
　<font class=bullet>☞</font> 닉네임을 입력합니다.<br>
<br>
　<font class=bullet>※</font> 닉네임이란 게임 내에서 사용되는 캐릭터 이름이 아닙니다. 단지 온라인에서 본인의 이름처럼 사용할 수 있는 것이면 됩니다. 그냥 평소 온라인에서 즐겨 쓰는 닉네임을 사용하시면 좋습니다. IRC 등에서 본인을 나타낼 수있는 목적으로 충분합니다. 참고로 운영자는 Hide_D라는 닉네임을 사용합니다^^<br>
<br>
　<font class=bullet>☞</font> 필독사항을 읽고 동의란에 체크를 합니다.<br>
<br>
　<font class=bullet>☞</font> 웹게임에서는 1인이 다중계정을 사용하는것을 엄격히 금하고 있습니다. 주로 IP를 대상으로 검사하므로, 한 가정에서 형/동생, 기숙사, 도서관 등 공공장소라면 주의하셔야 합니다.<br>
<br>
　<font class=bullet>☞</font> 회원가입을 누릅니다.<br>
<br>
<font class=intro>◈ 이제 회원가입이 완료되었습니다. 플레이하러 가볼까요?</font><br>
        </td>
    </tr>
</table>



<?php
    } elseif ($category == 2) {
        ?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td height=50 bgcolor=yellow align=center><b><font color=black size=5>접 속 관 리</font></b></td></tr>
    <tr>
        <td>
<font class=intro>◈ 웹게임에서 로그인은 굉장히 중요한 부분입니다. 잘 읽어주세요~</font><br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_03_01.jpg class=leftFloat>
　<font class=bullet>☞</font> 로그인하는 순간 IP같은 몇가지 접속 정보가 서버에 기록됩니다. 특히 공공장소에서 접속 장소를 명시하지 않고 접속하는 경우, 멀티로 판단되어 캐릭터가 블럭되는 경우가 있으므로 주의해주세요!<br>
<br>
　<font class=bullet>☞</font> 한 가정에서 형과 동생, 직장에서 직장 동료들과 함께 플레이하는 경우는 특히 접속장소를 잘 적어주세요.<br>
<br>
　<font class=bullet>예시)</font> 자택/형, 삼모대학 도서관, 62사단 사지방 등<br>
<br>
<p align=center><img src=<?=ServConfig::$gameImagePath?>/help_03_02.jpg>
<img src=<?=ServConfig::$gameImagePath?>/help_03_03.jpg>
<img src=<?=ServConfig::$gameImagePath?>/help_03_04.jpg></p>
　<font class=bullet>☞</font> 위처럼, 한 IP에서 여러명이 접속해야하는 경우는 항상 멀티후보로 분류되므로 조심해야 합니다. 동생이 형의 계정으로 접속하여 대신 명령을 해준다든지(이하 대턴입력) 하는 등의 행동은 여러가지 분석정보에 의해서 적발될 수 있습니다.<br>
<br>
　<font class=bullet>☞</font> 한 IP에서 다수가 접속해야 하는 경우는 정황판단에 의해서 언제든지 블럭대상이 될 수 있음을 명심하시고, 블럭당할시에 이의제기는 받지 않습니다. 그만큼 확실히 멀티나 대턴이 확실하다고 판단되는 경우를 적발합니다.<br>
<br>
　<font class=bullet>☞</font> 실수로 접속장소를 제대로 적지 못하고 로그인 한 경우, 즉시 로그아웃하고 다시 로그인하시면 됩니다.<br>
<br>
<font class=intro>◈ 자, 이제 가입시 기록했던 정보로 로그인을 해봅시다.</font><br>
        </td>
    </tr>
</table>



<?php
    } elseif ($category == 3) {
        ?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td height=50 bgcolor=yellow align=center><b><font color=black size=5>캐 릭 터 생 성 &amp; 계 정 관 리</font></b></td></tr>
    <tr>
        <td>
<font class=intro>◈ 각 서버의 특징을 알아봅시다.</font><br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_04_01.jpg class=leftFloat>
　<font class=bullet>☞</font> 온라인 게임을 해보셨다면 익숙한 서버 선택 화면입니다. 체섭은 크게 유저들만 활동하는 메이져 서버인 체섭과 NPC들과 어울릴 수 있는 마이너 서버인 퀘풰퉤훼섭으로 분류됩니다. 마이너 서버들은 본래 테스트 서버 목적이므로 언제든지 리셋, 폐쇄될 수 있습니다.<br>
<br>
　<font class=bullet>☞</font> 체섭은 180년에 모두 공백지인 상태에서 유저들끼리 경쟁하는 가장 기본적인 방법의 삼모전입니다. 활동 유저도 가장 많고 그만큼 치열합니다. 대신 눈에 띄는 활약을 했다면 『명예의 전당』과 『왕조일람』에 그 명성을 영원히 남길 수 있습니다!<br>
<br>
　<font class=bullet>☞</font> 퀘섭은 실제 역사 상황에 실존하던 장수들이 되어볼 수 있습니다! 제갈량이 되어 국가 정책을 세워봅시다! 관우가 되어서 중원을 누비며 전투를 해봅시다!<br>
<br>
　<font class=bullet>☞</font> 풰섭은 실제 역사 상황에 가상 인물로 뛰어들어 실존하던 장수들과 어울려 역사를 바꾸는데 동참할 수 있습니다! 유비와 함께 적벽대전에 참여해 봅시다!<br>
<br>
　<font class=bullet>☞</font> 퉤섭은 가상상황에 뛰어들어 여러 NPC들과 어울려 자웅을 겨뤄볼 수 있습니다! 장비와 하후돈을 동시에 거느려 봅시다!<br>
<br>
　<font class=bullet>☞</font> 훼섭은 퉤섭과 비슷한 스타일이지만 고속진행이 특징입니다. 아침에 시작해서 밤에 통일되는 쾌속 서버! 휴가나 방학에 즐기면 좋습니다.<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_04_02.jpg class=rightFloat>
<font class=intro>◈ 원하는 서버에 캐릭터를 생성해 봅시다.</font><br>
<br>
　<font class=bullet>☞</font> 마음에 드는 서버에서 장수생성을 클릭해봅시다. 저는 퉤섭을 찍어볼게요~<br>
<br>
　<font class=bullet>☞</font> 각 국가에서 재야 장수를 영입하기 위해서 여러가지 홍보 문구를 써놓고 있군요. 쭉 아래로 내려서...<br>
<br>
　<font class=bullet>☞</font> 생성할 캐릭터 이름을 입력합니다. 기본적으로는 닉네님으로 설정되어 있습니다. 매 기수마다 다른사람들이 알아보지 못하도록 숨어서 플레이(이하 잠입)하는 유저도 있습니다. 잠입의 묘미는 중수가 되면 저절로 아시게 될겁니다!<br>
<br>
　<font class=bullet>☞</font> 일반 장수 얼굴을 사용할때는 얼굴을 고릅니다. 각자의 개성을 나타낼 수 있도록 골라봅시다.<br>
<br>
　<font class=bullet>☞</font> 장수의 성격을 선택합니다. 처음은 그냥 ????(랜덤)을 선택합니다.<br>
<br>
　<font class=bullet>☞</font> 능력치를 결정합니다. 아래의 4가지 유형의 주사위를 굴려서 적당한 능력치를 골라보세요. 무난하게 통솔무력형, 통솔지력형을 눌러봅시다.<br>
<br>
　<font class=bullet>☞</font> 장수생성을 누릅시다.<br>
<br>
　<font class=bullet>☞</font> 다시 로그인 해봅시다. 캐릭터가 생성되었군요!<br>
<img src=<?=ServConfig::$gameImagePath?>/help_04_03.jpg class=leftFloat>
<div class=clear></div>
<br>
<font class=intro>◈ 자신의 계정을 관리하는 방법을 알아봅시다.</font><br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_04_04.jpg class=leftFloat>
<img src=<?=ServConfig::$gameImagePath?>/help_04_05.jpg class=rightFloat>
　<font class=bullet>☞</font> 플레이에 들어가기에 앞서 계정관리를 살펴봅시다. 계정관리의 비번&amp;전콘을 눌러봅시다.<br>
<br>
　<font class=bullet>☞</font> 비밀번호를 변경할 수 있습니다.<br>
<br>
　<font class=bullet>☞</font> 닉네임은 함부로 변경할 수 없으며, 특별히 바꾸고 싶은 경우는 관리자에게 문의해 주세요~<br>
<br>
<font class=intro>◈ 자, 이제 본격적으로 게임에 들어가볼까요! 지금은 연습이므로 풰섭이나 퉤섭중에서 하나에 캐릭터를 생성하고 입장해봅시다.</font><br>
        </td>
    </tr>
</table>



<?php
    } elseif ($category == 4) {
        ?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td height=50 bgcolor=yellow align=center><b><font color=black size=5>명 령 입 력</font></b></td></tr>
    <tr>
        <td>
<font class=intro>◈ 가장 기본이 되는 턴입력을 해봅시다.</font><br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_05_01.jpg class=leftFloat>
　<font class=bullet>☞</font> 중간에 보이는 세로 스크롤창에서 클릭, 드래그, Ctrl+클릭, Shift+클릭을 해봅시다. 클릭은 1개만 선택됩니다.<br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_05_03.jpg class=rightFloat>
<img src=<?=ServConfig::$gameImagePath?>/help_05_02.jpg class=rightFloat>
　<font class=bullet>☞</font> 1턴 부분에서 버튼을 누른 후, 떼지 말고 5턴 부분까지 끌어봅시다. 여러개가 선택됩니다. 드래그라고 하지요.<br>
<br>
　<font class=bullet>☞</font> 이번엔 1턴을 클릭해봅시다. 그리고 Ctrl키를 누른 상태에서 3턴, 6턴을 눌러봅시다. 떨어져있는 여러가지를 선택할 수 있답니다.<br>
<br>
　<font class=bullet>☞</font> 이번엔 1턴 부분을 클릭해봅시다. 그리고 Shift키를 누른 상태에서 5턴 부분을 클릭해 봅시다. 드래그와 비슷한 기능이죠? 이처럼 여러가지 방법을 사용해서 예약할 턴을 선택할 수 있습니다.<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_05_04.jpg class=leftFloat>
　<font class=bullet>☞</font> 간단하게 1턴, 3턴을 골라봅시다.<br>
<br>
　<font class=bullet>☞</font> 우측에 보이는 콤보박스를 클릭하면 명령 목록이 쭉 나열됩니다. 휠이나 스크롤바를 사용하여 아래로 쭉 내려봅시다. ===개인=== 탭에 가장 먼저 보이는 견문을 선택합니다.
<br>
　<font class=bullet>☞</font> 우측에 보이는 실행 버튼을 클릭해 봅시다.<br>
<br>
　<font class=bullet>☞</font> 가장 우측에 보이는 곳에 1, 3번째 칸에 견문이 입력되었을겁니다. 이처럼 원하는 턴을 선택하고, 원하는 명령을 선택하고, 실행을 눌러서 원하는 명령을 원하는 턴에 입력하는게 가장 기본적인 플레이 방법입니다.<br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_05_05.jpg class=leftFloat>
　<font class=bullet>☞</font> 우측에 보이는 정보창(이하 예턴창)에서는 턴 순서, 게임시간, 실제시간, 명령이 보이게 됩니다. 방금 입력한대로 따르면 1턴이 197년 9월이고 실제 시간으로는 21시 30분에 실행된다는 뜻이네요^^<br>
<br>
　<font class=bullet>☞</font> 이처럼 자신의 턴시간에 자신이 예약해둔 명령이 실행되는 형태로 게임이 진행됩니다. 예약턴을 지정해놓고 잠을 자도 되고, 일을 해도 되고, IRC에 가서 전략을 토론하거나 잡담을 해도 되며, 심지어 멍때려도 됩니다!<br>
<div class=clear></div>
<br>
<font class=intro>◈ 고급 턴 입력 스킬을 공부해 봅시다.</font><br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_05_06.jpg class=leftFloat>
　<font class=bullet>☞</font> 1턴에 견문을 입력해봅시다.<br>
<br>
　<font class=bullet>☞</font> 2턴에 요양을 입력해봅시다.<br>
<br>
　<font class=bullet>☞</font> 반복&amp;수정에서 2턴을 고르고 반복을 눌러봅시다.<br>
<br>
　<font class=bullet>☞</font> 무슨 기능인지 눈치 채셨나요? 2턴 반복을 선택한다면 1~2턴이 반복되어 24턴까지 자동 입력되는 기능입니다. 5턴 반복이라면 1~5턴이 반복되어서 6~10, 11~15, 16~20, 21~24 까지 자동 입력되게 됩니다! 아주 편리한 기능이죠.<br>
<br>
　<font class=bullet>☞</font> 이번엔 전체턴에 견문을 입력해봅시다. 1~24턴까지 견문이 입력될겁니다.<br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_05_07.jpg class=leftFloat>
　<font class=bullet>☞</font> 반복&amp;수정에서 5턴 미루기를 선택해 봅시다. 1~5턴이 휴식으로 된것을 볼 수 있습니다.<br>
<br>
　<font class=bullet>☞</font> 무슨 기능인지 눈치 채셨나요? 현재 입력된 1~24턴이 그대로 5칸이 6~24턴으로 밀리게 되고 공백칸은 휴식으로 채워지게 됩니다. 전체 턴 순서는 유지하면서도 끼워넣거나 할때 편리하게 이용할 수 있습니다. 당기기도 비슷한 기능이랍니다. 직접 해보세요!<br>
<br>
　<font class=bullet>☞</font> 이로써 입력 방법은 모두 마스터 하셨습니다. 상당히 간단하죠?<br>
<br>
　<font class=bullet>☞</font> 이제 인터페이스 구경을 하러 갑시다. 전체턴(이하 올턴)에 견문을 입력합니다.<br>
<br>
<font class=intro>◈ 올턴 견문을 입력했다면 이제 전체 인터페이스를 구경해 봅시다!</font><br>
        </td>
    </tr>
</table>



<?php
    } elseif ($category == 5) {
        ?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td height=50 bgcolor=yellow align=center><b><font color=black size=5>인 터 페 이 스</font></b></td></tr>
    <tr>
        <td>
<font class=intro>◈ 올턴 견문을 입력해놓으셨나요? 그럼 이제 각종 정보를 구경해볼까요?</font><br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_01.jpg class=leftFloat>
<font class=title>세력도</font><br>
　<font class=bullet>☞</font> 전체 지도와 장수동향, 정세동향을 볼 수 있습니다. 여기 나오는 정보들은 최신 소식이 가장 위에 나오는 형태이므로 아래에서 위로(↑방향) 읽는 것이 시간순서가 되겠지요^^<br>
<br>
<font class=title>세력일람</font><br>
　<font class=bullet>☞</font> 현재 존재하는 국가들이 큰 세력 순서대로 나열됩니다.<br>
<br>
<font class=title>장수일람</font><br>
　<font class=bullet>☞</font> 현재 존재하는 전체 장수들이 나열됩니다. 모든 장수들의 얼굴이 나오게 되어서 서버에 큰 부하를 줄 수 있으니 자주 보지 않도록 해요^^<br>
<br>
<font class=title>명장일람</font><br>
　<font class=bullet>☞</font> 장수들 중에서 활약이 뛰어나거나 멋진 아이템을 소유한 장수들이 얼굴을 자랑하는 곳입니다.<br>
<br>
<font class=title>명예의전당</font><br>
　<font class=bullet>☞</font> 서버에서 역대 가장 위대한 장수들의 기록을 남겨둔 곳입니다. 이곳의 이름은 영원히 남는 곳이므로 당신도 이곳에 이름을 남겨서 명성을 떨쳐 보시는건 어떨까요? 말그대로 초간지!<br>
<br>
<font class=title>왕조일람</font><br>
　<font class=bullet>☞</font> 서버의 역사를 고스란히 담고 있는 곳입니다. 매 기수마다 천통한 국가의 정보가 영원히 기록됩니다. 역시 뛰어난 활약을 보였다면 여기에 자신의 이름이 기록될겁니다!<br>
<br>
<font class=title>토너먼트</font><br>
　<font class=bullet>☞</font> 일기토를 아시나요? 천하제일 무술대회를 아시나요? 바로 그겁니다! 정세와 관계없이 캐릭터들끼리 실력을 겨룰 수 있는 곳입니다. 통솔, 무력, 지력, 종합능력에 자신이 있다면 각종 대회가 개최되는 순간 참여해 보세요! (체섭만 지원)<br>
<br>
<font class=title>베팅장</font><br>
　<font class=bullet>☞</font> 진행중인 토너먼트가 16강에 이르렀을때, 각 장수들에게 베팅 게임을 할 수 있습니다! (체섭만 지원)<br>
<br>
<font class=title>삼모게시판</font><br>
　<font class=bullet>☞</font> 자유게시판입니다. 공지사항이나 잡담등을 확인해보세요!<br>
<br>
<font class=title>삼국일보</font><br>
　<font class=bullet>☞</font> 게임의 정세와 관련하여 신문기사처럼 올리거나 분석기사등을 쓰거나 읽을 수 있습니다!<br>
<br>
<font class=title>참여게시판</font><br>
　<font class=bullet>☞</font> 참여하실분은 여기서 공지사항을 확인하시고 참여내역을 남겨주시면 됩니다!<br>
<br>
<font class=title>패치게시판</font><br>
　<font class=bullet>☞</font> 체섭은 꾸준히 활발하게 업데이트와 패치가 이루어집니다. 최신 트렌드를 놓치고 싶지 않다면 자주 확인 필수!<br>
<br>
<font class=title>레퍼런스</font><br>
　<font class=bullet>☞</font> 게임의 세부사항을 참고할 수 있는 도움말 게시판입니다.<br>
<br>
<font class=title>튜토리얼</font><br>
　<font class=bullet>☞</font> 지금 당신이 보고 있는 것!<br>
<br>
<font class=title>설문조사</font><br>
　<font class=bullet>☞</font> 설문조사에 참여하고 아이템도 받으세요!<br>
<br>
<font class=title>접속량정보</font><br>
　<font class=bullet>☞</font> 현재 서버내의 접속현황을 확인할 수 있습니다.<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_03.jpg class=rightFloat>
　<font class=bullet>☞</font> 1. 현재 토너먼트 진행상황을 알 수 있습니다.<br>
　<font class=bullet>☞</font> 2. 서버의 과부하 상황을 알 수 있습니다.<br>
　<font class=bullet>☞</font> 3. 서버의 시나리오 종류를 알 수 있습니다.<br>
　<font class=bullet>☞</font> 4. NPC모드(기본/확장)를 표시합니다.<br>
　<font class=bullet>☞</font> 5. NPC의 상성모드(역사상성, 가상상성)를 표시합니다.<br>
　<font class=bullet>☞</font> 6. NPC선택가능 여부를 표시합니다.<br>
　<font class=bullet>☞</font> 7. 게임시간과 턴종류<br>
　<font class=bullet>☞</font> 8. 현재 접속자 수<br>
　<font class=bullet>☞</font> 9. 과부하 방지를 위한 턴당 갱신 가능 횟수(클릭 가능 횟수)<br>
　<font class=bullet>☞</font> 10. 현재 유저 현황 . 등록수 / 최대수 + NPC수<br>
　<font class=bullet>☞</font> 11. 현재 접속중인 유저가 있는 나라 목록<br>
　<font class=bullet>☞</font> 12. 긴급공지나 부가정보 표시<br>
　<font class=bullet>☞</font> 13. 국가에서 정한 방침이 표시됨<br>
　<font class=bullet>☞</font> 14. 같은 국가 소속의 접속자가 표시됨<br>
　<font class=bullet>☞</font> 15. 정세 지도<br>
　<font class=bullet>☞</font> 16. 현재 소재 도시 정보<br>
　<font class=bullet>☞</font> 17. 턴입력 참고<br>
　<font class=bullet>☞</font> 18. 1~24턴의 예약턴 표시<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_04.jpg class=rightFloat>
　<font class=bullet>☞</font> 소속 국가 정보 : 현재는 재야이므로 당연히 없겠죠? ^^ (일반장수 튜토리얼)<br>
　<font class=bullet>☞</font> 내 장수 정보 표시<br>
　<font class=bullet>☞</font> 캐릭터의 얼굴입니다.<br>
　<font class=bullet>☞</font> 병력을 소유했을때, 해당 병력의 종류에 따른 그림<br>
　<font class=bullet>☞</font> 캐릭터 이름입니다.<br>
　<font class=bullet>☞</font> 관직이 표시됩니다. 재야부터 군주까지 다양합니다.<br>
　<font class=bullet>☞</font> 능력치에 따른 장수분류를 나타냅니다.<br>
　<font class=bullet>☞</font> 건강상태를 나타냅니다.<br>
　<font class=bullet>☞</font> 턴시간(분:초) : 자신의 턴이 실행되는 시간을 말합니다.<br>
　<font class=bullet>☞</font> 통솔력, 무력, 지력과 각 능력의 경험치들입니다.<br>
　<font class=bullet>☞</font> 능력치 추가 상승이 가능한 아이템들이 표시됩니다.<br>
　<font class=bullet>☞</font> 자금 : 병력을 모으거나 내정을 수행할때 필요한 돈입니다.<br>
　<font class=bullet>☞</font> 군량 : 병력을 유지하거나 내정을 수행할때 필요한 쌀입니다.<br>
　<font class=bullet>☞</font> 도구 : 특별한 기능을 하는 도구를 구입할 수 있습니다.<br>
　<font class=bullet>☞</font> 병종 : 현재 소유한 병사들의 종류를 나타냅니다.<br>
　<font class=bullet>☞</font> 병사 : 현재 소유한 병사들의 숫자를 나타냅니다.<br>
　<font class=bullet>☞</font> 성격 : 장수의 성격을 나타냅니다.<br>
　<font class=bullet>☞</font> 훈련 : 병사들의 훈련도를 나타냅니다. 높을수록 방어를 잘합니다.<br>
　<font class=bullet>☞</font> 사기 : 병사들의 사기를 나타냅니다. 높을수록 공격을 잘합니다.<br>
　<font class=bullet>☞</font> 특기 : 장수의 특기를 나타냅니다.<br>
　<font class=bullet>☞</font> Lv : 장수의 레벨과 경험치를 나타냅니다.<br>
　<font class=bullet>☞</font> 연령 : 장수의 나이를 나타냅니다.<br>
　<font class=bullet>☞</font> 수비 : 수비모드를 나타냅니다.<br>
　<font class=bullet>☞</font> 삭턴 : 휴식을 연속으로 이만큼 실행하면 서버에서 캐릭터가 사망합니다.<br>
　<font class=bullet>☞</font> 실행 : 다음 턴까지 남은 시간입니다.<br>
　<font class=bullet>☞</font> 부대 : 소속된 부대를 나타냅니다.<br>
　<font class=bullet>☞</font> 벌점 : 갱신(클릭)량에 따라서 벌점이 쌓입니다. 너무 무리한 갱신은 서버에 무리를 줄 수 있으므로 벌점이 너무 높으면 안되겠죠. 일반적으로 플레이하는 유저라면 신경쓰지 않아도 됩니다.<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_05.jpg class=rightFloat>
　<font class=bullet>☞</font> 1. 국가정보창들 : 일반장수 튜토리얼 참고<br>
　<font class=bullet>☞</font> 2. 장수동향 : 전체 장수들의 눈에 띄는 동향들이 표시됩니다.<br>
　<font class=bullet>☞</font> 3. 개인기록 : 내 캐릭터의 명령 실행 결과들이 표시됩니다.<br>
　<font class=bullet>☞</font> 4. 중원정세 : 전체 정세들이 표시됩니다.<br>
　<font class=bullet>☞</font> 5. 메세지를 보낼 상대를 선택합니다. 전체메세지, 해당국가, 1:1메세지가 가능합니다.<br>
　<font class=bullet>☞</font> 6. 보낼 메세지를 입력합니다.<br>
　<font class=bullet>☞</font> 7. 엔터나 버튼을 눌러서 메세지를 보냅니다.<br>
　<font class=bullet>☞</font> 8. 전체 메세지, 국가 메세지, 개인 메세지가 표시됩니다.<br>
　<font class=bullet>☞</font> 9. 현재 버젼과 Hide_D의 연락처, 도움주신분들입니다.<br>
　<font class=bullet>☞</font> 10. 좋은 건의나 패치에 도움 주신분들을 적어드립니다~<br>
<div class=clear></div>
<br>
<font class=intro>◈ 인터페이스 구경 잘 하셨나요? ^^ 그럼 이제 갱신 버튼을 눌러볼까요?</font><br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_06.jpg class=leftFloat>
<br>
　<font class=bullet>☞</font> 음? 뭐가 바뀐거지? 잘 살펴보시면 명령목록이 구경하시는동안 시간만큼 수행되어서 당겨진 것을 볼 수 있을겁니다. 5분턴 서버 기준으로 20분정도 구경을 하셨다면 약 4턴이 실행되었겠군요^^<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_07.jpg class=rightFloat>
　<font class=bullet>☞</font> 개인기록을 보시면 4턴이 실행된동안의 결과가 기록되어있습니다. 하하핫! 동네 장사를 이겼군요!.<br>
<br>
　<font class=bullet>☞</font> 오오~ 레벨업 하는 모습도 보이는군요.<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_08.jpg class=leftFloat>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_09.jpg class=rightFloat>
　<font class=bullet>☞</font> 자, 재야에서도 볼 수 있는 중원 정보를 눌러봅시다.<br>
<br>
　<font class=bullet>☞</font> 각 국가별로 외교상황이나 장수 숫자를 살펴볼 수 있습니다. 한눈에 전체 정세를 파악할 때 유용하겠죠?<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_10.jpg class=rightFloat>
　<font class=bullet>☞</font> 현재도시를 눌러봅시다.<br>
<br>
　<font class=bullet>☞</font> 현재 자신이 소재하고 있는 도시의 정보를 볼 수 있습니다. 흠... 각종 수치들을 보니 아직 이 도시는 개발이 더딘 것 같군요. 뭐 우리나라가 아니므로 패스~<br>
<br>
　<font class=bullet>☞</font> 장수명이 하얀색이면 유저 캐릭터, 하늘색이면 NPC입니다.<br>
<br>
　<font class=bullet>☞</font> 아악... 제 능력치가 빨간색인걸 보니 현재 부상을 입은 상태이군요. ㅠㅠ<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_06_11.jpg class=rightFloat>
　<font class=bullet>☞</font> 이번엔 내정보&amp;설정을 눌러봅시다.<br>
<br>
　<font class=bullet>☞</font> 오. 좀 더 자세한 정보가 보이는군요. 명성과 계급, 전투기록과 숙련도도 볼 수 있군요. 개인기록도 더 오래전 것까지 볼 수 있구요. 전투 결과나 자신의 열전(역사)도 볼 수 있답니다.<br>
<br>
　<font class=bullet>☞</font> 설정란은 읽어보면 대강 아실거에요. 수비모드는 전투 튜토리얼에서 배워봅시다.<br>
<br>
　<font class=bullet>☞</font> 휴가신청은 오랫동안 접속하지 못할때 캐릭터가 죽는 상황을 방지하기 위해서 있습니다. 플레이하지 않는 캐릭터는 없어지는게 자연스럽겠지요? 그래서 보통은 80시간(삭턴80)정도 휴식만 취하는 캐릭터는 사라지게 됩니다. 여행을 다녀온다거나 할때는 휴가신청 버튼을 눌러서 삭턴을 3배로 늘리면 240시간동안은 캐릭터가 유지된답니다. 삭턴이 늘어나도 플레이엔 아무 영향이 없으니 심심하면 눌러봐도 됩니다^^<br>
<br>
<font class=intro>◈ 삼모전의 목적은 치열한 대결로 천하통일을 하는 것입니다. 그러므로 재야에서는 특별히 할 것이 없겠지요. 끽해야 견문하면서 토너먼트와 베팅장을 즐기는 것뿐이지요. 자, 그럼 이제 본격적으로 국가에 임관하여 삼모전을 즐겨볼까요?</font><br>
        </td>
    </tr>
</table>



<?php
    } elseif ($category == 6) {
        ?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td height=50 bgcolor=yellow align=center><b><font color=black size=5>일 반 장 수</font></b></td></tr>
    <tr>
        <td>
<font class=intro>◈ 국가에 임관하여 일반장수가 되어서 국가를 강성하게 만들어 봅시다!</font><br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_07_01.jpg class=rightFloat>
　<font class=bullet>☞</font> 1턴에 임관을 실행해봅시다.<br>
<br>
　<font class=bullet>☞</font> 화면이 전환되면서 각국의 홍보문구가 나타납니다. 홍보문구를 보고 마음에 드는 국가를 선택해서 임관 버튼을 눌러봅시다. 손권을 돕고 싶으니 吳을 선택!<br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_07_02.jpg class=leftFloat>
　<font class=bullet>☞</font> 1턴에 【吳】(으)로 임관이라고 입력된게 보이죠? 지루하게 어떻게 기다리느냐구요? 나중에 중요 장수가 되거나 수뇌부, 군주가 되면 정신이 없을정도로 시간이 촉박할겁니다. ㅎㅎ 이리저리 구경하면서 임관이 되길 기다려봅시다.<br>
<br>
　<font class=bullet>☞</font> 1턴이 실행될즈음 중원정보나 현재도시를 들락날락 하거나 갱신 버튼을 눌러보면 임관명령 결과가 보이게 됩니다. 잘 임관 되었군요.<br>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_07_03.jpg class=leftFloat>
　<font class=bullet>☞</font> 이제 한 국가의 소속 장수이므로 신분도 일반이 되었고 국가를 강성하게 만들기 위해서 열심히 일을 해봅시다. 국가는 열심히 일한 장수들을 위해서 1월마다 자금을, 7월마다 군량을 지급하게 됩니다. 이 돈을 가지고 내정도 하고, 나중에 전쟁이 벌어지면 병사도 모으게 됩니다. 혹은 여유자금을 가지고 아이템을 구입하거나 베팅장에 투자를 할 수도 있습니다!(응?)<br>
<br>
　<font class=bullet>☞</font> 임관을 하게 되면 자동으로 수도(깃발에 별표시)로 이동하게 되는데요. 흠. 도시정보를 보니 농업, 수비가 부족하군요. 전체 선택후 명령목록에서 농업이나 수비를 선택합시다. 이 장수는 무력보다 지력이 높으므로 지력이 중요한 농지개간을 선택합니다. 실행을 누르면 올턴 농지개간이 입력!<br>
<br>
　<font class=bullet>☞</font> 이제 전체메세지로 전체 유저들과 인사도 나누고 국가메세지로 우리나라 장수들, 특히 수뇌부와 군주에게 아부를 떨어줍시다! 그래야 떡 하나라도 더 떨어지...<br>
<br>
　<font class=bullet>☞</font> 국가방침에서 장수들에게 방침을 정해준다면 그대로 따르는것이 더 좋겠죠? 국가는 일사불란하게 장수들이 움직일 때 더욱 강성해지는 법!<br>
<div class=clear></div>
<br>
<img src=<?=ServConfig::$gameImagePath?>/help_07_04.jpg class=rightFloat>
　<font class=bullet>☞</font> 이제 국가 정보가 나타나는군요. 살펴볼까요?<br>
　<font class=bullet>☞</font> 국가명<br>
　<font class=bullet>☞</font> 성향 : 국가의 성향입니다. 성향마다 장점과 단점이 존재합니다.<br>
　<font class=bullet>☞</font> 군주와 참모의 작위와 이름<br>
　<font class=bullet>☞</font> 국가 총주민수와 총병사수<br>
　<font class=bullet>☞</font> 국가소유 자금과 병량<br>
　<font class=bullet>☞</font> 지급율 : 1월과 7월에 지급하는 봉록의 정도입니다.<br>
　<font class=bullet>☞</font> 세율 : 1월과 7월에 백성들에게서 거두어 들이는 세금의 정도입니다.<br>
　<font class=bullet>☞</font> 속령 : 아국이 소유한 거점의 개수입니다.<br>
　<font class=bullet>☞</font> 장수 : 아국 소속의 장수수입니다.<br>
　<font class=bullet>☞</font> 기술력 : 아국의 기술력을 등급과 수치로 표시합니다.<br>
　<font class=bullet>☞</font> 작위 : 아국 군주의 작위를 표시합니다. 방랑군부터 황제까지 다양합니다.<br>
　<font class=bullet>☞</font> 전략 : 아국이 국가전략을 발동할 수 있기까지 남은 턴 수 입니다.<br>
　<font class=bullet>☞</font> 합병 : 각종 외교권을 행사할 수 있기까지 남은 턴 수 입니다.<br>
　<font class=bullet>☞</font> 임관 : 아국이 임관을 허용하는지 나타냅니다.<br>
　<font class=bullet>☞</font> 전쟁 : 장수들의 출병을 허용하는지 나타냅니다.<br>
　<font class=bullet>☞</font> 이제 국가관련 버튼들이 활성화 되었군요. 하나씩 살펴볼까요?<br>
　<font class=bullet>☞</font> 회의실 : 게시판처럼 사용할 수 있습니다. IRC에서 전부 모이기 힘들때 전략 토론을 하거나 타국 첩보 정보를 올리기도 합니다.<br>
　<font class=bullet>☞</font> 기밀실 : 수뇌부만 사용 가능한 회의실입니다. (수뇌부 튜토리얼 참고)<br>
　<font class=bullet>☞</font> 부대편성 : 국가에 소속된 장수라면 누구든지 부대를 만들거나 부대에 가입할 수 있습니다. 부대장이 집합명령을 실행하면 부대원들이 모두 부대장이 소재한 도시로 모이게 됩니다. 보통은 수뇌가 정해주는 지시에 따라 부대를 가입하면 됩니다.<br>
　<font class=bullet>☞</font> 인사부 : 아국내의 각 직위를 볼 수 있습니다.<br>
　<font class=bullet>☞</font> 내무부 : 아국의 외교상황과 예산 정보 등을 볼 수 있습니다. 아국이 어디와 전쟁중인지 누구와 동맹인지, 가난한지 부유한지 알 수 있겠죠?<br>
　<font class=bullet>☞</font> 사령부 : 아국의 수뇌부들의 수뇌명령들을 볼 수 있습니다. 무슨 전략을 수행하는지, 자신이 어디로 발령되는지, 언제 포상을 받을지 확인할 수 있겠죠?<br>
　<font class=bullet>☞</font> 암행부 : 아국 장수들의 일제 실행정보와 병력정보등을 볼 수 있습니다. 시중 이상의 관직 또는 국가에 설정된 사관년도 이상 장수만 볼 수 있습니다.<br>
　<font class=bullet>☞</font> 세력정보 : 아국의 간략 정보와 역사 등을 볼 수 있습니다.<br>
　<font class=bullet>☞</font> 세력도시 : 아국 소유의 거점들의 일제 정보를 볼 수 있습니다.<br>
　<font class=bullet>☞</font> 세력장수 : 아국 소속 장수들의 일제 저오를 볼 수 있습니다.<br>
　<font class=bullet>☞</font> 국법 : 수뇌부가 제정하는 법령이나 규칙, 기타 사항을 볼 수 있습니다.<br>
<br>
<font class=intro>◈ 자, 이로써 일반장수로서 플레이하는 방법을 간단하게나마 살펴보았습니다. 단순히 턴입력과 결과로 여유롭게 즐기는 삼모전, 혹은 IRC나 국가메세지에 적극 참여하여 전술과 전략을 토론하며 바쁘게 즐기는 삼모전, 모두 여려분의 선택에 달려있습니다!</font><br>
<br>
<font class=intro>◈ 일반장수를 마스터하신 분이라면 경험과 IRC에서 다른유저들과의 대화로 충분히 수뇌부도 마스터 하실 수 있습니다!</font><br>
        </td>
    </tr>
</table>



<?php
    } elseif ($category == 7) {
        ?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td height=50 bgcolor=yellow align=center><b><font color=black size=5>F A Q</font></b></td></tr>
    <tr>
        <td>
<font class=intro>◈ 몇가지 더 알아볼까요?</font><br>
<br>
<font class=title>왜 가입시에 도시나 국가선택이 없죠?</font><br>
　<font class=bullet>☞</font> 게임은 임의의 도시에서 재야로 시작하게 되며 건국 및 임관은 게임 내에서 명령으로 실행합니다.<br>
　<font class=bullet>☞</font> 임관하고 싶은 국가가 너무 멀리있다고 실망하지 마세요! 어디서든지 임관만 하면 수도로 자동 이동한답니다^^<br>
<br>
<font class=title>재야에서는 뭘 할 수 있죠?</font><br>
　<font class=bullet>☞</font> 모든것은 자신의 선택입니다^^ 견문을 할 수도 있고, 천하 정세를 구경할 수도 있습니다.<br>
　<font class=bullet>☞</font> 아니면 나라를 건국하여 천하 통일의 야심을 품어보세요! <br>
　<font class=bullet>☞</font> 군주가 부담스럽다면 다른 나라에 임관하여 신하가 되어 천하 통일에 보탬이 될 수도 있습니다.<br>
<br>
<font class=title>너무 지루해요~ 한시간에 한 명령밖에 못하나요?</font><br>
　<font class=bullet>☞</font> 삼모전은 웹게임으로서, 여유롭게 하는것이 매력인 게임입니다^^ 여유를 가지고 다른 일을 하면서 즐기세요!<br>
　<font class=bullet>☞</font> 또는 전체 메세지를 이용하여 다른 장수분들과 친분을 쌓거나 농담하기도 시간가는줄 모르게 된답니다^^<br>
　<font class=bullet>☞</font> 잘 모르는 것들은 전체 메세지로 다른분들께 물어보세요! 고수분들이 친절할게 알려주실지도 몰라요~ <br>
　<font class=bullet>☞</font> 진정한 삼모전을 즐겨보시려면 군주나 수뇌부를 해보시길 바랍니다. 1시간이 1분처럼 느껴질 정도로 정신없고 스릴있답니다!<br>
　<font class=bullet>☞</font> 아니면 턴시간이 빠른 마이너 서버들을 해보세요!<br>
<br>
<font class=title>공격력, 방어력, 기동력, 회피율은 무엇인가요?</font><br>
　<font class=bullet>☞</font> 병종의 특성이 궁금하시군요! 공격력과 방어력은 말그대로 병종의 공수능력이며 기동력은 병종의 공격횟수입니다.<br>
　<font class=bullet>☞</font> 보통은 7페이즈이지만 어떤 병종은 6~8페이즈를 전투하지요!<br>
　<font class=bullet>☞</font> 회피율은 병종이 장수의 말을 잘따라 공격을 피하는 것입니다!<br>
<br>
<font class=title>징병과 모병의 차이는 무엇인가요?</font><br>
　<font class=bullet>☞</font> 네. 징병은 자금이 적게 드는 대신 훈련과 사기가 낮아요. 훈련과 사기진작을 많이 해야겠죠!<br>
　<font class=bullet>☞</font> 모병은 자금이 2배로 드는 대신 훈련과 사기가 높아요. 훈련과 사기진작을 단 한번에 거의 끝낼 수 있답니다!<br>
<br>
<font class=title>여기에는 사기치가 따로 있네요?</font><br>
　<font class=bullet>☞</font> 훈련치는 방어력에 주로 영향을 미치며 공격력에도 약간의 영향을 미쳐요. 훈련을 하게 되면 사기가 조금 떨어지게 되요ㅠㅠ<br>
　<font class=bullet>☞</font> 사기치는 사기진작을 해서 올릴 수 있으며 약간의 돈이 듭니다. 병사들에게 술과 고기를 베풀려면 당연하겠죠?<br>
<br>
<font class=title>여러가지 병종을 모으고 싶어요!</font><br>
　<font class=bullet>☞</font> 병종은 특정 도시에서 생산이 가능한 특수병과 이민족병, 특정 지역에서 생산이 가능한 지역병으로 나눌 수 있어요.<br>
　<font class=bullet>☞</font> 더 좋고, 더 나쁜 병종은 없답니다. 상황과 목적에 따라 적합한 병종을 선택하여 전략의 승자가 되어봅시다!<br>
　<font class=bullet>☞</font> 특수병은 강력한 전투력 대신 자금과 군량 소모가 큰 특징이 있답니다.<br>
　<font class=bullet>☞</font> 이민족병은 준수한 전투력과 더불어 자금과 군량 소모가 적은 특징이 있답니다.<br>
　<font class=bullet>☞</font> 지역병은 일반병과 비슷한 능력에 기동력이나 회피율이 뛰어난 특징이 있답니다.<br>
<img src=<?=ServConfig::$gameImagePath?>/help_08_01.jpg class=rightFloat>
　　---------- 보병계열 ----------<br>
　　　　보병 : 특별한 조건이 없이 만들 수 있어요.<br>
　　　청주병 : <font color=cyan>중원</font>지역에서 생산이 가능해요.<br>
　　　　수병 : <font color=cyan>오월</font>지역에서 생산이 가능해요.<br>
　　　자객병 : <font color=green>왜</font>에서 생산이 가능해요.<br>
　　　근위병 : <font color=green>낙양</font>에서 생산이 가능해요.<br>
　　　등갑병 : <font color=cyan>남중</font>지역에서 생산이 가능해요.<br>
　　---------- 궁병계열 ----------<br>
　　　　궁병 : 특별한 조건이 없이 만들 수 있어요.<br>
　　　궁기병 : <font color=cyan>동이</font>지역에서 생산이 가능해요.<br>
　　　연노병 : <font color=cyan>서촉</font>지역에서 생산이 가능해요.<br>
　　　강궁병 : <font color=green>양양</font>에서 생산이 가능해요.<br>
　　　석궁병 : <font color=green>건업</font>에서 생산이 가능해요.<br>
　　---------- 기병계열 ----------<br>
　　　　기병 : 특별한 조건이 없이 만들 수 있어요.<br>
　　　백마병 : <font color=cyan>하북</font>지역에서 생산이 가능해요.<br>
　　중장기병 : <font color=cyan>서북</font>지역에서 생산이 가능해요.<br>
　　돌격기병 : <font color=green>흉노</font>에서 생산이 가능해요.<br>
　　　철기병 : <font color=green>강</font>에서 생산이 가능해요.<br>
　　수렵기병 : <font color=green>저</font>에서 생산이 가능해요.<br>
　　　맹수병 : <font color=green>남만</font>에서 생산이 가능해요.<br>
　　호표기병 : <font color=green>허창</font>에서 생산이 가능해요.<br>
　　---------- 귀병계열 ----------<br>
　　　　귀병 : 특별한 조건이 없이 만들 수 있어요.<br>
　　　신귀병 : <font color=cyan>초</font>지역에서 생산이 가능해요.<br>
　　　백귀병 : <font color=green>오환</font>에서 생산이 가능해요.<br>
　　　흑귀병 : <font color=green>산월</font>에서 생산이 가능해요.<br>
　　　악귀병 : <font color=green>장안</font>에서 생산이 가능해요.<br>
　　　남귀병 : 특별한 조건이 없이 만들 수 있어요.<br>
　　　황귀병 : <font color=green>낙양</font>에서 생산이 가능해요.<br>
　　　천귀병 : <font color=green>성도</font>에서 생산이 가능해요.<br>
　　　마귀병 : <font color=green>업</font>에서 생산이 가능해요.<br>
　　---------- 차병계열 ----------<br>
　　　　정란 : 특별한 조건이 없이 만들 수 있어요.<br>
　　　　충차 : 특별한 조건이 없이 만들 수 있어요.<br>
　　　벽력거 : <font color=green>업</font>에서 생산이 가능해요.<br>
　　　　목우 : <font color=green>성도</font>에서 생산이 가능해요.<br>
<br>
<font class=title>병종간의 상성이란 무엇인가요?</font><br>
　<font class=bullet>☞</font> 보병은 궁병계열에 강하고, 기병은 보병계열에 강하답니다. 궁병은 기병계열에 강하답니다.<br>
　<font class=bullet>☞</font> 무조건 강한 병종이란 없답니다^^ 상대측의 주력 병종을 파악 후 대처하는것도 좋겠죠?<br>
<br>
<font class=title>첩보란 무엇인가요?</font><br>
　<font class=bullet>☞</font> 첩보는 타국 도시를 정찰하는 것이에요.<br>
　<font class=bullet>☞</font> 자신이 있는 도시와 가까운 곳이라면 많은 정보를 얻을 수 있어요. 인접한 도시가 아니라면 간단한 정보만 얻을 수 있어요.<br>
　<font class=bullet>☞</font> 첩보를 실행하고 개인기록을 확인하세요! 또한 첩보를 실행하면 3개월간 그 도시를 클릭하여 확인이 가능해요!<br>
<br>
<font class=title>전투 로그가 너무 길어서 지나가 버렸어요ㅠㅠ</font><br>
　<font class=bullet>☞</font> 걱정하지 마세요^^ 메인화면 메뉴중 내정보&amp;설정 부분을 클릭하면 더 많은 로그를 확인할 수 있어요!<br>
<br>
<font class=title>다른도시는 볼 수 없나요?</font><br>
　<font class=bullet>☞</font> 우리나라의 장수들이 있는 도시라면 도시의 자세한 정보와 도시에 있는 장수들을 볼 수 있어요!<br>
　<font class=bullet>☞</font> 도시 이름을 클릭하거나 중원 지도에서 성을 클릭해 보세요! 첩보를 통해서 타국의 도시도 마치 아국 도시처럼 훤히 볼 수 있답니다!<br>
<br>
<font class=title>칭호에 대해 알고 싶어요!</font><br>
　<font class=bullet>☞</font> 평범 : 모든 능력치가 평범할때<br>
　<font class=bullet>☞</font> 현명 : 지력이 높은 장수<br>
　<font class=bullet>☞</font> 용맹 : 무력이 높은 장수<br>
　<font class=bullet>☞</font> 명사 : 통솔이 높은 장수<br>
　<font class=bullet>☞</font> 명장 : 무력과 지력이 높은 장수<br>
　<font class=bullet>☞</font> 지장 : 통솔과 지력이 높은 장수<br>
　<font class=bullet>☞</font> 용장 : 통솔과 무력이 높은 장수<br>
　<font class=bullet>☞</font> 만능 : 모든 능력이 높은 장수<br>
<br>
<font class=title>세율이 뭐지요?</font><br>
　<font class=bullet>☞</font> 세율은 국가 수뇌부가 결정하는건데요. 1월 7월마다 거두어들이는 세금의 양을 말해요.<br>
　<font class=bullet>☞</font> 세율이 낮으면 인구가 늘고 내정이 증가해요. 다만 거두어 들이는 자금이 적어요.<br>
　<font class=bullet>☞</font> 세율이 높으면 인구가 줄고 내정이 감소해요. 다만 거두어 들이는 자금이 늘어나요. 군주라면 세율을 적절히 잘 조절해야겠죠?<br>
<br>
<font class=title>지급율이 뭐지요?</font><br>
　<font class=bullet>☞</font> 지급율은 국가 수뇌부가 결정하는건데요. 100%일때 자기 자신의 제대로된 봉급을 받을 수 있어요.<br>
　<font class=bullet>☞</font> 예를 들어 200%로 정해져 있다면 보너스를 받아서 봉급의 2배를 받게 되는거죠!<br>
　<font class=bullet>☞</font> 군주라면 장수들의 사기진작과 충성을 위해 재정이 충분할때는 지급율을 높여야겠죠!<br>
　<font class=bullet>☞</font> 반대로 재정이 어렵다면 안타깝지만 국고를 유지하기 위해 지급율을 낮춰야 해요ㅠㅠ<br>
<br>
<font class=title>수지와 예산이 뭐지요?</font><br>
　<font class=bullet>☞</font> 예산은 국가정보나 사령부에 들어가면 볼 수 있어요. 우리나라의 총 수입과 지출을 나타내는것이 수지에요.<br>
　<font class=bullet>☞</font> 총 수입은 도시에서 거둬들이는 금이나 쌀의 양을 나타내구요, 총 지출은 장수들의 봉급으로 나가는 금이나 쌀의 양을 뜻해요.<br>
　<font class=bullet>☞</font> 예산이 +라면 장수들의 봉급을 주고도 남아서 국고로 쌓이게 되는 것이지요! 이럴땐 지급율을 늘려서 장수들에게 고루 나누어줄수도 있을거에요!<br>
<br>
<font class=title>전쟁에 대해 알고 싶어요.</font><br>
　<font class=bullet>☞</font> 전쟁에 영향을 미치는 요소는 아주 많답니다. 1번의 전투는 총6~9페이즈로 이루어진답니다.<br>
　<font class=bullet>☞</font> 공격력에 영향을 미치는 것들은 병종의 공격력, 장수의 무력, 병사의 사기, 훈련, 병사의 수.<br>
　<font class=bullet>☞</font> 방어력에 영향을 미치는 것들은 병종의 방어력, 장수의 통솔, 병사의 사기, 훈련, 병사의 수.<br>
　<font class=bullet>☞</font> 기동력은 페이즈 수에 영향을 미쳐요.<br>
　<font class=bullet>☞</font> 회피율은 해당 확률로 회피하는 것을 말해요.<br>
<br>
<font class=title>명 령 들</font><br>
　---------- 내 정 ----------<br>
　<font class=bullet>☞</font>농지개간: 수확을 향상시킵니다. 지력과 민심이 높을수록 좋습니다.<br>
　<font class=bullet>☞</font>상업투자: 소득을 향상시킵니다. 지력과 민심이 높을수록 좋습니다.<br>
　<font class=bullet>☞</font>기술개발: 기술을 향상시킵니다. 지력과 민심이 높을수록 좋습니다.<br>
　<font class=bullet>☞</font>수비강화: 수비병을 모읍니다. 무력과 민심이 높을수록 좋습니다.<br>
　<font class=bullet>☞</font>성벽보수: 성벽을 보수합니다. 무력과 민심이 높을수록 좋습니다.<br>
　<font class=bullet>☞</font>치안강화: 치안을 강화합니다. 무력이 높을수록 좋습니다.<br>
　<font class=bullet>☞</font>정착장려: 주민들에게 쌀을 베풀어 정착하도록 돕습니다. 통솔이 높을수록 좋습니다.<br>
　<font class=bullet>☞</font>주민선정: 주민들에게 쌀을 베풉니다. 통솔이 높을수록 좋습니다.<br>
　<font class=bullet>☞</font>물자조달: 온갖 방법으로 자원을 조달합니다.<br>
　---------- 군 사 ----------<br>
　<font class=bullet>☞</font>　　징병: 병사를 모읍니다. 통솔력 이상은 모집할 수 없습니다.<br>
　<font class=bullet>☞</font>　　모병: 병사를 모읍니다. 2배의 자금이 드는 대신 훌륭한 병사가 모입니다.<br>
　<font class=bullet>☞</font>　　훈련: 병사를 훈련시킵니다. 훈련도가 높을수록 방어를 잘합니다.<br>
　<font class=bullet>☞</font>사기진작: 병사들의 사기를 높입니다. 약간의 자금이 들지만 병사들이 공격을 잘합니다..<br>
　<font class=bullet>☞</font>　　출병: 인접 도시로 공격을 합니다.<br>
　<font class=bullet>☞</font>소집해제: 현재 소유중인 병사를 해체하고 주민으로 돌려보냅니다.<br>
　---------- 인 사 ----------<br>
　<font class=bullet>☞</font>　　이동: 인접 도시로 이동합니다.<br>
　<!--<font class=bullet>☞</font>　　등용: 재야나 타국의 장수를 등용합니다. 서신은 개인메세지로 도착하며 상대 장수가 수락,거절을 하게 됩니다.<br>--> <!--xxx:등용장 일단 끔-->
　<font class=bullet>☞</font>　　집합: 현재 도시로 부대원들을 집합합니다. 부대장만 가능합니다.<br>
　<font class=bullet>☞</font>　　귀환: 태수, 군사, 시중인 경우 자신의 도시로 귀환합니다.<br>
　<font class=bullet>☞</font>　　임관: 국가에 사관을 합니다. 대상 국가의 군주가 있는 장소로 바로 이동합니다. 재야만 가능합니다.<br>
　---------- 계 략 ----------<br>
　<font class=bullet>☞</font>　　첩보: 어느 도시든지 첩보를 수행하여 정보를 얻을 수 있습니다. 거리가 먼 도시라면 정확한 정보를 얻을 수 없습니다.<br>
　<font class=bullet>☞</font>　　화계: 인접 도시에 불을 지릅니다. 성공시 농업, 상업, 기술치가 하락하게 되며 그 피해정도는 항상 다릅니다. 그 도시 장수의 지력이 높다면 성공률이 감소합니다.<br>
　<font class=bullet>☞</font>　　탈취: 인접 도시를 약탈합니다. 성공시 금과 쌀을 가져오게 되며 그 피해정도는 항상 다릅니다. 그 도시 장수의 무력이 높다면 성공률이 감소합니다.<br>
　<font class=bullet>☞</font>　　파괴: 인접 도시의 성벽을 부숩니다. 성공시 수비병, 내구도가 하락하게 되며 그 피해정도는 항상 다릅니다. 그 도시 장수의 무력이 높다면 성공률이 감소합니다.<br>
　<font class=bullet>☞</font>　　선동: 인접 도시에 헛소문을 퍼트립니다. 성공시 민심이 하락하게 되며 그 피해정도는 항상 다릅니다. 그 도시 장수의 통솔+지력이 높다면 성공률이 감소합니다.<br>
　---------- 개 인 ----------<br>
　<font class=bullet>☞</font>　　단련: 숙련도를 단련합니다.<br>
　<font class=bullet>☞</font>　　견문: 견문을 떠납니다. 무슨일이 일어날지는 알 수 없습니다.<br>
　<font class=bullet>☞</font>군량매매: 쌀을 사고 팝니다. 시세가 1.0 이상이면 이익이 됩니다.<br>
　<font class=bullet>☞</font>장비구입: 무기나 서적을 구입합니다.<br>
　<font class=bullet>☞</font>　　증여: 자금이나 군량을 다른 장수에게 증여합니다.<br>
　<font class=bullet>☞</font>　　헌납: 자금이나 군량을 국가에 바칩니다.<br>
　<font class=bullet>☞</font>　　하야: 국가를 떠나 재야가 됩니다. 군주는 할 수 없습니다.<br>
　<font class=bullet>☞</font>　　거병: 방랑군을 결성합니다.<br>
　<font class=bullet>☞</font>　　건국: 현재 도시에서 국가를 건설합니다. 공백지에서만 가능합니다.<br>
　<font class=bullet>☞</font>　　선양: 다른 장수에게 군주직을 물려줍니다. 군주만 가능합니다.<br>
　<font class=bullet>☞</font>　　방랑: 국가를 버리고 방랑군으로 내려갑니다. 군주만 가능합니다.<br>
　<font class=bullet>☞</font>　　해산: 방랑군을 해체하고 재야로 내려갑니다. 군주만 가능합니다.<br>
　<font class=bullet>☞</font>　　모반: 군주직을 찬탈합니다. 수뇌부만 가능합니다.<br>
<br>
        </td>
    </tr>
</table>



<?php
    }
?>

<table class='tb_layout bg0'>
    <tr>
        <td align=center><input type=button style=background-color:<?=$category==0?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='시작하기' onclick=location.replace('help.php?category=0')></td>
        <td align=center><input type=button style=background-color:<?=$category==1?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='회원가입' onclick=location.replace('help.php?category=1')></td>
        <td align=center><input type=button style=background-color:<?=$category==2?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='접속관리' onclick=location.replace('help.php?category=2')></td>
        <td align=center><input type=button style=background-color:<?=$category==3?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='캐릭터생성' onclick=location.replace('help.php?category=3')></td>
        <td align=center><input type=button style=background-color:<?=$category==4?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='명령입력' onclick=location.replace('help.php?category=4')></td>
        <td align=center><input type=button style=background-color:<?=$category==5?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='인터페이스' onclick=location.replace('help.php?category=5')></td>
        <td align=center><input type=button style=background-color:<?=$category==6?"red":"#225500"?>;color:white;width:125px;height:50px;font-weight:bold;font-size:13px; value='일반장수' onclick=location.replace('help.php?category=6')></td>
        <td align=center><input type=button style=background-color:<?=$category==7?"red":"#225500"?>;color:white;width:123px;height:50px;font-weight:bold;font-size:13px; value='FAQ' onclick=location.replace('help.php?category=7')></td>
    </tr>
</table>
</body>

</html>
