<?=$btnBegin??''?><a href='v_board.php' class='commandButton <?=$btnClass??""?> <?= $meLevel >= 1 ? '' : 'disabled' ?>'>회 의 실</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='v_board.php?isSecret=true' class='commandButton <?= $permission >= 2 ? '' : 'disabled' ?> <?=$btnClass??""?>'>기 밀 실</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_troop.php' class='commandButton <?= ($meLevel >= 1 && $nationLevel >= 1) ? '' : 'disabled' ?> <?=$btnClass??""?>'>부대 편성</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='t_diplomacy.php' class='commandButton <?= $showSecret ? '' : 'disabled' ?> <?=$btnClass??""?>'>외 교 부</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_myBossInfo.php' class='commandButton <?= $meLevel >= 1 ? '' : 'disabled' ?> <?=$btnClass??""?>'>인 사 부</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='v_nationStratFinan.php' class='commandButton <?= $showSecret ? '' : 'disabled' ?> <?=$btnClass??""?>'>내 무 부</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='v_chiefCenter.php' class='commandButton <?= $showSecret ? '' : 'disabled' ?> <?=$btnClass??""?>'>사 령 부</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='v_NPCControl.php' class='commandButton <?= $showSecret ? '' : 'disabled' ?> <?=$btnClass??""?>'>NPC 정책</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_genList.php' target='_blank' class='open-window commandButton <?=$btnClass??""?> <?= $showSecret ? '' : 'disabled' ?>'>암 행 부</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_tournament.php' target='_blank' class='open-window commandButton <?=$btnClass??""?>'>토 너 먼 트</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_myKingdomInfo.php' class='commandButton <?=$btnClass??""?> <?= $meLevel >= 1 ? '' : 'disabled' ?>'>세력 정보</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_myCityInfo.php' class='commandButton <?=$btnClass??""?> <?= ($meLevel >= 1 && $nationLevel >= 1) ? '' : 'disabled' ?>'>세력 도시</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_myGenInfo.php' class='commandButton <?=$btnClass??""?> <?= $meLevel >= 1 ? '' : 'disabled' ?>'>세력 장수</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_diplomacy.php' class='commandButton <?=$btnClass??""?>'>중원 정보</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_currentCity.php' class='commandButton <?=$btnClass??""?>'>현재 도시</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_battleCenter.php' target='_blank' class='open-window commandButton <?=$btnClass??""?> <?= $showSecret ? '' : 'disabled' ?>'>감 찰 부</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='v_inheritPoint.php' class='commandButton <?=$btnClass??""?>'>유산 관리</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_myPage.php' class='commandButton <?=$btnClass??""?>'>내 정보&amp;설정</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_auction.php' target='_blank' class='open-window commandButton <?=$btnClass??""?>'>거 래 장</a><?=$btnEnd??''?>
<?=$btnBegin??''?><a href='b_betting.php' target='_blank' class='open-window commandButton <?=$btnClass??""?>'>베 팅 장</a><?=$btnEnd??''?>