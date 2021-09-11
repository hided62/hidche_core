<?php

namespace sammo;

use sammo\Command\BaseCommand;

/**
 * Value Converter
 *
 * Side effect 없이 값의 변환만을 수행하는 함수들의 모음.
 * (단, autoload, 정적 변수 초기화는 허용)
 */

function getCharacterList(bool $onlyAvailable=true){
    $infoText = [];
    if(!$onlyAvailable){
        $class = buildPersonalityClass(GameConst::$neutralPersonality);
        $infoText[GameConst::$neutralPersonality] = [$class->getName(), $class->getInfo()];
    }
    foreach(GameConst::$availablePersonality as $personalityID){
        $class = buildPersonalityClass($personalityID);
        $infoText[$personalityID] = [$class->getName(), $class->getInfo()];
    }
    if(!$onlyAvailable){
        foreach(GameConst::$optionalPersonality as $personalityID){
            $class = buildPersonalityClass($personalityID);
            $infoText[$personalityID] = [$class->getName(), $class->getInfo()];
        }
    }
    return $infoText;
}

function isOfficerSet(int $officerSet, int $reqOfficerLevel):bool{
    return ($officerSet & (1 << $reqOfficerLevel)) !== 0;
}

function doOfficerSet(int $officerSet, int $reqOfficerLevel):int{
    return ($officerSet | (1 << $reqOfficerLevel));
}

function getNationChiefLevel(int $level) {
    return [
        7=>5,
        6=>5,
        5=>7,
        4=>7,
        3=>9,
        2=>9,
        1=>11,
        0=>11,
    ][$level];
}

function getNationLevel(int $level) {
    return [
        7=>'황제',
        6=>'왕',
        5=>'공',
        4=>'주목',
        3=>'주자사',
        2=>'군벌',
        1=>'호족',
        0=>'방랑군',
    ][$level];
}

function getGenChar(?string $type) {
    if($type === null){
        return '-';
    }
    return buildPersonalityClass($type)->getName();
}

function getGeneralSpecialDomesticName(?string $type):string{
    if($type === null){
        return '-';
    }
    return buildGeneralSpecialDomesticClass($type)->getName();
}

function getGeneralSpecialWarName(?string $type):string{
    if($type === null){
        return '-';
    }
    return buildGeneralSpecialWarClass($type)->getName();
}

function getSpecialTextList():array{
    static $list = null;
    if($list !== null){
        return $list;
    }

    $list = ['None' => ['-', null]];
    foreach(GameConst::$availableSpecialDomestic as $specialKey){
        $specialClass = buildGeneralSpecialDomesticClass($specialKey);
        $list[$specialKey] = [$specialClass->getName(), $specialClass->getInfo()];
    }

    foreach(GameConst::$availableSpecialWar as $specialKey){
        $specialClass = buildGeneralSpecialWarClass($specialKey);
        $list[$specialKey] = [$specialClass->getName(), $specialClass->getInfo()];
    }

    return $list;
}

function getSpecialInfo(?string $type):?string{
    if($type === null){
        return null;
    }

    $infoText = getSpecialTextList();

    return $infoText[$type][1]??null;
}

function getNationType(?string $type) {
    $nationClass = buildNationTypeClass($type);
    $text = $nationClass->getName();
    $text = join(' ', StringUtil::splitString($text));

    return $text;
}


function getConnect($con) {
    if($con < 50)        $conname = '안함';
    elseif($con <   100) $conname = '무관심';
    elseif($con <   200) $conname = '가끔';
    elseif($con <   400) $conname = '보통';
    elseif($con <   800) $conname = '자주';
    elseif($con <  1600) $conname = '열심';
    elseif($con <  3200) $conname = '중독';
    elseif($con <  6400) $conname = '폐인';
    elseif($con < 12800) $conname = '경고';
    else $conname = '헐...';

    return $conname;
}

function getNationType2(?string $type) {
    $nationClass = getNationTypeClass($type);

    [$pros, $cons] = [$nationClass::$pros, $nationClass::$cons];
    return "<font color=cyan>{$pros}</font> <font color=magenta>{$cons}</font>";
}

function getNationTypeClass(?string $type){
    if($type === null || $type === ''){
        $type = GameConst::$neutralNationType;
    }

    static $basePath = __NAMESPACE__.'\\ActionNationType\\';
    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    $classPath = ($basePath.'che_'.$type);
    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}은 올바른 국가 타입 클래스가 아님");
}

function buildNationTypeClass(?string $type):BaseNation{
    static $cache = [];
    if($type === null){
        $type = 'None';
    }
    if(key_exists($type, $cache)){
        return $cache[$type];
    }
    $class = getNationTypeClass($type);

    $obj = new $class();
    $cache[$type]= $obj;
    return $obj;
}

function getPersonalityClass(?string $type){
    if($type === null || $type === ''){
        $type = GameConst::$neutralPersonality;
    }

    static $basePath = __NAMESPACE__.'\\ActionPersonality\\';
    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    $classPath = ($basePath.'che_'.$type);
    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}은 올바른 성격 클래스가 아님");
}

function buildPersonalityClass(?string $type):iAction{
    static $cache = [];
    if($type === null){
        $type = 'None';
    }
    if(key_exists($type, $cache)){
        return $cache[$type];
    }
    $class = getPersonalityClass($type);

    $obj = new $class();
    $cache[$type]= $obj;
    return $obj;
}

function getItemClass(?string $type){
    if($type === null){
        $type = 'None';
    }

    static $basePath = __NAMESPACE__.'\\ActionItem\\';

    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}는 올바른 아이템 클래스가 아님");
}

function buildItemClass(?string $type):BaseItem{
    static $cache = [];
    if($type === null){
        $type = 'None';
    }
    if(key_exists($type, $cache)){
        return $cache[$type];
    }
    $class = getItemClass($type);

    $obj = new $class();
    $cache[$type]= $obj;
    return $obj;
}

function getGeneralSpecialDomesticClass(?string $type){
    if($type === null || $type === ''){
        $type = GameConst::$defaultSpecialDomestic;
    }

    static $basePath = __NAMESPACE__.'\\ActionSpecialDomestic\\';
    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    $classPath = ($basePath.'che_'.$type);
    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}은 올바른 내정 특기가 아님");
}

function buildGeneralSpecialDomesticClass(?string $type):BaseSpecial{
    static $cache = [];
    if($type === null){
        $type = 'None';
    }
    if(key_exists($type, $cache)){
        return $cache[$type];
    }
    $class = getGeneralSpecialDomesticClass($type);

    $obj = new $class();
    $cache[$type]= $obj;
    return $obj;
}

function getGeneralSpecialWarClass(?string $type){
    if($type === null || $type === ''){
        $type = GameConst::$defaultSpecialWar;
    }

    static $basePath = __NAMESPACE__.'\\ActionSpecialWar\\';
    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    $classPath = ($basePath.'che_'.$type);
    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}은 올바른 전투 특기가 아님");
}

function buildGeneralSpecialWarClass(?string $type):BaseSpecial{
    static $cache = [];
    if($type === null){
        $type = 'None';
    }
    if(key_exists($type, $cache)){
        return $cache[$type];
    }
    $class = getGeneralSpecialWarClass($type);

    $obj = new $class();
    $cache[$type]= $obj;
    return $obj;
}

function getGeneralCommandClass(?string $type){
    if($type === null || $type === ''){
        $type = '휴식';
    }

    static $basePath = __NAMESPACE__.'\\Command\\General\\';
    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}은 올바른 장수 커맨드가 아님");
}

function buildGeneralCommandClass(?string $type, General $generalObj, array $env, $arg = null):Command\GeneralCommand{
    $class = getGeneralCommandClass($type);
    return new $class($generalObj, $env, $arg);
}

function getNationCommandClass(?string $type){
    if($type === null || $type === ''){
        $type = '휴식';
    }

    static $basePath = __NAMESPACE__.'\\Command\\Nation\\';
    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}은 올바른 국가 커맨드가 아님");
}

function buildNationCommandClass(?string $type, General $generalObj, array $env, LastTurn $lastTurn, $arg = null):Command\NationCommand{
    $class = getNationCommandClass($type);
    return new $class($generalObj, $env, $lastTurn, $arg);
}

function getAPIExecutorClass($path){

    static $basePath = __NAMESPACE__.'\\API\\';
    if(is_string($path)){
    }
    else if(is_array($path)){
        $path = join('\\', $path);
    }
    else{
        throw new \InvalidArgumentException("{$path}는 올바른 API 지시자가 아님");
    }

    $classPath = ($basePath.$path);

    if(class_exists($classPath)){
        return $classPath;
    }
    throw new \InvalidArgumentException("{$path}는 올바른 API 경로가 아님");
}

function buildAPIExecutorClass(string $type, array $args):\sammo\BaseAPI{
    $class = getAPIExecutorClass($type);
    return new $class($args);
}

function getWarUnitTriggerClass(string $type){
    static $basePath = __NAMESPACE__.'\\WarUnitTrigger\\';
    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}은 WarUnitTrigger가 아님");
}

function buildWarUnitTriggerClass(?string $type, WarUnit $unit, ?array $args = null):BaseWarUnitTrigger{
    $classPath = getWarUnitTriggerClass($type);
    if(!$args){
        return new $classPath($unit);
    }

    $class = new \ReflectionClass($classPath);
    return $class->newInstanceArgs(array_merge([$unit], $args));
}

function getGeneralPoolClass(string $type){
    static $basePath = __NAMESPACE__.'\\GeneralPool\\';
    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}은 GeneralPool이 아님");
}

/**
 * @param \MeekroDB $db
 * @param int $owner
 * @param int $pickCnt
 * @param null|string $prefix
 * @return AbsGeneralPool[]
 */
function pickGeneralFromPool(\MeekroDB $db, int $owner, int $pickCnt, ?string $prefix=null):array{
    /** @var AbsGeneralPool */
    $class = getGeneralPoolClass(GameConst::$targetGeneralPool);
    return $class::pickGeneralFromPool($db, $owner, $pickCnt, $prefix);
}

function countPureGeneralFromRawList(?array $rawGeneralList=null):int{
    if(!$rawGeneralList){
        return 0;
    }

    $result = 0;
    foreach($rawGeneralList as $rawGeneral){
        if(!$rawGeneral['npc'] === 5){
            continue;
        }
        $result+=1;
    }
    return $result;
}

function getOfficerLevelText($officerLevel, $nlevel=8) {
    if($officerLevel >= 0 && $officerLevel <= 4) { $nlevel = 0; }
    $code = $nlevel * 100 + $officerLevel;
    return [
        812 =>     '군주',
        811 =>     '참모',
        810 =>  '제1장군',
        809 =>  '제1모사',
        808 =>  '제2장군',
        807 =>  '제2모사',
        806 =>  '제3장군',
        805 =>  '제3모사',

        712 =>     '황제',    612 =>       '왕',
        711 =>     '승상',    611 =>   '광록훈',
        710 => '표기장군',    610 =>   '좌장군',
        709 =>     '사공',    609 =>   '상서령',
        708 => '거기장군',    608 =>   '우장군',
        707 =>     '태위',    607 =>   '중서령',
        706 =>   '위장군',    606 =>   '전장군',
        705 =>     '사도',    605 =>   '비서령',

        512 =>       '공',    412 =>     '주목',
        511 => '광록대부',    411 =>   '태사령',
        510 => '안국장군',    410 => '아문장군',
        509 =>   '집금오',    409 =>     '낭중',
        508 => '파로장군',    408 =>     '호군',
        507 =>     '소부',    407 => '종사중랑',

        312 =>   '주자사',    212 =>     '군벌',
        311 =>     '주부',    211 =>     '참모',
        310 =>   '편장군',    210 =>   '비장군',
        309 => '간의대부',    209 =>   '부참모',

        112 =>     '영주',     12 =>     '두목',
        111 =>     '참모',     11 =>   '부두목',

          4 =>     '태수',
          3 =>     '군사',
          2 =>     '종사',
          1 =>     '일반',
          0 =>     '재야',
    ][$code]??'-';
}

function getCall($leadership, $strength, $intel) {
    if($leadership < 40){
        if($strength + $intel < 40){
            return '아둔';
        }
        if($intel >= GameConst::$chiefStatMin && $strength < $intel * 0.8){
            return '학자';
        }
        if($strength >= GameConst::$chiefStatMin && $intel < $strength * 0.8){
            return '장사';
        }
        return '명사';
    }

    $maxStat = max($leadership, $strength, $intel);
    $sum2Stat = min($leadership+$strength, $strength+$intel, $intel+$leadership);
    if($maxStat >= GameConst::$chiefStatMin + GameConst::$statGradeLevel && $sum2Stat >= $maxStat * 1.7){
        return '만능';
    }
    if($strength >= GameConst::$chiefStatMin - GameConst::$statGradeLevel && $intel < $strength * 0.8){
        return '용장';
    }
    if($intel >= GameConst::$chiefStatMin - GameConst::$statGradeLevel && $strength < $intel * 0.8){
        return '명장';
    }
    if($leadership >= GameConst::$chiefStatMin - GameConst::$statGradeLevel && $strength + $intel < $leadership){
        return '차장';
    }
    return '평범';
}

function getDed($dedication) {
    return getDedLevelText(getDedLevel($dedication));
}

function getDedLevelText(int $dedLevel):string{
    if($dedLevel === 0){
        return '무품관';
    }
    //{$maxDedLevel}품관 ~ 1품관
    $dedInvLevel = GameConst::$maxDedLevel - $dedLevel + 1;
    return "{$dedInvLevel}품관";
}


function getHonor($experience) {
    if($experience < 640 ) $honor = '전무';
    elseif($experience < 2560) $honor = '무명';
    elseif($experience < 5760) $honor = '신동';
    elseif($experience < 10240) $honor = '약간';
    elseif($experience < 16000) $honor = '평범';
    elseif($experience < 23040) $honor = '지역적';
    elseif($experience < 31360) $honor = '전국적';
    elseif($experience < 40960) $honor = '세계적';
    elseif($experience < 45000) $honor = '유명';
    elseif($experience < 51840) $honor = '명사';
    elseif($experience < 55000) $honor = '호걸';
    elseif($experience < 64000) $honor = '효웅';
    elseif($experience < 77440) $honor = '영웅';
    else $honor = '구세주';

    return $honor;
}

function getExpLevel($experience) {
    if($experience < 1000) {
        $level = intdiv($experience, 100);
    } else {
        $level = Util::toInt(sqrt($experience/10));
    }

    return $level;
}

function getDedLevel($dedication) {
    $level = Util::valueFit(
        ceil(sqrt($dedication) / 10),
        0,
        GameConst::$maxDedLevel
    );

    return $level;
}

function expStatus($exp) {
    return $exp / GameConst::$upgradeLimit * 100;
}

function getLevelPer($exp, $level) {
    if($exp < 100)      { $per = $exp; }
    elseif($exp < 1000) { $per = $exp - ($level)*100; }
    else                { $per = ($exp - 10*$level*$level) / (2*$level+1) * 10; }
    return $per;
}

function getBill(int $dedication) : int{
    return getBillByLevel(getDedLevel($dedication));
}

function getBillByLevel(int $dedLevel) : int{
    return ($dedLevel * 200 + 400);
}

function getCost(int $armtype) : int {
    return GameUnitConst::byID($armtype)->cost;
}

function getTechLevel($tech):int{
    return Util::valueFit(
        floor($tech / 1000),
        0,
        GameConst::$maxTechLevel
    );
}

function TechLimit($startYear, $year, $tech) : bool {

    $relYear = $year - $startYear;

    $relMaxTech = Util::valueFit(
        floor($relYear / 5) + 1,
        1,
        GameConst::$maxTechLevel
    );

    $techLevel = getTechLevel($tech);

    return $techLevel >= $relMaxTech;
}

function getTechAbil($tech) : int{
    return getTechLevel($tech) * 25;
}

function getTechCost($tech) : float{
    return 1 + getTechLevel($tech) * 0.15;
}

function getTechCall($tech) : string {
    $techLevel = getTechLevel($tech);
    return "{$techLevel}등급";
}

function getDexLevelList(): array{
    return [
        [0, 'navy', 'F-'],
        [350, 'navy', 'F'],
        [1375, 'navy', 'F+'],
        [3500, 'skyblue', 'E-'],
        [7125, 'skyblue', 'E'],
        [12650, 'skyblue', 'E+'],
        [20475, 'seagreen', 'D-'],
        [31000, 'seagreen', 'D'],
        [44625, 'seagreen', 'D+'],
        [61750, 'teal', 'C-'],
        [82775, 'teal', 'C'],
        [108100, 'teal', 'C+'],
        [138125, 'limegreen', 'B-'],
        [173250, 'limegreen', 'B'],
        [213875, 'limegreen', 'B+'],
        [260400, 'darkorange', 'A-'],
        [313225, 'darkorange', 'A'],
        [372750, 'darkorange', 'A+'],
        [439375, 'tomato', 'S-'],
        [513500, 'tomato', 'S'],
        [595525, 'tomato', 'S+'],
        [685850, 'darkviolet', 'Z-'],
        [784875, 'darkviolet', 'Z'],
        [893000, 'darkviolet', 'Z+'],
        [1010625, 'gold', 'EX-'],
        [1138150, 'gold', 'EX'],
        [1275975, 'white', 'EX+'],
    ];
}

function getDexCall(int $dex) : string {
    if($dex < 0){
        throw new \InvalidArgumentException();
    }

    $color = null;
    $name = null;
    foreach(getDexLevelList() as $dexLevel => [$dexKey, $nextColor, $nextName]){
        if($dex < $dexKey){
            break;
        }
        $color = $nextColor;
        $name = $nextName;
    }

    return "<font color='{$color}'>{$name}</font>";
}

function getDexLevel(int $dex) : int {
    if($dex < 0){
        throw new \InvalidArgumentException();
    }

    $retVal = 0;
    foreach(getDexLevelList() as $dexLevel => [$dexKey, $nextColor, $nextName]){
        if($dex < $dexKey){
            break;
        }
        $retVal = $dexLevel;
    }
    return $retVal;
}

function getDexLog($dex1, $dex2) {
    $ratio = (getDexLevel($dex1) - getDexLevel($dex2)) / 55 + 1;
    return $ratio;
}


function getItemName(?string $item) : ?string {
    if($item === null){
        return '-';
    }
    $itemClass = buildItemClass($item);
    return $itemClass->getName();
}

function isConsumable(?string $item) : bool{
    if($item === null){
        return false;
    }
    $itemClass = buildItemClass($item);
    return $itemClass->isConsumable();
}

function getNameColor(int $npcType):?string{
    if($npcType >= 2){
        return 'cyan';
    }
    if($npcType == 1){
        return 'skyblue';
    }
    return null;
}
function getColoredName(string $name, int $npcType):string{
    $color = getNameColor($npcType);
    if($color === null){
        return $name;
    }
    return "<span style='color:{$color}'>{$name}</span>";
}

function ConvertLog(?string $str, $type=1) : string {
    if(!$str){
        return '';
    }
    //TODO: 이 함수는 없애야 한다. CSS로 대신하자
    if($type > 0) {
        $str = str_replace("<1>", "<font size=1>", $str);
        $str = str_replace("<Y1>", "<font size=1 color=yellow>", $str);
        $str = str_replace("<R>", "<font color=red>", $str);
        $str = str_replace("<B>", "<font color=blue>", $str);
        $str = str_replace("<G>", "<font color=green>", $str);
        $str = str_replace("<M>", "<font color=magenta>", $str);
        $str = str_replace("<C>", "<font color=cyan>", $str);
        $str = str_replace("<L>", "<font color=limegreen>", $str);
        $str = str_replace("<S>", "<font color=skyblue>", $str);
        //$str = str_replace("<O>", "<font color=orange>", $str);
        //$str = str_replace("<D>", "<font color=darkorange>", $str);
        $str = str_replace("<O>", "<font color=orangered>", $str);
        $str = str_replace("<D>", "<font color=orangered>", $str);
        $str = str_replace("<Y>", "<font color=yellow>", $str);
        $str = str_replace("<W>", "<font color=white>", $str);
        $str = str_replace("</>", "</font>", $str);
    } else {
        $str = str_replace("<1>", "", $str);
        $str = str_replace("<Y1>", "", $str);
        $str = str_replace("<R>", "", $str);
        $str = str_replace("<B>", "", $str);
        $str = str_replace("<G>", "", $str);
        $str = str_replace("<M>", "", $str);
        $str = str_replace("<C>", "", $str);
        $str = str_replace("<L>", "", $str);
        $str = str_replace("<S>", "", $str);
        $str = str_replace("<O>", "", $str);
        $str = str_replace("<D>", "", $str);
        $str = str_replace("<Y>", "", $str);
        $str = str_replace("<W>", "", $str);
        $str = str_replace("</>", "", $str);
    }

    return $str;
}



function newColor($color) : string {
    switch($color) {
        case "":
        case "#330000":
        case "#FF0000":
        case "#800000":
        case "#A0522D":
        case "#FF6347":
        case "#808000":
        case "#008000":
        case "#2E8B57":
        case "#008080":
        case "#6495ED":
        case "#0000FF":
        case "#000080":
        case "#483D8B":
        case "#7B68EE":
        case "#800080":
        case "#A9A9A9":
        case "#000000":
            $color = "#FFFFFF"; break;
        default:
            $color = "#000000"; break;
    }
    return $color;
}

function backColor($color) : string {
    return newColor($color);
}


function getDomesticExpLevelBonus(int $expLevel):float{
    return 1 + $expLevel / 500;
}