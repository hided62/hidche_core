<?php

namespace sammo\Command\Nation;

use\sammo\{
    DB,
    Util,
    JosaUtil,
    General,
    DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    MessageTarget,
    Message,
    CityConst,
    Json,
};

use function\sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic,
    CriticalScoreEx,
    GetImageURL,
    getNationStaticInfo,
    GetNationColors,
    newColor,
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Event\Action;

class che_국기변경 extends Command\NationCommand
{
    static protected $actionName = '국기변경';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }

        if (!key_exists('colorType', $this->arg)) {
            return false;
        }
        $colorType = $this->arg['colorType'];
        if (!key_exists($colorType, GetNationColors())) {
            return false;
        }

        $this->arg = [
            'colorType' => $colorType,
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation(['aux']);

        $actionName = $this->getName();

        $this->minConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqNationAuxValue("can_{$actionName}", 0, '>', 0, '더이상 변경이 불가능합니다.')
        ];
    }

    protected function initWithArg()
    {
        $actionName = $this->getName();

        $this->fullConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqNationAuxValue("can_{$actionName}", 0, '>', 0, '더이상 변경이 불가능합니다.')
        ];
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

    public function getBrief(): string
    {
        $color = GetNationColors()[$this->arg['colorType']];
        return "【<span style='color:{$color};'>국기</span>】를 변경";
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $actionName = $this->getName();

        $general = $this->generalObj;
        $generalID = $general->getID();
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $colorType = $this->arg['colorType'];
        $color = GetNationColors()[$colorType];


        $nationID = $general->getNationID();
        $nationName = $this->nation['name'];

        $logger = $general->getLogger();


        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $aux = Json::decode($this->nation['aux']);
        $aux["can_{$actionName}"] = 0;

        $db->update('nation', [
            'color'=>$color,
            'aux'=>Json::encode($aux)
        ], 'nation=%i', $nationID);

        $logger->pushGeneralActionLog("<span style='color:{$color};'><b>국기</b></span>를 변경하였습니다 <1>$date</>");
        $logger->pushGeneralHistoryLog("<span style='color:{$color};'><b>국기</b></span>를 변경");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <span style='color:{$color};'><b>국기</b></span>를 변경하였습니다");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <span style='color:{$color};'><b>국기</b></span>를 변경하였습니다");
        $logger->pushGlobalHistoryLog("<S><b>【국기변경】</b></><D><b>{$nationName}</b></>{$josaYiNation} <span style='color:{$color};'><b>국기</b></span>를 변경하였습니다.");

        $this->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);
        return true;
    }

    public function getJSFiles(): array
    {
        return [
            'js/colorSelect.js'
        ];
    }


    public function getForm(): string
    {
        ob_start();
?>
        국기를 변경합니다. 단 1회 가능합니다.<br>
        색상 : <select class='formInput' name='colorType' id='colorType' size='1'>

            <?php foreach (GetNationColors() as $idx => $color) :
                /*
            if($colorUsed[$color] > 0){
                continue;
            }
            */
            ?>
                <option value="<?= $idx ?>" data-color=<?=$color?> data-font-color="<?=newColor($color)?>" style='background-color:<?= $color ?>;color:<?= newColor($color) ?>;'>국가명(<?=$color?>)</option>
            <?php endforeach; ?> <input type=button id="commonSubmit" value="<?= $this->getName() ?>"><br>
        <br>
<?php
        return ob_get_clean();
    }
}
