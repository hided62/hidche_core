<?php

namespace sammo\Enums;

// 유산 타입, DB에서 사용하는 키
enum InheritanceKey: string
{
  case previous = 'previous';
  case lived_month = 'lived_month';
  case max_belong = 'max_belong';
  case max_domestic_critical = 'max_domestic_critical';
  case active_action = 'active_action';
  case snipe_combat = 'snipe_combat';
  case combat = 'combat';
  case sabotage = 'sabotage';
  case unifier = 'unifier';
  case dex = 'dex';
  case tournament = 'tournament';
  case betting = 'betting';
}
