<?php

namespace sammo\Event\Action;

use RuntimeException;
use sammo\ActionLogger;

class NoticeToHistoryLog extends \sammo\Event\Action
{
  public function __construct(private string $msg, private int $type = ActionLogger::YEAR_MONTH)
  {
  }

  public function run(array $env)
  {
    if(!key_exists('year', $env) && !key_exists('month', $env)){
      throw new RuntimeException('year, month가 없음');
    }
    $logger = new ActionLogger(0, 0, $env['year'], $env['month']);
    $logger->pushGlobalHistoryLog($this->msg, $this->type);
    $logger->flush();
  }
}
