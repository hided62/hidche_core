<?php

namespace sammo\Enums;

enum RankColumn: string
{
  case firenum = 'firenum';
  case warnum = 'warnum';
  case killnum = 'killnum';
  case deathnum = 'deathnum';
  case killcrew = 'killcrew';
  case deathcrew = 'deathcrew';

  case ttw = 'ttw';
  case ttd = 'ttd';
  case ttl = 'ttl';
  case ttg = 'ttg';
  case ttp = 'ttp';

  case tlw = 'tlw';
  case tld = 'tld';
  case tll = 'tll';
  case tlg = 'tlg';
  case tlp = 'tlp';

  case tsw = 'tsw';
  case tsd = 'tsd';
  case tsl = 'tsl';
  case tsg = 'tsg';
  case tsp = 'tsp';

  case tiw = 'tiw';
  case tid = 'tid';
  case til = 'til';
  case tig = 'tig';
  case tip = 'tip';

  case betwin = 'betwin';
  case betgold = 'betgold';
  case betwingold = 'betwingold';

  case killcrew_person = 'killcrew_person';
  case deathcrew_person = 'deathcrew_person';

  case occupied = 'occupied';

  case inherit_point_earned = 'inherit_point_earned';
  case inherit_point_spent = 'inherit_point_spent';
}
