<div class='buttonPlate bg2'>
    <div>
    <a href='v_board.php'><button type="button" class='commandButton' <?=$meLevel>=1?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>회 의 실</button></a>
    <a href='v_board.php?isSecret=true'><button type="button" class='commandButton' <?=$permission>=2?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>기 밀 실</button></a>
    <a href='b_troop.php'><button type="button" class='commandButton' <?=($meLevel>=1&&$nationLevel>=1)?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>부대 편성</button></a>
    <a href='t_diplomacy.php'><button type="button" class='commandButton' <?=$showSecret?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>외 교 부</button></a>
    <a href='b_myBossInfo.php'><button type="button" class='commandButton' <?=$meLevel>=1?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>인 사 부</button></a>
    <a href='b_dipcenter.php'><button type="button" class='commandButton' <?=$showSecret?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>내 무 부</button></a>
    <a href='b_chiefcenter.php'><button type="button" class='commandButton' <?=$showSecret?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>사 령 부</button></a>
    <a href='v_NPCControl.php'><button type="button" class='commandButton' <?=$showSecret?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>NPC 정책</button></a>
    <a href='b_genList.php' target='_blank'><button type="button" class='commandButton' <?=$showSecret?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>암 행 부</button></a>
    <a href='b_tournament.php' target='_blank'><button type="button" class='commandButton' style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>토 너 먼 트</button></a>
    <a href='b_myKingdomInfo.php'><button type="button" class='commandButton' <?=$meLevel>=1?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>세력 정보</button></a>
    <a href='b_myCityInfo.php'><button type="button" class='commandButton' <?=($meLevel>=1&&$nationLevel>=1)?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>세력 도시</button></a>
    <a href='b_myGenInfo.php'><button type="button" class='commandButton' <?=$meLevel>=1?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>세력 장수</button></a>
    <a href='b_diplomacy.php'><button type="button" class='commandButton' style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>중원 정보</button></a>
    <a href='b_currentCity.php'><button type="button" class='commandButton' style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>현재 도시</button></a>
    <a href='b_battleCenter.php' target='_blank'><button type="button" class='commandButton' <?=$showSecret?'':'disabled'?> style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>감 찰 부</button></a>
    <a href='v_inheritPoint.php'><button type="button" class='commandButton' style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>유산 관리</button></a>
    <a href='b_myPage.php'><button type="button" class='commandButton' style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>내 정보&amp;설정</button></a>
    <a href='b_auction.php' target='_blank'><button type="button" class='commandButton' style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>거 래 장</button></a>
    <a href='b_betting.php' target='_blank'><button type="button" class='commandButton' style='background-color:<?=$bgColor?>;color:<?=$fgColor?>;'>베 팅 장</button></a>

    </div>
</div>