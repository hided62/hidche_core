<?php

namespace sammo\Event\Action;

use RuntimeException;
use sammo\ActionLogger;

class NoticeToHistoryLog extends \sammo\Event\Action
{
  public function __construct(private string $msg)
  {
  }

  public function run(array $env)
  {
    if(!key_exists('year', $env) && !key_exists('month', $env)){
      throw new RuntimeException('year, month가 없음');
    }
    $logger = new ActionLogger(0, 0, $env['year'], $env['month']);
    $logger->pushGlobalHistoryLog($this->msg);
    $logger->flush();
  }
}
