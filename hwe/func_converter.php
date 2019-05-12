<?php 

namespace sammo;

/**
 * Value Converter
 * 
 * Side effect 없이 값의 변환만을 수행하는 함수들의 모음.
 * (단, autoload, 정적 변수 초기화는 허용)
 */

function getCharacterList(){
    $infoText = [];
    foreach(GameConst::$allPersonality as $personalityID){
        $class = getPersonalityClass($personalityID);
        $infoText[$personalityID] = [$class::$name, $class::$info];
    }
    return $infoText;
}

function CharCall($call) {
    static $invTable = [];
    if(\key_exists($call, $invTable)){
        return $invTable[$call];
    }

    foreach(getCharacterList() as $id => [$name, $info]){
        $invTable[$name] = $id;
    }
    if(!key_exists($call, $invTable)){
        throw new \InvalidArgumentException("{$call}은 올바른 성격이 아님");
    }
    return $invTable[$call];
}

function SpecCall($call) {
    switch($call) {
        case '-':       $type =  0; break;
        case '경작':    $type =  1; break;
        case '상재':    $type =  2; break;
        case '발명':    $type =  3; break;

        case '축성':    $type = 10; break;
        case '수비':    $type = 11; break;
        case '통찰':    $type = 12; break;

        case '인덕':    $type = 20; break;

        case '거상':    $type = 30; break;
        case '귀모':    $type = 31; break;

        case '귀병':    $type = 40; break;
        case '신산':    $type = 41; break;
        case '환술':    $type = 42; break;
        case '집중':    $type = 43; break;
        case '신중':    $type = 44; break;
        case '반계':    $type = 45; break;

        case '보병':    $type = 50; break;
        case '궁병':    $type = 51; break;
        case '기병':    $type = 52; break;
        case '공성':    $type = 53; break;

        case '돌격':    $type = 60; break;
        case '무쌍':    $type = 61; break;
        case '견고':    $type = 62; break;
        case '위압':    $type = 63; break;

        case '저격':    $type = 70; break;
        case '필살':    $type = 71; break;
        case '징병':    $type = 72; break;
        case '의술':    $type = 73; break;
        case '격노':    $type = 74; break;
        case '척사':    $type = 75; break;
        default: $type = 0; break;
    }
    return $type;
}

function getNationChiefLevel($level) {
    switch($level) {
        case 7: $lv = 5; break;
        case 6: $lv = 5; break;
        case 5: $lv = 7; break;
        case 4: $lv = 7; break;
        case 3: $lv = 9; break;
        case 2: $lv = 9; break;
        case 1: $lv = 11; break;
        case 0: $lv = 11; break;
    }
    return $lv;
}

function getNationLevel($level) {
    switch($level) {
        case 7: $call = '황제'; break;
        case 6: $call = '왕'; break;
        case 5: $call = '공'; break;
        case 4: $call = '주목'; break;
        case 3: $call = '주자사'; break;
        case 2: $call = '군벌'; break;
        case 1: $call = '호족'; break;
        case 0: $call = '방랑군'; break;
    }
    return $call;
}

function getGenChar($type) {
    return getCharacterList()[$type][0];
}

function getCharInfo(?int $type):?string {
    if($type === null){
        return null;
    }
    return getCharacterList()[$type][1]??null;
}

function getGenSpecial($type) {
    switch($type) {
        case 40: $call = '귀병'; break;
        case 41: $call = '신산'; break;
        case 42: $call = '환술'; break;
        case 43: $call = '집중'; break;
        case 44: $call = '신중'; break;
        case 45: $call = '반계'; break;

        case 50: $call = '보병'; break;
        case 51: $call = '궁병'; break;
        case 52: $call = '기병'; break;
        case 53: $call = '공성'; break;

        case 60: $call = '돌격'; break;
        case 61: $call = '무쌍'; break;
        case 62: $call = '견고'; break;
        case 63: $call = '위압'; break;

        case 70: $call = '저격'; break;
        case 71: $call = '필살'; break;
        case 72: $call = '징병'; break;
        case 73: $call = '의술'; break;
        case 74: $call = '격노'; break;
        case 75: $call = '척사'; break;
        default: $call = null;
    }
    return $call;
}

function getSpecialTextList():array{
    
    //앞칸은 '설명을 위해' '그냥' 적어둠
    return [
        0 => ['-', null],
        1 => ['경작', '[내정] 농지 개간 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%'],
        2 => ['상재', '[내정] 상업 투자 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%'],
        3 => ['발명', '[내정] 기술 연구 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%'],

        10 => ['축성', '[내정] 성벽 보수 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%'],
        11 => ['수비', '[내정] 수비 강화 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%'],
        12 => ['통찰', '[내정] 치안 강화 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%'],

        20 => ['인덕', '[내정] 주민 선정·정착 장려 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%'],

        30 => ['거상', '이것 저것'],
        31 => ['귀모', '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p'],

        40 => ['귀병', '[군사] 귀병 계통 징·모병비 -10%<br>[전투] 계략 성공 확률 +20%p'],
        41 => ['신산', '[계략] 화계·탈취·파괴·선동 : 성공률 +10%p<br>[전투] 계략 시도 확률 +20%p, 계략 성공 확률 +20%p '],
        42 => ['환술', '[전투] 계략 성공 확률 +10%p, 계략 성공 시 대미지 +30%'],
        43 => ['집중', '[전투] 계략 성공 시 대미지 +50%'],
        44 => ['신중', '[전투] 계략 성공 확률 100%'],
        45 => ['반계', '[전투] 상대의 계략 성공 확률 -10%p, 상대의 계략을 40% 확률로 되돌림, 반목 성공시 대미지 추가(+60% → +100%)'],

        50 => ['보병', '[군사] 보병 계통 징·모병비 -10%<br>[전투] 공격 시 아군 피해 -10%, 수비 시 아군 피해 -20%'],
        51 => ['궁병', '[군사] 궁병 계통 징·모병비 -10%<br>[전투] 회피 확률 +20%p'],
        52 => ['기병', '[군사] 기병 계통 징·모병비 -10%<br>[전투] 수비 시 대미지 +10%, 공격 시 대미지 +20%'],
        53 => ['공성', '[군사] 차병 계통 징·모병비 -10%<br>[전투] 성벽 공격 시 대미지 +100%'],

        60 => ['돌격', '[전투] 상대 회피 불가, 공격 시 전투 페이즈 +1, 공격 시 대미지 +10%'],
        61 => ['무쌍', '[전투] 대미지 +10%, 공격 시 필살 확률 +10%p'],
        62 => ['견고', '[전투] 상대 필살, 격노, 위압, 저격 불가, 상대 계략 시도시 성공 확률 -10%p, 부상 없음, 아군 피해 -5%'],
        63 => ['위압', '[전투] 훈련/사기≥90, 병력≥1,000 일 때 첫 페이즈 위압 발동(적 공격 불가)'],

        70 => ['저격', '[전투] 전투 개시 시 1/3 확률로 저격 발동'],
        71 => ['필살', '[전투] 필살 확률 +20%p'],
        72 => ['징병', '[군사] 징·모병비 -50%, 통솔 순수 능력치 보정 +15%'],
        73 => ['의술', '[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>[전투] 페이즈마다 20% 확률로 치료 발동(아군 피해 1/3 감소)'],
        74 => ['격노', '[전투] 상대방 필살 및 회피 시도시 일정 확률로 격노(필살) 발동, 공격 시 일정 확률로 진노(1페이즈 추가)'],
        75 => ['척사', '[전투] 지역·도시 병종 상대로 대미지 +10%, 아군 피해 -10%']
    ];
}

function getSpecialInfo(?int $type):?string{
    if($type === null){
        return null;
    }

    $infoText = getSpecialTextList();

    return $infoText[$type][1]??null;
}

function getNationType(?string $type) {
    $nationClass = getNationTypeClass($type);
    $text = $nationClass::$name;
    $text = join(' ', StringUtil::splitString($text));
    $cache[$type] = $text;

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

    [$name, $pros, $cons] = [$nationClass::$name, $nationClass::$pros, $nationClass::$cons];
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

function getItemClass(int $itemCode){
    //XXX: 임시 구현임

    //iAction이 필요한 것만 반환

    static $basePath = __NAMESPACE__.'\\ActionItem\\';

    $itemPath = [
        1=>'che_환약_치료',
        5=>'che_이추_계략',
        6=>'che_향낭_계략',

        7=>'che_오석산_치료',
        8=>'che_무후행군_치료',
        9=>'che_도소연명_치료',
        10=>'che_칠엽청점_치료',
        11=>'che_정력견혈_치료',
        21=>'che_육도_계략',
        22=>'che_삼략_계략',
        23=>'che_청낭서_의술',
        24=>'che_태평청령_의술',
    ];

    $itemClass = $itemPath[$itemCode]??null;
    if($itemClass === null){
        return null;
    }

    $classPath = ($basePath.$itemClass);

    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$itemCode}, {$itemClass}는 올바른 성격 클래스가 아님");
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

function getWarUnitTriggerClass(string $type){
    static $basePath = __NAMESPACE__.'\\WarUnitTrigger\\';
    $classPath = ($basePath.$type);

    if(class_exists($classPath)){
        return $classPath;
    }

    throw new \InvalidArgumentException("{$type}은 WarUnitTrigger가 아님");
}

function buildWarUnitTriggerClass(?string $type, WarUnit $unit, ?array $args = null):BaseWarUnitTrigger{
    $classPath = getNationCommandClass($type);
    if(!$args){
        return new $classPath($unit);
    }

    $class = new \ReflectionClass($classPath);
    return $class->newInstanceArgs(array_merge([$unit], $args));
}

function getLevel($level, $nlevel=8) {
    if($level >= 0 && $level <= 4) { $nlevel = 0; }
    $code = $nlevel * 100 + $level;
    switch($code) {
        case 812: $call =     '군주'; break;
        case 811: $call =     '참모'; break;
        case 810: $call =  '제1장군'; break;
        case 809: $call =  '제1모사'; break;
        case 808: $call =  '제2장군'; break;
        case 807: $call =  '제2모사'; break;
        case 806: $call =  '제3장군'; break;
        case 805: $call =  '제3모사'; break;

        case 712: $call =     '황제'; break;    case 612: $call =       '왕'; break;
        case 711: $call =     '승상'; break;    case 611: $call =   '광록훈'; break;
        case 710: $call = '표기장군'; break;    case 610: $call =   '좌장군'; break;
        case 709: $call =     '사공'; break;    case 609: $call =   '상서령'; break;
        case 708: $call = '거기장군'; break;    case 608: $call =   '우장군'; break;
        case 707: $call =     '태위'; break;    case 607: $call =   '중서령'; break;
        case 706: $call =   '위장군'; break;    case 606: $call =   '전장군'; break;
        case 705: $call =     '사도'; break;    case 605: $call =   '비서령'; break;

        case 512: $call =       '공'; break;    case 412: $call =     '주목'; break;
        case 511: $call = '광록대부'; break;    case 411: $call =   '태사령'; break;
        case 510: $call = '안국장군'; break;    case 410: $call = '아문장군'; break;
        case 509: $call =   '집금오'; break;    case 409: $call =     '낭중'; break;
        case 508: $call = '파로장군'; break;    case 408: $call =     '호군'; break;
        case 507: $call =     '소부'; break;    case 407: $call = '종사중랑'; break;

        case 312: $call =   '주자사'; break;    case 212: $call =     '군벌'; break;
        case 311: $call =     '주부'; break;    case 211: $call =     '참모'; break;
        case 310: $call =   '편장군'; break;    case 210: $call =   '비장군'; break;
        case 309: $call = '간의대부'; break;    case 209: $call =   '부참모'; break;

        case 112: $call =     '영주'; break;    case  12: $call =     '두목'; break;
        case 111: $call =     '참모'; break;    case  11: $call =   '부두목'; break;

        case   4: $call =     '태수'; break;
        case   3: $call =     '군사'; break;
        case   2: $call =     '종사'; break;
        case   1: $call =     '일반'; break;
        case   0: $call =     '재야'; break;
        default:  $call =        '-'; break;
    }
    return $call;
}

function getCall($leader, $power, $intel) {
    $call = '평범';

    if($leader < 40){
        if($power + $intel < 40){
            return '아둔';
        }
        if($intel >= GameConst::$chiefStatMin && $power < $intel * 0.8){
            return '학자';
        }
        if($power >= GameConst::$chiefStatMin && $intel < $power * 0.8){
            return '장사';
        }
        return '명사';
    }

    $maxStat = max($leader, $power, $intel);
    $sum2Stat = min($leader+$power, $power+$intel, $intel+$leader);
    if($maxStat >= GameConst::$chiefStatMin + GameConst::$statGradeLevel && $sum2Stat >= $maxStat * 1.7){
        return '만능';
    }
    if($power >= GameConst::$chiefStatMin - GameConst::$statGradeLevel && $intel < $power * 0.8){
        return '용장';
    }
    if($intel >= GameConst::$chiefStatMin - GameConst::$statGradeLevel && $power < $intel * 0.8){
        return '명장';
    }
    if($leader >= GameConst::$chiefStatMin - GameConst::$statGradeLevel && $power + $intel < $leader){
        return '차장';
    }
    return '평범';
}

function getDed($dedication) {
    $dedLevel = getDedLevel($dedication);
    if($dedLevel == 0){
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
    $level = getDedLevel($dedication);
    return ($level * 200 + 400);
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

    $retVal = null;
    foreach(getDexLevelList() as $dexLevel => [$dexKey, $nextColor, $nextName]){
        if($dex < $dexKey){
            break;
        }
        $retVal = $dexLevel;
    }
    return $dexLevel;
}

function getDexLog($dex1, $dex2) {
    $ratio = (getDexLevel($dex1) - getDexLevel($dex2)) / 55 + 1;
    return $ratio;
}


function getWeapName($weap) : ?string {
    switch($weap) {
        case  0: $weapname = '-'; break;
        case  1: $weapname = '단도(+1)'; break;
        case  2: $weapname = '단궁(+2)'; break;
        case  3: $weapname = '단극(+3)'; break;
        case  4: $weapname = '목검(+4)'; break;
        case  5: $weapname = '죽창(+5)'; break;
        case  6: $weapname = '소부(+6)'; break;

        case  7: $weapname = '동추(+7)'; break;
        case  8: $weapname = '철편(+7)'; break;
        case  9: $weapname = '철쇄(+7)'; break;
        case 10: $weapname = '맥궁(+7)'; break;
        case 11: $weapname = '유성추(+8)'; break;
        case 12: $weapname = '철질여골(+8)'; break;
        case 13: $weapname = '쌍철극(+9)'; break;
        case 14: $weapname = '동호비궁(+9)'; break;
        case 15: $weapname = '삼첨도(+10)'; break;
        case 16: $weapname = '대부(+10)'; break;
        case 17: $weapname = '고정도(+11)'; break;
        case 18: $weapname = '이광궁(+11)'; break;
        case 19: $weapname = '철척사모(+12)'; break;
        case 20: $weapname = '칠성검(+12)'; break;
        case 21: $weapname = '사모(+13)'; break;
        case 22: $weapname = '양유기궁(+13)'; break;
        case 23: $weapname = '언월도(+14)'; break;
        case 24: $weapname = '방천화극(+14)'; break;
        case 25: $weapname = '청홍검(+15)'; break;
        case 26: $weapname = '의천검(+15)'; break;
    }
    return $weapname;
}

function getWeapEff($weap) : ?int{
    switch($weap) {
        case  7: $weap =  7; break;
        case  8: $weap =  7; break;
        case  9: $weap =  7; break;
        case 10: $weap =  7; break;
        case 11: $weap =  8; break;
        case 12: $weap =  8; break;
        case 13: $weap =  9; break;
        case 14: $weap =  9; break;
        case 15: $weap = 10; break;
        case 16: $weap = 10; break;
        case 17: $weap = 11; break;
        case 18: $weap = 11; break;
        case 19: $weap = 12; break;
        case 20: $weap = 12; break;
        case 21: $weap = 13; break;
        case 22: $weap = 13; break;
        case 23: $weap = 14; break;
        case 24: $weap = 14; break;
        case 25: $weap = 15; break;
        case 26: $weap = 15; break;
        default: break;
    }
    return $weap;
}

function getBookName($book) : ?string {
    switch($book) {
        case  0: $bookname = '-'; break;
        case  1: $bookname = '효경전(+1)'; break;
        case  2: $bookname = '회남자(+2)'; break;
        case  3: $bookname = '변도론(+3)'; break;
        case  4: $bookname = '건상역주(+4)'; break;
        case  5: $bookname = '여씨춘추(+5)'; break;
        case  6: $bookname = '사민월령(+6)'; break;

        case  7: $bookname = '위료자(+7)'; break;
        case  8: $bookname = '사마법(+7)'; break;
        case  9: $bookname = '한서(+7)'; break;
        case 10: $bookname = '논어(+7)'; break;
        case 11: $bookname = '전론(+8)'; break;
        case 12: $bookname = '사기(+8)'; break;
        case 13: $bookname = '장자(+9)'; break;
        case 14: $bookname = '역경(+9)'; break;
        case 15: $bookname = '시경(+10)'; break;
        case 16: $bookname = '구국론(+10)'; break;
        case 17: $bookname = '상군서(+11)'; break;
        case 18: $bookname = '춘추전(+11)'; break;
        case 19: $bookname = '산해경(+12)'; break;
        case 20: $bookname = '맹덕신서(+12)'; break;
        case 21: $bookname = '관자(+13)'; break;
        case 22: $bookname = '병법24편(+13)'; break;
        case 23: $bookname = '한비자(+14)'; break;
        case 24: $bookname = '오자병법(+14)'; break;
        case 25: $bookname = '노자(+15)'; break;
        case 26: $bookname = '손자병법(+15)'; break;
    }
    return $bookname;
}

function getBookEff($book) : ?int {
    switch($book) {
        case  7: $book =  7; break;
        case  8: $book =  7; break;
        case  9: $book =  7; break;
        case 10: $book =  7; break;
        case 11: $book =  8; break;
        case 12: $book =  8; break;
        case 13: $book =  9; break;
        case 14: $book =  9; break;
        case 15: $book = 10; break;
        case 16: $book = 10; break;
        case 17: $book = 11; break;
        case 18: $book = 11; break;
        case 19: $book = 12; break;
        case 20: $book = 12; break;
        case 21: $book = 13; break;
        case 22: $book = 13; break;
        case 23: $book = 14; break;
        case 24: $book = 14; break;
        case 25: $book = 15; break;
        case 26: $book = 15; break;
        default: break;
    }
    return $book;
}

function getHorseName($horse) : ?string {
    switch($horse) {
        case  0: $horsename = '-'; break;
        case  1: $horsename = '노기(+1)'; break;
        case  2: $horsename = '조랑(+2)'; break;
        case  3: $horsename = '노새(+3)'; break;
        case  4: $horsename = '나귀(+4)'; break;
        case  5: $horsename = '갈색마(+5)'; break;
        case  6: $horsename = '흑색마(+6)'; break;

        case  7: $horsename = '백마(+7)'; break;
        case  8: $horsename = '백마(+7)'; break;
        case  9: $horsename = '기주마(+7)'; break;
        case 10: $horsename = '기주마(+7)'; break;
        case 11: $horsename = '양주마(+8)'; break;
        case 12: $horsename = '양주마(+8)'; break;
        case 13: $horsename = '과하마(+9)'; break;
        case 14: $horsename = '과하마(+9)'; break;
        case 15: $horsename = '대완마(+10)'; break;
        case 16: $horsename = '대완마(+10)'; break;
        case 17: $horsename = '서량마(+11)'; break;
        case 18: $horsename = '서량마(+11)'; break;
        case 19: $horsename = '사륜거(+12)'; break;
        case 20: $horsename = '사륜거(+12)'; break;
        case 21: $horsename = '절영(+13)'; break;
        case 22: $horsename = '적로(+13)'; break;
        case 23: $horsename = '적란마(+14)'; break;
        case 24: $horsename = '조황비전(+14)'; break;
        case 25: $horsename = '한혈마(+15)'; break;
        case 26: $horsename = '적토마(+15)'; break;
    }
    return $horsename;
}

function getHorseEff($horse) : ?int {
    switch($horse) {
        case  7: $horse =  7; break;
        case  8: $horse =  7; break;
        case  9: $horse =  7; break;
        case 10: $horse =  7; break;
        case 11: $horse =  8; break;
        case 12: $horse =  8; break;
        case 13: $horse =  9; break;
        case 14: $horse =  9; break;
        case 15: $horse = 10; break;
        case 16: $horse = 10; break;
        case 17: $horse = 11; break;
        case 18: $horse = 11; break;
        case 19: $horse = 12; break;
        case 20: $horse = 12; break;
        case 21: $horse = 13; break;
        case 22: $horse = 13; break;
        case 23: $horse = 14; break;
        case 24: $horse = 14; break;
        case 25: $horse = 15; break;
        case 26: $horse = 15; break;
        default: break;
    }
    return $horse;
}

function isConsumable($item) : bool{
    //XXX: 제거할 것. 정식 아이템 구현으로 이동
    if(1 <= $item && $item <= 6){
        return true;
    }
    return false;
}

function getItemName($item) : ?string {
    switch($item) {
        case  0: $itemname = '-'; break;
        case  1: $itemname = '환약(치료)'; break;
        case  2: $itemname = '수극(저격)'; break;
        case  3: $itemname = '탁주(사기)'; break;
        case  4: $itemname = '청주(훈련)'; break;
        case  5: $itemname = '이추(계략)'; break;
        case  6: $itemname = '향낭(계략)'; break;

        case  7: $itemname = '오석산(치료)'; break;
        case  8: $itemname = '무후행군(치료)'; break;
        case  9: $itemname = '도소연명(치료)'; break;
        case 10: $itemname = '칠엽청점(치료)'; break;
        case 11: $itemname = '정력견혈(치료)'; break;
        case 12: $itemname = '과실주(훈련)'; break;
        case 13: $itemname = '이강주(훈련)'; break;
        case 14: $itemname = '의적주(사기)'; break;
        case 15: $itemname = '두강주(사기)'; break;
        case 16: $itemname = '보령압주(사기)'; break;
        case 17: $itemname = '철벽서(훈련)'; break;
        case 18: $itemname = '단결도(훈련)'; break;
        case 19: $itemname = '춘화첩(사기)'; break;
        case 20: $itemname = '초선화(사기)'; break;
        case 21: $itemname = '육도(계략)'; break;
        case 22: $itemname = '삼략(계략)'; break;
        case 23: $itemname = '청낭서(의술)'; break;
        case 24: $itemname = '태평청령(의술)'; break;
        case 25: $itemname = '태평요술(회피)'; break;
        case 26: $itemname = '둔갑천서(회피)'; break;
        default: $itemname = null;
    }
    return $itemname;
}

function getItemInfo(?int $item):?string{
    $itemInfo = [
        1=>['환약(치료)', '[군사] 턴 실행 전 부상 회복. 1회용'],
        2=>['수극(저격)', '[전투] 전투 개시 전 20% 확률로 저격 시도. 1회용'],
        3=>['탁주(사기)', '[전투] 사기 보정 +3. 1회용'],
        4=>['청주(훈련)', '[전투] 훈련 보정 +3. 1회용'],
        5=>['이추(계략)', '[계략] 화계·탈취·파괴·선동 : 성공률 +10%p. 1회용'],
        6=>['향낭(계략)', '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p. 1회용'],
        
        7=>['오석산(치료)', '[군사] 턴 실행 전 부상 회복.'],
        8=>['무후행군(치료)', '[군사] 턴 실행 전 부상 회복.'],
        9=>['도소연명(치료)', '[군사] 턴 실행 전 부상 회복.'],
        10=>['칠엽청점(치료)', '[군사] 턴 실행 전 부상 회복.'],
        11=>['정력견혈(치료)', '[군사] 턴 실행 전 부상 회복.'],
        12=>['과실주(훈련)', '[전투] 훈련 보정 +5'],
        13=>['이강주(훈련)', '[전투] 훈련 보정 +5'],
        14=>['의적주(사기)', '[전투] 사기 보정 +5'],
        15=>['두강주(사기)', '[전투] 사기 보정 +5'],
        16=>['보령압주(사기)', '[전투] 사기 보정 +5'],
        17=>['철벽서(훈련)', '[전투] 훈련 보정 +7'],
        18=>['단결도(훈련)', '[전투] 훈련 보정 +7'],
        19=>['춘화첩(사기)', '[전투] 사기 보정 +7'],
        20=>['초선화(사기)', '[전투] 사기 보정 +7'],
        21=>['육도(계략)', '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p'],
        22=>['삼략(계략)', '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p'],
        23=>['청낭서(의술)', '[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>[전투] 페이즈마다 20% 확률로 치료 발동(아군 피해 1/3 감소)'],
        24=>['태평청령(의술)', '[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>[전투] 페이즈마다 20% 확률로 치료 발동(아군 피해 1/3 감소)'],
        25=>['태평요술(회피)', '[전투] 회피 확률 +20%p'],
        26=>['둔갑천서(회피)', '[전투] 회피 확률 +20%p'],
    ];

    return $itemInfo[$item][1]??null;
}

function getItemCost2($weap) : int {
    switch($weap) {
        case  0: $weapcost = 0; break;
        case  1: $weapcost = 100; break;
        case  2: $weapcost = 1000; break;
        case  3: $weapcost = 1000; break;
        case  4: $weapcost = 1000; break;
        case  5: $weapcost = 1000; break;
        case  6: $weapcost = 3000; break;
        default: $weapcost = 200; break;
    }
    return $weapcost;
}

function getItemCost($weap) : int {
    switch($weap) {
        case  0: $weapcost = 0; break;
        case  1: $weapcost = 1000; break;
        case  2: $weapcost = 3000; break;
        case  3: $weapcost = 6000; break;
        case  4: $weapcost = 10000; break;
        case  5: $weapcost = 15000; break;
        case  6: $weapcost = 21000; break;
        default: $weapcost = 200; break;
    }
    return $weapcost;
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
    $color = nameColor($npcType);
    if($color === null){
        return $name;
    }
    //TODO: font 폐기.
    return "<font color='{$color}'>{$name}</font>";
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