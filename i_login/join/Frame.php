<?php
require_once('_common.php');

/*
<pre>주민번호는 중복가입 방지와 허위정보 방지를 위한 용도로만 사용되며,
생년은 통계정보 등에 사용되며, <font class="LoginJoin_notice">뒷자리는 다른용도로 악용될 수 없도록 암호화됩니다.</font>
<font class="LoginJoin_notice">게임물등급위원회</font>의 정책에 따라 연령제한을 위해 주민번호 입력이 필요합니다.
주민번호 도용시 <font class="LoginJoin_alert">주민등록법 제21조 제2항 9호</font>에 따라 처벌될 수 있습니다.</pre>
*/

?>

<div id="LoginJoin_00" class="bg0">
    <div id="LoginJoin_0000" class="font4">회 원 가 입 <input class="LoginJoin_back" type="button" value="돌아가기"></div>
    <div id="LoginJoin_0001">전체 회원 수 : <font id="LoginJoin_000100">0</font>명</div>
    <div id="LoginJoin_0002">6개월간 플레이 하지 않은 일반 계정은 삭제됩니다.</div>
    <div id="LoginJoin_0003" class="bg1">계정 생성</div>
    <div id="LoginJoin_0004">아이디는 4~12자의 영문소문자 숫자 또는 조합된 문자열이어야 합니다!</div>
    <div id="LoginJoin_0005">ID&nbsp;</div>
    <div id="LoginJoin_0006">
        <input id="LoginJoin_000600" type="text" maxlength=12>
        <input id="LoginJoin_000601" type="button" value="아이디 중복 확인">
    </div>
    <div id="LoginJoin_0007">비밀번호는 4~12자의 영문자나 숫자 또는 조합된 문자열이어야 합니다!<br>암호화되어서 운영자도 알 수 없으므로 주의해 주세요!</div>
    <div id="LoginJoin_0008">비밀번호&nbsp;</div>
    <div id="LoginJoin_0009"><input id="LoginJoin_000900" type="password" maxlength=12></div>
    <div id="LoginJoin_0010">비밀번호 확인&nbsp;</div>
    <div id="LoginJoin_0011"><input id="LoginJoin_001100" type="password" maxlength=12></div>
    <input id="token_join" type="hidden" value="<?=md5(rand()%100000000);?>">
    <div id="LoginJoin_0012">
<pre>생년월일과 성별은 유저 구분을 위한 보조 용도와 통계정보 등에 사용됩니다.
진짜 본인의 생년월일을 입력해주셔야, 아이디 복구나 암호 초기화시 불이익을 당하지 않습니다.
주민번호 사용 불가로 인해 <font class="LoginJoin_notice">셧다운 제도</font> 적용이 불가능하므로,
본인이 청소년이라면 스스로의 의지로 <font class="LoginJoin_notice">셧다운 제도</font>를 준수하실 것을 권고합니다.</pre>
    </div>
    <div id="LoginJoin_0013">생년월일-성별&nbsp;</div>
    <div id="LoginJoin_0014">
        <input id="LoginJoin_001400" type="text" maxlength=6>
        <input id="LoginJoin_001401" type="text" maxlength=1>
        <input id="LoginJoin_001402" type="button" value="생년월일 확인"> (남자: 1, 여자: 2)
    </div>
    <div id="LoginJoin_0015">
        삼모전에서 주로 사용할 장수명을 입력해주세요.<br>
        <font class="LoginJoin_notice">한번 가입하면 영구회원이므로 운영자가 알아볼 수 있는 평소 닉네임을 써주세요.</font><br>
        <font class="LoginJoin_alert">일반 특수문자는 무시됩니다.</font>
    </div>
    <div id="LoginJoin_0016">닉네임&nbsp;</div>
    <div id="LoginJoin_0017">
        <input id="LoginJoin_001700" type="text" maxlength=12>
        <input id="LoginJoin_001701" type="button" value="닉네임 중복 확인">(6글자 이내)
    </div>
    <div id="LoginJoin_0018">
        인증을 위한 이메일 주소를 입력해주세요.<br>
        <font class="LoginJoin_notice">가입완료를 위한 인증메일을 수신할 주소입니다.</font> <font class="LoginJoin_alert">메일인증을 하지 않으면 로그인이 불가능합니다.</font><br>
        메일주소를 주의해서 입력해주세요! <font class="LoginJoin_notice">@gmail.com, @naver.com, @hanmail.net</font><br>
    </div>
    <div id="LoginJoin_0019">이메일&nbsp;</div>
    <div id="LoginJoin_0020">
        <input id="LoginJoin_002000" type="text" maxlength=64>
        <input id="LoginJoin_002001" type="button" value="이메일 중복 확인">(64글자 이내)
    </div>
    <div id="LoginJoin_0021">
        인증을 위한 인증번호를 입력해주세요.<br>
        <font class="LoginJoin_notice">인증번호전송을 누르고 메일을 확인하세요.</font><br>
        <font class="LoginJoin_alert">번호를 확인하여 입력후 인증확인을 누르세요.</font>
    </div>
    <div id="LoginJoin_0022">이메일 인증&nbsp;</div>
    <div id="LoginJoin_0023">
        <input id="LoginJoin_002300" type="button" value="인증번호 전송">
        <input id="LoginJoin_002301" type="text" maxlength=6>
        <input id="LoginJoin_002302" type="button" value="인증번호 확인">
    </div>
    <div id="LoginJoin_0024">필 독</div>
    <div id="LoginJoin_0025">
        접속을 6개월간 하지 않는 계정은 삭제됩니다.<br>
        한 사람의 유저가 다수의 계정을 등록을 하는것은 <font class="LoginJoin_alert">멀티이며 엄중히 처리되니</font> 양심껏 1개만 등록하시길 바랍니다.<br>
        멀티 등록이 적발될 시에는 운영자 임의대로 캐릭터를 블럭하게 됩니다. 블럭된 캐릭터는 24시간 후 삭제되면 재등록 가능합니다.<br>
        계정이 삭제되는것은 아니며, 해당 서버의 캐릭터만 삭제되게 됩니다.<br>
        블럭시에는 24시간후에 로그인하여 캐릭터 생성만 다시 하시면 됩니다.<br>
        악의적으로 멀티를 계속 시도하는 경우는 영구ip블럭이 될 수 있습니다.<br>
        멀티 검사는 관리자가 일일이 검사를 합니다.
    </div>
    <div id="LoginJoin_0026">
        반드시 게시판의 이용 약관을 읽어보셔야 합니다.<br>
        약관을 지키지 않는 유저의 경우 임의의 조치를 받으실 수 있습니다.<br>
        접속장소를 적지 않는 것은 블럭해도 이의 없다는 것에 동의하는 것입니다.
    </div>
    <div id="LoginJoin_0027"><input id="LoginJoin_002700" type="checkbox">위의 주의사항을 모두 읽었으며 동의합니다.</div>
    <div id="LoginJoin_0028"><input id="LoginJoin_002800" type="button" value="회원가입"></div>
    <div id="LoginJoin_0029"><input id="LoginJoin_002900" type="button" value="다시 입력"></div>
    <div id="LoginJoin_0030"><input class="LoginJoin_back" type="button" value="돌아가기"></div>
</div>
