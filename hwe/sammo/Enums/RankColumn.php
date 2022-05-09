<?php

namespace sammo\Enums;

enum RankColumn: string
{
  /** 계략 횟수 */
  case firenum = 'firenum';
  /** 전투 횟수 */
  case warnum = 'warnum';
  /** 전투 승리(적 전멸) 수 */
  case killnum = 'killnum';
  /** 전투 패배(아군 전멸) 수 */
  case deathnum = 'deathnum';
  /** 사살 병력 수 */
  case killcrew = 'killcrew';
  /** 피살 병력 수 */
  case deathcrew = 'deathcrew';

  /** 토너먼트 전력전 승 */
  case ttw = 'ttw';
  /** 토너먼트 전력전 무 */
  case ttd = 'ttd';
  /** 토너먼트 전력전 패 */
  case ttl = 'ttl';
  /** 토너먼트 전력전 전 */
  case ttg = 'ttg';
  /** 토너먼트 전력전 포인트 */
  case ttp = 'ttp';


  /** 토너먼트 통솔전 승 */
  case tlw = 'tlw';
  /** 토너먼트 통솔전 무 */
  case tld = 'tld';
  /** 토너먼트 통솔전 패 */
  case tll = 'tll';
  /** 토너먼트 통솔전 전 */
  case tlg = 'tlg';
  /** 토너먼트 통솔전 포인트 */
  case tlp = 'tlp';

  /** 토너먼트 일기토 승 */
  case tsw = 'tsw';
  /** 토너먼트 일기토 무 */
  case tsd = 'tsd';
  /** 토너먼트 일기토 패 */
  case tsl = 'tsl';
  /** 토너먼트 일기토 전 */
  case tsg = 'tsg';
  /** 토너먼트 일기토 포인트 */
  case tsp = 'tsp';

  /** 토너먼트 설전 승 */
  case tiw = 'tiw';
  /** 토너먼트 설전 무 */
  case tid = 'tid';
  /** 토너먼트 설전 패 */
  case til = 'til';
  /** 토너먼트 설전 전 */
  case tig = 'tig';
  /** 토너먼트 설전 포인트 */
  case tip = 'tip';

  /** 토너먼트 베팅 성공 수 */
  case betwin = 'betwin';
  /** 토너먼트 베팅 금액 */
  case betgold = 'betgold';
  /** 토너먼트 베팅 성공 금액 */
  case betwingold = 'betwingold';

  /** 대인 사살 병력 수 */
  case killcrew_person = 'killcrew_person';
  /** 대인 피살 병력 수 */
  case deathcrew_person = 'deathcrew_person';

  /** 점령 */
  case occupied = 'occupied';

  /** 유산 포인트 획득(지연) */
  case inherit_point_earned = 'inherit_point_earned';
  /** 유산 포인트 소모(지연) */
  case inherit_point_spent = 'inherit_point_spent';

  /** 유산 포인트 획득량(merge 명령) */
  case inherit_point_earned_by_merge = 'inherit_point_earned_dynamic';
  /** 유산 포인트 획득량(베팅 등 별도 명령) */
  case inherit_point_earned_by_action = 'inherit_point_earned_by_action';
  /** 유산 포인트 소모량(증감 있음) */
  case inherit_point_spent_dynamic = 'inherit_point_spent_dynamic';
}
