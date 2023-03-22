<?php

namespace sammo;

use sammo\DTO\MenuItem;
use sammo\DTO\MenuLine;
use sammo\DTO\MenuMulti;
use sammo\DTO\MenuSplit;

//변경을 원한다면 d_setting/GlobalMenu.orig.php 파일을 복제 후 수정

class GlobalMenu {
  /** @var (MenuItem|MenuMulti|MenuSplit)[] */

  const version = 2;
  static ?array $menu = null;
  public static function getMenu(): array{
    if(static::$menu !== null){
      return static::$menu;
    }
    static::$menu = [
      new MenuItem('천통국 베팅', 'v_nationBetting.php', condHighlightVar: 'nationBetting'),
      new MenuMulti('게임정보', [
        new MenuItem('세력일람', 'a_kingdomList.php', newTab: true),
        new MenuItem('장수일람', 'a_genList.php', newTab: true),
        new MenuItem('명장일람', 'a_bestGeneral.php', newTab: true),
        new MenuLine(),
        new MenuItem('명예의전당', 'a_hallOfFame.php', newTab: true),
        new MenuItem('왕조일람', 'a_emperior.php', newTab: true),
      ]),
      new MenuItem('연감', 'v_history.php', newTab: true),
      new MenuSplit(
        new MenuItem('게시판', '/board/community', newTab: true),
        [
          new MenuItem('건의/제안', '/board/request', newTab: true),
          new MenuItem('팁/강좌', '/board/tip', newTab: true),
          new MenuLine(),
          new MenuItem('패치 내역', '/board/patch', newTab: true),
        ]
      ),
      new MenuSplit(
        new MenuItem('공식 오픈 톡', 'https://open.kakao.com/o/', newTab: true),
        [
          new MenuItem('잡담 오픈 톡', 'https://open.kakao.com/o/', newTab: true)
        ],
      ),
      new MenuItem('전투 시뮬레이터', 'battle_simulator.php', newTab: true),
      new MenuMulti('기타 정보', [
        new MenuItem('접속량정보', 'a_traffic.php', newTab: true),
        new MenuItem('빙의일람', 'a_npcList', newTab: true, condShowVar: 'npcMode'),
      ]),
      new MenuItem('설문조사', 'v_vote.php', newTab: true, condHighlightVar: 'vote'),
    ];
    return static::$menu;
  }
}