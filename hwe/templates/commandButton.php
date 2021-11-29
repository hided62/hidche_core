<div class='buttonPlate bg2'>
    <a href='v_board.php' class='commandButton btn btn-sammo-nation' <?=$meLevel>=1?'':'disabled'?>>회 의 실</a>
    <a href='v_board.php?isSecret=true' class='commandButton btn btn-sammo-nation disabled'>기 밀 실</a>
    <a href='b_troop.php' class='commandButton btn btn-sammo-nation <?=($meLevel>=1&&$nationLevel>=1)?'':'disabled'?>'>부대 편성</a>
    <a href='t_diplomacy.php' class='commandButton btn btn-sammo-nation <?=$showSecret?'':'disabled'?>'>외 교 부</a>
    <a href='b_myBossInfo.php' class='commandButton btn btn-sammo-nation <?=$meLevel>=1?'':'disabled'?>'>인 사 부</a>
    <a href='b_dipcenter.php' class='commandButton btn btn-sammo-nation <?=$showSecret?'':'disabled'?>'>내 무 부</a>
    <a href='b_chiefcenter.php' class='commandButton btn btn-sammo-nation <?=$showSecret?'':'disabled'?>'>사 령 부</a>
    <a href='v_NPCControl.php' class='commandButton btn btn-sammo-nation <?=$showSecret?'':'disabled'?>'>NPC 정책</a>
    <a href='b_genList.php' target='_blank' class='open-window commandButton btn btn-sammo-nation <?=$showSecret?'':'disabled'?>'>암 행 부</a>
    <a href='b_tournament.php' target='_blank' class='open-window commandButton btn btn-sammo-nation'>토 너 먼 트</a>
    <a href='b_myKingdomInfo.php' class='commandButton btn btn-sammo-nation <?=$meLevel>=1?'':'disabled'?>'>세력 정보</a>
    <a href='b_myCityInfo.php' class='commandButton btn btn-sammo-nation <?=($meLevel>=1&&$nationLevel>=1)?'':'disabled'?>'>세력 도시</a>
    <a href='b_myGenInfo.php' class='commandButton btn btn-sammo-nation <?=$meLevel>=1?'':'disabled'?>'>세력 장수</a>
    <a href='b_diplomacy.php' class='commandButton btn btn-sammo-nation'>중원 정보</a>
    <a href='b_currentCity.php' class='commandButton btn btn-sammo-nation'>현재 도시</a>
    <a href='b_battleCenter.php' target='_blank' class='open-window commandButton btn btn-sammo-nation <?=$showSecret?'':'disabled'?>'>감 찰 부</a>
    <a href='v_inheritPoint.php' class='commandButton btn btn-sammo-nation'>유산 관리</a>
    <a href='b_myPage.php' class='commandButton btn btn-sammo-nation'>내 정보&amp;설정</a>
    <a href='b_auction.php' target='_blank' class='open-window commandButton btn btn-sammo-nation'>거 래 장</a>
    <a href='b_betting.php' target='_blank' class='open-window commandButton btn btn-sammo-nation'>베 팅 장</a>
</div>