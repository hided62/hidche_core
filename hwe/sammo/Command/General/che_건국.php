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

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;
use function sammo\buildNationTypeClass;
use function sammo\refreshNationStaticInfo;
use function sammo\GetNationColors;
use function sammo\newColor;


class che_건국 extends Command\GeneralCommand
{
    static protected $actionName = '건국';
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

        $this->setCity();
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
            ConstraintHelper::ConstructableCity(),
        ];
    }

    public function getBrief(): string
    {
        $nationName = $this->arg['nationName'];
        $josaUl = JosaUtil::pick($nationName, '을');
        return "【{$nationName}】{$josaUl} 건국";
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

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $nationName = $this->arg['nationName'];
        $nationType = $this->arg['nationType'];
        $colorType = GetNationColors()[$this->arg['colorType']];

        $cityName = $this->city['name'];

        $josaUl = JosaUtil::pick($nationName, '을');

        $logger = $general->getLogger();

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

        $general->increaseInheritancePoint('active_action', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general, '건국');
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
