<?php

namespace sammo\Enums;

enum MessageType: string
{
  case private = 'private';
  case public = 'public';
  case national = 'national';
  case diplomacy = 'diplomacy';
  //TODO: System을 정식으로 추가해야함.
}
