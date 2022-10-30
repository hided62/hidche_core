<?php

namespace sammo\Command\General;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\GameUnitConst;
use \sammo\LastTurn;
use \sammo\Command;
use \sammo\Json;

use function \sammo\tryUniqueItemLottery;
use function \sammo\getAllNationStaticInfo;

use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\InheritanceKey;

use function sammo\buildNationTypeClass;
use function sammo\genGenericUniqueRNGFromGeneral;
use function sammo\refreshNationStaticInfo;
use function sammo\GetNationColors;


class che_무작위건국 extends Command\GeneralCommand
{
    static protected $actionName = '무작위 도시 건국';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        $nationName = $this->arg['nationName'] ?? null;
        $nationType = $this->arg['nationType'] ?? null;
        $colorType = $this->arg['colorType'] ?? null;

        if ($nationName === null || $nationType === null || $colorType === null) {
            return false;
        }

        if (!is_string($nationName) || !is_string($nationType) || !is_int($colorType)) {
            return false;
        }

        if (mb_strwidth($nationName) > 18 || $nationName == '') {
            return false;
        }

        if (!key_exists($colorType, GetNationColors())) {
            return false;
        }

        try {
            $nationTypeClass = buildNationTypeClass($nationType);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        $this->arg = [
            'nationName' => $nationName,
            'nationType' => $nationType,
            'colorType' => $colorType
        ];

        return true;
    }

    protected function init()
    {
        $env = $this->env;

        $this->setNation(['gennum', 'aux']);

        $relYear = $env['year'] - $env['startyear'];

        $this->minConditionConstraints = [
            ConstraintHelper::BeOpeningPart($relYear + 1),
            ConstraintHelper::ReqNationValue('level', '국가규모', '==', 0, '정식 국가가 아니어야합니다.')
        ];
    }

    protected function initWithArg()
    {
        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];

        $nationName = $this->arg['nationName'];
        $nationType = $this->arg['nationType'];
        $colorType = $this->arg['colorType'];

        $this->fullConditionConstraints = [
            ConstraintHelper::BeLord(),
            ConstraintHelper::WanderingNation(),
            ConstraintHelper::ReqNationValue('gennum', '수하 장수', '>=', 2),
            ConstraintHelper::BeOpeningPart($relYear + 1),
            ConstraintHelper::CheckNationNameDuplicate($nationName),
            ConstraintHelper::AllowJoinAction(),
        ];
    }

    public function getBrief(): string
    {
        $nationName = $this->arg['nationName'];
        $josaUl = JosaUtil::pick($nationName, '을');
        return "【{$nationName}】{$josaUl} 무작위 도시에 건국";
    }

    public function getCost(): array
    {
        return [0, 0];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);
        $generalName = $general->getName();
        $logger = $general->getLogger();

        $initYearMonth = Util::joinYearMonth($env['init_year'], $env['init_month']);
        $yearMonth = Util::joinYearMonth($env['year'], $env['month']);
        if($yearMonth <= $initYearMonth){
            $logger->pushGeneralActionLog("다음 턴부터 건국할 수 있습니다. <1>$date</>");
            $this->alternative = new che_인재탐색($general, $this->env, null);
            return false;
        }


        $cities = $db->queryFirstColumn('SELECT city FROM city where `level`>=5 and `level`<=6 and nation=0');
        if(!$cities){
            $logger->pushGeneralActionLog("건국할 수 있는 도시가 없습니다. <1>$date</>");
            $this->alternative = new che_해산($general, $this->env, null);
            return false;
        }
        $cityID = $rng->choice($cities);
        if($general->getCityID() == $cityID){
            $this->setCity();
        }
        else{
            $this->generalObj->setVar('city', $cityID);
            $this->setCity();
            $db->update('general', [
                'city' => $cityID
            ], 'nation = %i', $general->getNationID());
        }

        $josaYi = JosaUtil::pick($generalName, '이');

        $nationName = $this->arg['nationName'];
        $nationType = $this->arg['nationType'];
        $colorType = GetNationColors()[$this->arg['colorType']];

        $cityName = $this->city['name'];

        $josaUl = JosaUtil::pick($nationName, '을');

        $nationTypeClass = buildNationTypeClass($nationType);
        $nationTypeName = $nationTypeClass->getName();

        $logger->pushGeneralActionLog("<D><b>{$nationName}</b></>{$josaUl} 건국하였습니다. <1>$date</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$cityName}</b></>에 국가를 건설하였습니다.");

        $josaNationYi = JosaUtil::pick($nationName, '이');
        $logger->pushGlobalHistoryLog("<Y><b>【건국】</b></>{$nationTypeName} <D><b>{$nationName}</b></>{$josaNationYi} 새로이 등장하였습니다.");
        $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaUl} 건국");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>{$josaUl} 건국");

        $exp = 1000;
        $ded = 1000;

        $general->addExperience($exp);
        $general->addDedication($ded);

        $aux = Json::decode($this->nation['aux']) ?? [];
        $aux['can_국기변경'] = 1;
        $aux['can_무작위수도이전'] = 1;

        $db->update('city', [
            'nation' => $general->getNationID(),
            'conflict' => '{}'
        ], 'city=%i', $general->getCityID());

        $db->update('nation', [
            'name' => $nationName,
            'color' => $colorType,
            'level' => 1,
            'type' => $nationType,
            'capital' => $general->getCityID(),
            'aux' => Json::encode($aux)
        ], 'nation=%i', $general->getNationID());

        refreshNationStaticInfo();

        $general->increaseInheritancePoint(InheritanceKey::active_action, 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery(genGenericUniqueRNGFromGeneral($general), $general, '건국');
        $general->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        $nationTypes = [];
        foreach (GameConst::$availableNationType as $nationType) {
            $nationClass = buildNationTypeClass($nationType);
            $nationTypes[$nationType] = [
                'type' => $nationType,
                'name' => $nationClass->getName(),
                'pros' => $nationClass::$pros,
                'cons' => $nationClass::$cons
            ];
        }
        return [
            'procRes' => [
                'available건국' => count(getAllNationStaticInfo()) < $this->env['maxnation'],
                'nationTypes'   => $nationTypes,
                'colors'        => GetNationColors(),

            ]
        ];
    }
}
