<?php

namespace sammo\Event\Action;

use sammo\ActionLogger;
use sammo\DB;

class NewYear extends \sammo\Event\Action
{
  public function run(array $env)
  {
    $year = $env['year'];
    $month = $env['month'];

    $logger = new ActionLogger(0, 0, $year, $month, false);
    $logger->pushGlobalActionLog("<C>{$year}</>년이 되었습니다.");
    $logger->pushGeneralHistoryLog("<S>모두들 즐거운 게임 하고 계신가요? ^^ <Y>매너 있는 플레이</> 부탁드리고, 게임보단 <L>건강이 먼저</>란점, 잊지 마세요!</>", $logger::NOTICE_YEAR_MONTH);
    $logger->flush(); //TODO: globalAction류는 전역에서 관리하는것이 좋을 듯.

    $db = DB::db();

    //나이와 호봉 증가
    $db->update('general', [
      'age' => $db->sqleval('age+1'),
    ], true);

    $db->update('general', [
      'belong' => $db->sqleval('belong+1')
    ], 'nation != 0');
  }
}
