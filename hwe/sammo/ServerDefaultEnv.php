<?php

namespace sammo;

/** 서버 내 변수 모음 */
class ServerDefaultEnv
{
  //TODO: 이 값이 .env든 d_setting의 기타 변수이든, 수정 가능해야함

  private function __construct()
  {
  }

  static int $maxGeneralsPerMinute = 1000;
}
