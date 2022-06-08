<?php
namespace sammo\Enums;

enum ResourceType: string
{
  case gold = 'gold';
  case rice = 'rice';
  case inheritancePoint = 'inheritPoint';

  public function getName(): string
  {
    return match($this){
      ResourceType::gold => '금',
      ResourceType::rice => '쌀',
      ResourceType::inheritancePoint => '유산 포인트',
    };
  }
}
