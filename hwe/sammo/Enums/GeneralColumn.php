<?php
namespace sammo\Enums;

/**
 * General Table column
 * schemal.sql과 일치
 */
enum GeneralColumn: string{
  case no = 'no';
	case owner = 'owner';
	case npcmsg = 'npcmsg';
	case npc = 'npc';
	case npc_org = 'npc_org';
	case affinity = 'affinity';
	case bornyear = 'bornyear';
	case deadyear = 'deadyear';
	case newmsg = 'newmsg';
	case con = 'con';
	case connect = 'connect';
	case refresh = 'refresh';
	case logcnt = 'logcnt';
	case refcnt = 'refcnt';
	case picture = 'picture';
	case imgsvr = 'imgsvr';
	case name = 'name';
	case owner_name = 'owner_name';
	case nation = 'nation';
	case city = 'city';
	case troop = 'troop';
	case leadership = 'leadership';
	case leadership_exp = 'leadership_exp';
	case strength = 'strength';
	case strength_exp = 'strength_exp';
	case intel = 'intel';
	case intel_exp = 'intel_exp';
	case injury = 'injury';
	case experience = 'experience';
	case dedication = 'dedication';
	case dex1 = 'dex1';
	case dex2 = 'dex2';
	case dex3 = 'dex3';
	case dex4 = 'dex4';
	case dex5 = 'dex5';
	case officer_level = 'officer_level';
	case officer_city = 'officer_city';
	case permission = 'permission';
	case gold = 'gold';
	case rice = 'rice';
	case crew = 'crew';
	case crewtype = 'crewtype';
	case train = 'train';
	case atmos = 'atmos';
	case weapon = 'weapon';
	case book = 'book';
	case horse = 'horse';
	case item = 'item';
	case turntime = 'turntime';
	case recent_war = 'recent_war';
	case makelimit = 'makelimit';
	case killturn = 'killturn';
	case lastconnect = 'lastconnect';
	case lastrefresh = 'lastrefresh';
	case ip = 'ip';
	case block = 'block';
	case dedlevel = 'dedlevel';
	case explevel = 'explevel';
	case age = 'age';
	case startage = 'startage';
	case belong = 'belong';
	case betray = 'betray';
	case personal = 'personal';
	case special = 'special';
	case specage = 'specage';
	case special2 = 'special2';
	case specage2 = 'specage2';
	case defence_train = 'defence_train';
	case tnmt = 'tnmt';
	case myset = 'myset';
	case tournament = 'tournament';
	case newvote = 'newvote';
	case last_turn = 'last_turn';
	case aux = 'aux';
	case penalty = 'penalty';
}