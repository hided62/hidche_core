<?php

namespace sammo\Event\Action;

use sammo\DB;

class ResetOfficerLock extends \sammo\Event\Action
{
  public function run(array $env)
  {
    $db = DB::db();

    //천도 제한 해제, 관직 변경 제한 해제
    $db->update('nation', [
      'chief_set' => 0,
    ], true);
    //관직 변경 제한 해제
    $db->update('city', [
      'officer_set' => 0,
    ], true);
  }
}
