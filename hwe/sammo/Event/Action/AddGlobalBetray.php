<?php

namespace sammo\Event\Action;

use sammo\DB;

class AddGlobalBetray extends \sammo\Event\Action
{
  public function __construct(private int $cnt = 1, private int $ifMax = 0)
  {
  }

  public function run(array $env)
  {
    $db = DB::db();
    $db->update('general', [
      'betray' => $db->sqleval('betray + %i', $this->cnt),
    ], 'betray <= %i', $this->ifMax);
  }
}
