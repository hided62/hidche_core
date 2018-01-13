<?php

////////////////////////////////////////////////////////////////////////////
// 장수 테이블
////////////////////////////////////////////////////////////////////////////

$general_schema = "

  create table general (
    no       int not null auto_increment,
    user_id  char(32) not null,
    password char(32) not null,
    conmsg   char(255) default '',
    npcmsg   char(255) default '',
    npcid    int(5) default 0,
    npc      int(1) default 0,
    npc_org  int(1) default 0,
    npcmatch int(3) default 0,
    bornyear int(3) default 180,
    deadyear int(3) default 300,
    newmsg   int(1) default 0,
    con      int(6) default 0,      connect  int(6) default 0,  refresh int(6) default 0,
    logcnt   int(6) default 1,      refcnt   int(6) default 1,
    picture  char(32) not null,     imgsvr   int(1) default 0,
    name     char(32) not null,
    name2    char(32) NULL DEFAULT NULL,
    nation   int(6) default 0,
    nations  char(64) default ',0,',
    city     int(6) default 0,
    troop    int(6) default 0,
    leader   int(3) default 0,      leader2 int(3) default 0,
    power    int(3) default 0,      power2  int(3) default 0,
    intel    int(3) default 0,      intel2  int(3) default 0,
    injury   int(2) default 0,
    experience int(6) default 0,    dedication int(6) default 0,
    dex0     int(8) default 0,      dex10   int(8) default 0,
    dex20    int(8) default 0,      dex30   int(8) default 0,
    dex40    int(8) default 0,
    level int(2) default 0,
    gold  int(6) default 0,         rice  int(6) default 0,
    crew  int(5) default 0,         crewtype  int(2) default 0,
    train int(3) default 0,         atmos int(3) default 0,
    weap  int(2) default 0,         book  int(2) default 0,
    horse int(2) default 0,         item  int(2) default 0,
    turntime datetime,              recwar  datetime,
    makenation char(255),           makelimit int(2) default 24,
    killturn int(3),
    lastconnect datetime,           lastrefresh datetime,
    ip char(255) default '',
    block int(1) default 0,
    dedlevel    int(2) default 0,   explevel    int(2) default 0,
    firenum     int(3) default 0,   warnum      int(3) default 0,
    killnum     int(3) default 0,   deathnum    int(3) default 0,
    killcrew    int(7) default 0,   deathcrew   int(7) default 0,
    age         int(3) default 20,  startage    int(3) default 20,
    history  text default '',
    belong   int(2) default 1,
    betray   int(2) default 0,
    personal int(2) default 0,
    special  int(2) default 0,      specage     int(2) default 0,
    special2 int(2) default 0,      specage2    int(2) default 0,
    skin     int(3) default 1,
    mode     int(1) default 2,      tnmt        int(1) default 1,   map     int(1) default 0,
    myset    int(1) default 3,
    userlevel  int(1) default 1,
    tournament int(1) default 0,
    vote       int(1) default 0,
    newvote    int(1) default 0,

    ttw  int(4) default 0,  ttd  int(4) default 0,  ttl  int(4) default 0,  ttg  int(4) default 0,  ttp  int(4) default 0,
    tlw  int(4) default 0,  tld  int(4) default 0,  tll  int(4) default 0,  tlg  int(4) default 0,  tlp  int(4) default 0,
    tpw  int(4) default 0,  tpd  int(4) default 0,  tpl  int(4) default 0,  tpg  int(4) default 0,  tpp  int(4) default 0,
    tiw  int(4) default 0,  tid  int(4) default 0,  til  int(4) default 0,  tig  int(4) default 0,  tip  int(4) default 0,

    bet0  int(8) default 0, bet1  int(8) default 0, bet2  int(8) default 0, bet3  int(8) default 0,
    bet4  int(8) default 0, bet5  int(8) default 0, bet6  int(8) default 0, bet7  int(8) default 0,
    bet8  int(8) default 0, bet9  int(8) default 0, bet10 int(8) default 0, bet11 int(8) default 0,
    bet12 int(8) default 0, bet13 int(8) default 0, bet14 int(8) default 0, bet15 int(8) default 0,
    betwin int(8) default 0, betgold int(8) default 0, betwingold int(8) default 0,

    term int(4) default 0,
    turn0  char(14) default '00000000000000', turn1  char(14) default '00000000000000', turn2  char(14) default '00000000000000',
    turn3  char(14) default '00000000000000', turn4  char(14) default '00000000000000', turn5  char(14) default '00000000000000',
    turn6  char(14) default '00000000000000', turn7  char(14) default '00000000000000', turn8  char(14) default '00000000000000',
    turn9  char(14) default '00000000000000', turn10 char(14) default '00000000000000', turn11 char(14) default '00000000000000',
    turn12 char(14) default '00000000000000', turn13 char(14) default '00000000000000', turn14 char(14) default '00000000000000',
    turn15 char(14) default '00000000000000', turn16 char(14) default '00000000000000', turn17 char(14) default '00000000000000',
    turn18 char(14) default '00000000000000', turn19 char(14) default '00000000000000', turn20 char(14) default '00000000000000',
    turn21 char(14) default '00000000000000', turn22 char(14) default '00000000000000', turn23 char(14) default '00000000000000',
    recturn char(14) default '', resturn char(14) default '',
    msg0 char(150) default '', msg0_type int(4) default 0, msg0_who int(9) default 0, msg0_when datetime,
    msg1 char(150) default '', msg1_type int(4) default 0, msg1_who int(9) default 0, msg1_when datetime,
    msg2 char(150) default '', msg2_type int(4) default 0, msg2_who int(9) default 0, msg2_when datetime,
    msg3 char(150) default '', msg3_type int(4) default 0, msg3_who int(9) default 0, msg3_when datetime,
    msg4 char(150) default '', msg4_type int(4) default 0, msg4_who int(9) default 0, msg4_when datetime,
    msg5 char(150) default '', msg5_type int(4) default 0, msg5_who int(9) default 0, msg5_when datetime,
    msg6 char(150) default '', msg6_type int(4) default 0, msg6_who int(9) default 0, msg6_when datetime,
    msg7 char(150) default '', msg7_type int(4) default 0, msg7_who int(9) default 0, msg7_when datetime,
    msg8 char(150) default '', msg8_type int(4) default 0, msg8_who int(9) default 0, msg8_when datetime,
    msg9 char(150) default '', msg9_type int(4) default 0, msg9_who int(9) default 0, msg9_when datetime,
    msgindex int(2) default 9,

    PRIMARY KEY (no),
    KEY (user_id),
    KEY (nation),
    KEY (city),
    KEY (turntime)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

   ";

///////////////////////////////////////////////////////////////////////////
// 국가 테이블
///////////////////////////////////////////////////////////////////////////

$nation_schema = "

  create table nation (
    nation  int(6) not null auto_increment,
    name    char(64) not null,
    color   char(10) not null,  colset int(1) default 1,
    onlinegen   varchar(1024) default '',
    msg     text default '',
    capital int(1) default 0,   capset int(1) default 0,
    gennum  int(3) default 1,
    gennum2 int(3) default 1,
    chemi   int(3) default 0,
    gold    int(8) default 0,
    rice    int(8) default 0,
    bill    int(3) default 0,
    rate    int(3) default 0,
    rate_tmp int(3) default 0,
    secretlimit int(2) default 3,
    l12set  int(1) default 0,
    l11set  int(1) default 0,
    l10set  int(1) default 0,
    l9set int(1) default 0,
    l8set int(1) default 0,
    l7set int(1) default 0,
    l6set int(1) default 0,
    l5set int(1) default 0,
    scout int(1) default 0,
    war     int(1) default 0,
    myset   int(1) default 3,
    tricklimit int(4) default 36,
    surlimit int(4) default 72,
    scoutmsg text default '',
    tech int(8) default 0,  totaltech int(8) default 0,
    power int(8) default 0,
    spy  char(255) default '',
    level int(1) default 0,
    type int(2) default 0,
    rule text default '',
    history text default '',
    dip0 char(150) default '', dip0_type int(4) default 0, dip0_who int(9) default 0, dip0_when datetime,
    dip1 char(150) default '', dip1_type int(4) default 0, dip1_who int(9) default 0, dip1_when datetime,
    dip2 char(150) default '', dip2_type int(4) default 0, dip2_who int(9) default 0, dip2_when datetime,
    dip3 char(150) default '', dip3_type int(4) default 0, dip3_who int(9) default 0, dip3_when datetime,
    dip4 char(150) default '', dip4_type int(4) default 0, dip4_who int(9) default 0, dip4_when datetime,
    dip5 char(150) default '', dip5_type int(4) default 0, dip5_who int(9) default 0, dip5_when datetime,
    board0  text default '', board0_who  int(6) default 0, board0_when  datetime,
    board1  text default '', board1_who  int(6) default 0, board1_when  datetime,
    board2  text default '', board2_who  int(6) default 0, board2_when  datetime,
    board3  text default '', board3_who  int(6) default 0, board3_when  datetime,
    board4  text default '', board4_who  int(6) default 0, board4_when  datetime,
    board5  text default '', board5_who  int(6) default 0, board5_when  datetime,
    board6  text default '', board6_who  int(6) default 0, board6_when  datetime,
    board7  text default '', board7_who  int(6) default 0, board7_when  datetime,
    board8  text default '', board8_who  int(6) default 0, board8_when  datetime,
    board9  text default '', board9_who  int(6) default 0, board9_when  datetime,
    board10 text default '', board10_who int(6) default 0, board10_when datetime,
    board11 text default '', board11_who int(6) default 0, board11_when datetime,
    board12 text default '', board12_who int(6) default 0, board12_when datetime,
    board13 text default '', board13_who int(6) default 0, board13_when datetime,
    board14 text default '', board14_who int(6) default 0, board14_when datetime,
    board15 text default '', board15_who int(6) default 0, board15_when datetime,
    board16 text default '', board16_who int(6) default 0, board16_when datetime,
    board17 text default '', board17_who int(6) default 0, board17_when datetime,
    board18 text default '', board18_who int(6) default 0, board18_when datetime,
    board19 text default '', board19_who int(6) default 0, board19_when datetime,
    coreboard0  text default '', coreboard0_who  int(6) default 0, coreboard0_when  datetime,
    coreboard1  text default '', coreboard1_who  int(6) default 0, coreboard1_when  datetime,
    coreboard2  text default '', coreboard2_who  int(6) default 0, coreboard2_when  datetime,
    coreboard3  text default '', coreboard3_who  int(6) default 0, coreboard3_when  datetime,
    coreboard4  text default '', coreboard4_who  int(6) default 0, coreboard4_when  datetime,
    coreboard5  text default '', coreboard5_who  int(6) default 0, coreboard5_when  datetime,
    coreboard6  text default '', coreboard6_who  int(6) default 0, coreboard6_when  datetime,
    coreboard7  text default '', coreboard7_who  int(6) default 0, coreboard7_when  datetime,
    coreboard8  text default '', coreboard8_who  int(6) default 0, coreboard8_when  datetime,
    coreboard9  text default '', coreboard9_who  int(6) default 0, coreboard9_when  datetime,
    coreboard10 text default '', coreboard10_who int(6) default 0, coreboard10_when datetime,
    coreboard11 text default '', coreboard11_who int(6) default 0, coreboard11_when datetime,
    coreboard12 text default '', coreboard12_who int(6) default 0, coreboard12_when datetime,
    coreboard13 text default '', coreboard13_who int(6) default 0, coreboard13_when datetime,
    coreboard14 text default '', coreboard14_who int(6) default 0, coreboard14_when datetime,
    coreboard15 text default '', coreboard15_who int(6) default 0, coreboard15_when datetime,
    coreboard16 text default '', coreboard16_who int(6) default 0, coreboard16_when datetime,
    coreboard17 text default '', coreboard17_who int(6) default 0, coreboard17_when datetime,
    coreboard18 text default '', coreboard18_who int(6) default 0, coreboard18_when datetime,
    coreboard19 text default '', coreboard19_who int(6) default 0, coreboard19_when datetime,
    boardindex int(2) default 19,
    coreindex int(2) default 19,
    l12term   int(4)   default 0,                l11term   int(4)   default 0,                l10term   int(4)   default 0,                l9term   int(4)   default 0,
    l12turn0  char(14) default '00000000000099', l11turn0  char(14) default '00000000000099', l10turn0  char(14) default '00000000000099', l9turn0  char(14) default '00000000000099',
    l12turn1  char(14) default '00000000000099', l11turn1  char(14) default '00000000000099', l10turn1  char(14) default '00000000000099', l9turn1  char(14) default '00000000000099',
    l12turn2  char(14) default '00000000000099', l11turn2  char(14) default '00000000000099', l10turn2  char(14) default '00000000000099', l9turn2  char(14) default '00000000000099',
    l12turn3  char(14) default '00000000000099', l11turn3  char(14) default '00000000000099', l10turn3  char(14) default '00000000000099', l9turn3  char(14) default '00000000000099',
    l12turn4  char(14) default '00000000000099', l11turn4  char(14) default '00000000000099', l10turn4  char(14) default '00000000000099', l9turn4  char(14) default '00000000000099',
    l12turn5  char(14) default '00000000000099', l11turn5  char(14) default '00000000000099', l10turn5  char(14) default '00000000000099', l9turn5  char(14) default '00000000000099',
    l12turn6  char(14) default '00000000000099', l11turn6  char(14) default '00000000000099', l10turn6  char(14) default '00000000000099', l9turn6  char(14) default '00000000000099',
    l12turn7  char(14) default '00000000000099', l11turn7  char(14) default '00000000000099', l10turn7  char(14) default '00000000000099', l9turn7  char(14) default '00000000000099',
    l12turn8  char(14) default '00000000000099', l11turn8  char(14) default '00000000000099', l10turn8  char(14) default '00000000000099', l9turn8  char(14) default '00000000000099',
    l12turn9  char(14) default '00000000000099', l11turn9  char(14) default '00000000000099', l10turn9  char(14) default '00000000000099', l9turn9  char(14) default '00000000000099',
    l12turn10 char(14) default '00000000000099', l11turn10 char(14) default '00000000000099', l10turn10 char(14) default '00000000000099', l9turn10 char(14) default '00000000000099',
    l12turn11 char(14) default '00000000000099', l11turn11 char(14) default '00000000000099', l10turn11 char(14) default '00000000000099', l9turn11 char(14) default '00000000000099',

    l8term  int(4)   default 0,                 l7term   int(4)   default 0,                l6term   int(4)   default 0,                l5term   int(4)   default 0,
    l8turn0  char(14) default '00000000000099', l7turn0  char(14) default '00000000000099', l6turn0  char(14) default '00000000000099', l5turn0  char(14) default '00000000000099',
    l8turn1  char(14) default '00000000000099', l7turn1  char(14) default '00000000000099', l6turn1  char(14) default '00000000000099', l5turn1  char(14) default '00000000000099',
    l8turn2  char(14) default '00000000000099', l7turn2  char(14) default '00000000000099', l6turn2  char(14) default '00000000000099', l5turn2  char(14) default '00000000000099',
    l8turn3  char(14) default '00000000000099', l7turn3  char(14) default '00000000000099', l6turn3  char(14) default '00000000000099', l5turn3  char(14) default '00000000000099',
    l8turn4  char(14) default '00000000000099', l7turn4  char(14) default '00000000000099', l6turn4  char(14) default '00000000000099', l5turn4  char(14) default '00000000000099',
    l8turn5  char(14) default '00000000000099', l7turn5  char(14) default '00000000000099', l6turn5  char(14) default '00000000000099', l5turn5  char(14) default '00000000000099',
    l8turn6  char(14) default '00000000000099', l7turn6  char(14) default '00000000000099', l6turn6  char(14) default '00000000000099', l5turn6  char(14) default '00000000000099',
    l8turn7  char(14) default '00000000000099', l7turn7  char(14) default '00000000000099', l6turn7  char(14) default '00000000000099', l5turn7  char(14) default '00000000000099',
    l8turn8  char(14) default '00000000000099', l7turn8  char(14) default '00000000000099', l6turn8  char(14) default '00000000000099', l5turn8  char(14) default '00000000000099',
    l8turn9  char(14) default '00000000000099', l7turn9  char(14) default '00000000000099', l6turn9  char(14) default '00000000000099', l5turn9  char(14) default '00000000000099',
    l8turn10 char(14) default '00000000000099', l7turn10 char(14) default '00000000000099', l6turn10 char(14) default '00000000000099', l5turn10 char(14) default '00000000000099',
    l8turn11 char(14) default '00000000000099', l7turn11 char(14) default '00000000000099', l6turn11 char(14) default '00000000000099', l5turn11 char(14) default '00000000000099',

    PRIMARY KEY (nation)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 도시 테이블
///////////////////////////////////////////////////////////////////////////
// trade 100 이 표준 시세
$city_schema = "

  create table city (
    city   int(6) not null auto_increment,
    name   char(64) not null,
    level  int(1) default 0,
    upgrading int(1) default 0,
    nation int(6) default 0,
    supply int(1) default 1,
    path   char(32) default '',
    front  int(1) default 0,
    pop    int(7) default 50000,
    pop2   int(7) default 50000,
    agri   int(5) default 0,
    agri2  int(5) default 0,
    comm   int(5) default 0,
    comm2  int(5) default 0,
    secu   int(5) default 0,
    secu2  int(5) default 0,
    rate   int(3) default 0,
    trade  int(3) default 100,
    dead   int(7) default 0,
    def    int(5) default 0,
    def2   int(5) default 0,
    wall   int(5) default 0,
    wall2  int(5) default 0,
    gen1   int(4) default 0,
    gen2   int(4) default 0,
    gen3   int(4) default 0,
    gen1set int(1) default 0,
    gen2set int(1) default 0,
    gen3set int(1) default 0,
    state   int(2) default 0,
    region  int(2) default 0,
    term    int(1) default 0,
    conflict    char(255) default '',
    conflict2   char(255) default '',

    PRIMARY KEY (city),
    KEY (nation)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 부대 테이블
///////////////////////////////////////////////////////////////////////////

$troop_schema = "

  create table troop (
    troop int(6) not null auto_increment,
    name char(64) not null,
    nation int(6) not null,
    no int(6) not null,

    PRIMARY KEY (troop)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 토큰 테이블
///////////////////////////////////////////////////////////////////////////

$token_schema = "

  create table token (
    token int(6) not null auto_increment,
    id    char(16) not null,
    leader int(3) not null,
    power int(3) not null,
    intel int(3) not null,

    PRIMARY KEY (token),
    KEY(id)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

//////////////////////////////////////////////////////////////////////////
//  락 테이블
//////////////////////////////////////////////////////////////////////////

$plock_schema = "

  create table plock (
    no          int(11) not null auto_increment,
    plock       int(1) default 0,

    PRIMARY KEY (no)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

//////////////////////////////////////////////////////////////////////////
// 게임 테이블
//////////////////////////////////////////////////////////////////////////

$game_schema = "

  create table game (
    no          int(11) not null auto_increment,
    startyear   int(3) not null,
    year        int(3) not null,
    month       int(3) not null,
    refresh     int(9) default 0,
    maxonline   int(9) default 1,
    maxrefresh  int(9) default 1,
    conlimit    int(9) default 5,
    conweight   int(3) default 100,
    develcost   int(4) default 20,
    online      int(9) default 0,
    onlinenation    varchar(256) default '',
    onlinegen   varchar(1024) default '',
    msg         text default '',
    maxgeneral  int(3) default 100,
    genius      int(2) default 3,
    normgeneral int(3) default 100,
    maxnation   int(3) default 50,
    gold_rate   int(3) default 100,
    rice_rate   int(3) default 100,
    city_rate   int(3) default 50,
    turnterm    int(1) default 1,
    killturn    int(6) default 80,
    turntime    datetime,
    starttime   datetime,
    isUnited    int(1) default 0,
    scenario    int(2) default 0,
    img         int(1) default 0,
    extend      int(1) default 0,
    fiction     int(1) default 0,
    npcmode     int(1) default 0,
    tnmt_auto   int(2) default 0,   tnmt_time   datetime  default '2100-01-01 00:00:00',
    tournament  int(2) default 0,   phase       int(2)    default 0,
    tnmt_type   int(2) default 0,   tnmt_msg    char(255) default '',
    tnmt_trig   int(2) default 0,
    voteopen    int(1) default 1,
    vote        text default '',
    votecomment text default '',
    npccount    int(4) default 0,

    bet0  int(8) default 0, bet1  int(8) default 0, bet2  int(8) default 0, bet3  int(8) default 0,
    bet4  int(8) default 0, bet5  int(8) default 0, bet6  int(8) default 0, bet7  int(8) default 0,
    bet8  int(8) default 0, bet9  int(8) default 0, bet10 int(8) default 0, bet11 int(8) default 0,
    bet12 int(8) default 0, bet13 int(8) default 0, bet14 int(8) default 0, bet15 int(8) default 0,

    att0  int(3) default 0, def0  int(3) default 0, spd0  int(3) default 0, avd0  int(3) default 0, ric0  int(3) default 0, cst0  int(3) default 0,
    att1  int(3) default 0, def1  int(3) default 0, spd1  int(3) default 0, avd1  int(3) default 0, ric1  int(3) default 0, cst1  int(3) default 0,
    att2  int(3) default 0, def2  int(3) default 0, spd2  int(3) default 0, avd2  int(3) default 0, ric2  int(3) default 0, cst2  int(3) default 0,
    att3  int(3) default 0, def3  int(3) default 0, spd3  int(3) default 0, avd3  int(3) default 0, ric3  int(3) default 0, cst3  int(3) default 0,
    att4  int(3) default 0, def4  int(3) default 0, spd4  int(3) default 0, avd4  int(3) default 0, ric4  int(3) default 0, cst4  int(3) default 0,
    att5  int(3) default 0, def5  int(3) default 0, spd5  int(3) default 0, avd5  int(3) default 0, ric5  int(3) default 0, cst5  int(3) default 0,

    att10 int(3) default 0, def10 int(3) default 0, spd10 int(3) default 0, avd10 int(3) default 0, ric10 int(3) default 0, cst10 int(3) default 0,
    att11 int(3) default 0, def11 int(3) default 0, spd11 int(3) default 0, avd11 int(3) default 0, ric11 int(3) default 0, cst11 int(3) default 0,
    att12 int(3) default 0, def12 int(3) default 0, spd12 int(3) default 0, avd12 int(3) default 0, ric12 int(3) default 0, cst12 int(3) default 0,
    att13 int(3) default 0, def13 int(3) default 0, spd13 int(3) default 0, avd13 int(3) default 0, ric13 int(3) default 0, cst13 int(3) default 0,
    att14 int(3) default 0, def14 int(3) default 0, spd14 int(3) default 0, avd14 int(3) default 0, ric14 int(3) default 0, cst14 int(3) default 0,

    att20 int(3) default 0, def20 int(3) default 0, spd20 int(3) default 0, avd20 int(3) default 0, ric20 int(3) default 0, cst20 int(3) default 0,
    att21 int(3) default 0, def21 int(3) default 0, spd21 int(3) default 0, avd21 int(3) default 0, ric21 int(3) default 0, cst21 int(3) default 0,
    att22 int(3) default 0, def22 int(3) default 0, spd22 int(3) default 0, avd22 int(3) default 0, ric22 int(3) default 0, cst22 int(3) default 0,
    att23 int(3) default 0, def23 int(3) default 0, spd23 int(3) default 0, avd23 int(3) default 0, ric23 int(3) default 0, cst23 int(3) default 0,
    att24 int(3) default 0, def24 int(3) default 0, spd24 int(3) default 0, avd24 int(3) default 0, ric24 int(3) default 0, cst24 int(3) default 0,
    att25 int(3) default 0, def25 int(3) default 0, spd25 int(3) default 0, avd25 int(3) default 0, ric25 int(3) default 0, cst25 int(3) default 0,
    att26 int(3) default 0, def26 int(3) default 0, spd26 int(3) default 0, avd26 int(3) default 0, ric26 int(3) default 0, cst26 int(3) default 0,
    att27 int(3) default 0, def27 int(3) default 0, spd27 int(3) default 0, avd27 int(3) default 0, ric27 int(3) default 0, cst27 int(3) default 0,

    att30 int(3) default 0, def30 int(3) default 0, spd30 int(3) default 0, avd30 int(3) default 0, ric30 int(3) default 0, cst30 int(3) default 0,
    att31 int(3) default 0, def31 int(3) default 0, spd31 int(3) default 0, avd31 int(3) default 0, ric31 int(3) default 0, cst31 int(3) default 0,
    att32 int(3) default 0, def32 int(3) default 0, spd32 int(3) default 0, avd32 int(3) default 0, ric32 int(3) default 0, cst32 int(3) default 0,
    att33 int(3) default 0, def33 int(3) default 0, spd33 int(3) default 0, avd33 int(3) default 0, ric33 int(3) default 0, cst33 int(3) default 0,
    att34 int(3) default 0, def34 int(3) default 0, spd34 int(3) default 0, avd34 int(3) default 0, ric34 int(3) default 0, cst34 int(3) default 0,
    att35 int(3) default 0, def35 int(3) default 0, spd35 int(3) default 0, avd35 int(3) default 0, ric35 int(3) default 0, cst35 int(3) default 0,
    att36 int(3) default 0, def36 int(3) default 0, spd36 int(3) default 0, avd36 int(3) default 0, ric36 int(3) default 0, cst36 int(3) default 0,
    att37 int(3) default 0, def37 int(3) default 0, spd37 int(3) default 0, avd37 int(3) default 0, ric37 int(3) default 0, cst37 int(3) default 0,
    att38 int(3) default 0, def38 int(3) default 0, spd38 int(3) default 0, avd38 int(3) default 0, ric38 int(3) default 0, cst38 int(3) default 0,

    att40 int(3) default 0, def40 int(3) default 0, spd40 int(3) default 0, avd40 int(3) default 0, ric40 int(3) default 0, cst40 int(3) default 0,
    att41 int(3) default 0, def41 int(3) default 0, spd41 int(3) default 0, avd41 int(3) default 0, ric41 int(3) default 0, cst41 int(3) default 0,
    att42 int(3) default 0, def42 int(3) default 0, spd42 int(3) default 0, avd42 int(3) default 0, ric42 int(3) default 0, cst42 int(3) default 0,
    att43 int(3) default 0, def43 int(3) default 0, spd43 int(3) default 0, avd43 int(3) default 0, ric43 int(3) default 0, cst43 int(3) default 0,

    PRIMARY KEY (no)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 명전 테이블
///////////////////////////////////////////////////////////////////////////

$hall_schema = "

  create table hall (
    no int(6) not null auto_increment,
    type int(2) default 0,
    rank int(2) default 0,
    name char(64) default '',
    nation char(12) default '',
    data int(5) default 0,
    color char(12) default '',
    picture char(32) default '',

    PRIMARY KEY (no)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 왕조 테이블
///////////////////////////////////////////////////////////////////////////

$emperior_schema = "

  create table emperior (
    no      int(6) not null auto_increment,
    phase   char(255) default '',
    nation_count    char(64) default '',
    nation_name     text default '',
    nation_hist     text default '',
    gen_count       char(64) default '',
    personal_hist   text default '',
    special_hist    text default '',
    name    char(64) default '',
    type    char(64) default '',
    color   char(6) default '',
    year    int(4) default 0,
    month   int(2) default 0,
    power   int(8) default 0,
    gennum  int(3) default 0,
    citynum int(3) default 0,
    pop     char(255) default 0,
    poprate char(255) default '',
    gold    int(9) default 0,
    rice    int(9) default 0,
    l12name char(64) default '', l12pic char(32) default '',
    l11name char(64) default '', l11pic char(32) default '',
    l10name char(64) default '', l10pic char(32) default '',
    l9name  char(64) default '', l9pic char(32) default '',
    l8name  char(64) default '', l8pic char(32) default '',
    l7name  char(64) default '', l7pic char(32) default '',
    l6name  char(64) default '', l6pic char(32) default '',
    l5name  char(64) default '', l5pic char(32) default '',
    tiger   char(64) default '',
    eagle   char(64) default '',
    gen     text default '',
    history text default '',

    PRIMARY KEY (no)
    ) ENGINE=INNODB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 외교 테이블
///////////////////////////////////////////////////////////////////////////

$diplomacy_schema = "

  create table diplomacy (
    no int(6) not null auto_increment,
    me int(6) default 0,
    you int(6) default 0,
    state int(6) default 0,
    term int(6) default 0,
    dead int(8) default 0,
    fixed char(128) default '',
    reserved char(128) default '',
    showing datetime,

    PRIMARY KEY (no)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 토너먼트 테이블
///////////////////////////////////////////////////////////////////////////

$tournament_schema = "

  create table tournament (
    seq  int(6) not null auto_increment,
    no   int(6) default 0,
    npc  int(6) default 0,
    name char(64) default '',
    w    int(2) default 0,
    b    int(2) default 0,
    h    int(2) default 0,
    ldr  int(3) default 0,
    pwr  int(3) default 0,
    itl  int(3) default 0,
    lvl  int(3) default 0,
    grp  int(2) default 0,
    grp_no int(2) default 0,
    win  int(2) default 0,
    draw int(2) default 0,
    lose int(2) default 0,
    gl   int(2) default 0,
    prmt int(1) default 0,
    PRIMARY KEY (seq)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 거래 테이블
///////////////////////////////////////////////////////////////////////////

$auction_schema = "

  create table auction (
    no     int(6) not null auto_increment,
    type   int(6) default 0,
    no1    int(6) default 0,
    name1  char(64) default '-',
    stuff  int(6) default 0,
    amount int(6) default 0,
    cost   int(6) default 0,
    value  int(6) default 0,
    topv   int(6) default 0,
    no2    int(6) default 0,
    name2  char(64) default '-',
    expire datetime,

    PRIMARY KEY (no)
    ) ENGINE=INNODB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 통계 테이블
///////////////////////////////////////////////////////////////////////////

$statistic_schema = "

  create table statistic (
    no              int(6) not null auto_increment,
    year            int(4) default 0,
    month           int(2) default 0,
    nation_count    int(2) default 0,
    nation_name     text default '',
    nation_hist     text default '',
    gen_count       varchar(32) default '',
    personal_hist   text default '',
    special_hist    text default '',
    power_hist      text default '',
    crewtype        text default '',
    etc             text default '',

    PRIMARY KEY (no)
    ) ENGINE=INNODB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=UTF8

    ";

///////////////////////////////////////////////////////////////////////////
// 연감 테이블
///////////////////////////////////////////////////////////////////////////

$history_schema = "

  create table history (
    no      int(6) not null auto_increment,
    year    int(4) default 0,
    month   int(2) default 0,
    map     longtext default '',
    log     text default '',
    genlog  text default '',
    nation  text default '',
    power   text default '',
    gen     text default '',
    city    text default '',

    PRIMARY KEY (no)
    ) ENGINE=INNODB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=UTF8

    ";

