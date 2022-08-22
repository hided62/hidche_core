<?php
namespace sammo\Enums;

/**
 * City Table column
 * schemal.sql과 일치
 */
enum CityColumn: string{
  case city = 'city';
	case name = 'name';
	case level = 'level';
	case nation = 'nation';
	case supply = 'supply';
	case front = 'front';
	case pop = 'pop';
	case pop_max = 'pop_max';
	case agri = 'agri';
	case agri_max = 'agri_max';
	case comm = 'comm';
	case comm_max = 'comm_max';
	case secu = 'secu';
	case secu_max = 'secu_max';
	case trust = 'trust';
	case trade = 'trade';
	case dead = 'dead';
	case def = 'def';
	case def_max = 'def_max';
	case wall = 'wall';
	case wall_max = 'wall_max';
	case officer_set = 'officer_set';
	case state = 'state';
	case region = 'region';
	case term = 'term';
	case conflict = 'conflict';
}