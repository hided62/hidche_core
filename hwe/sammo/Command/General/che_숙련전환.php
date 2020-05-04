<?php

namespace sammo\Command\General;

use\sammo\{
    DB,
    Util,
    JosaUtil,
    Session,
    KVStorage,
    General,
    ActionLogger,
    GameConst,
    GameUnitConst,
    LastTurn,
    Command,
    ServConfig
};


use function\sammo\{
    getDexCall,
    getTechCall,
    tryUniqueItemLottery,
    getTechAbil
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;



class che_숙련전환 extends Command\GeneralCommand
{
    static protected $actionName = '숙련전환';
    static public $reqArg = true;

    /** @var int */
    protected $srcArmType;
    /** @var string */
    protected $srcArmTypeName;
    /** @var int */
    protected $destArmType;
    /** @var string */
    protected $destArmTypeName;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        if (!key_exists('srcArmType', $this->arg)) {
            return false;
        }
        if (!key_exists('destArmType', $this->arg)) {
            return false;
        }
        $srcArmType = $this->arg['srcArmType'];
        $destArmType = $this->arg['destArmType'];

        if (!is_int($srcArmType)) {
            return false;
        }
        if (!key_exists($srcArmType, GameUnitConst::allType())) {
            return false;
        }

        if (!is_int($destArmType)) {
            return false;
        }
        if (!key_exists($destArmType, GameUnitConst::allType())) {
            return false;
        }

        if ($srcArmType === $destArmType) {
            return false;
        }

        $this->arg = [
            'srcArmType' => $srcArmType,
            'destArmType' => $destArmType
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();


        [$reqGold, $reqRice] = $this->getCost();

        $this->minConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];
    }

    protected function initWithArg()
    {
        $this->srcArmType = $this->arg['srcArmType'];
        $this->srcArmTypeName = GameUnitConst::allType()[$this->srcArmType];
        $this->destArmType = $this->arg['destArmType'];
        $this->destArmTypeName = GameUnitConst::allType()[$this->destArmType];
        
        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];
    }

    public function getBrief(): string
    {
        return "【{$this->srcArmTypeName}】숙련을 【{$this->destArmTypeName}】숙련으로 전환";
    }

    public function getCommandDetailTitle(): string
    {
        $name = $this->getName();
        [$reqGold, $reqRice] = $this->getCost();

        $title = "{$name}(통솔경험";
        if ($reqGold > 0) {
            $title .= ", 자금{$reqGold}";
        }
        if ($reqRice > 0) {
            $title .= ", 군량{$reqRice}";
        }
        $title .= ')';
        return $title;
    }

    public function getCost(): array
    {
        $env = $this->env;
        return [$env['develcost'], $env['develcost']];
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

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $logger = $general->getLogger();

        $srcDex = $general->getVar('dex' . $this->srcArmType);
        $cutDex = Util::toInt($srcDex * 0.3);
        $cutDexText = number_format($cutDex);
        $addDex = Util::toInt($cutDex * 2 / 3);
        $addDexText = number_format($addDex);

        $general->increaseVar('dex' . $this->srcArmType, -$cutDex);
        $general->increaseVar('dex' . $this->destArmType, $addDex);

        $josaUl = JosaUtil::pick($cutDex, '을');
        $josaRo = JosaUtil::pick($addDex, '로');

        [$reqGold, $reqRice] = $this->getCost();

        $logger->pushGeneralActionLog("{$this->srcArmTypeName} 숙련 {$cutDexText}{$josaUl} {$this->destArmTypeName} 숙련 {$addDexText}{$josaRo} 전환했습니다. <1>$date</>");

        $general->addExperience(10);
        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVarWithLimit('rice', -$reqRice, 0);
        $general->increaseVar('leadership_exp', 2);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    public function getForm(): string
    {
        $db = DB::db();

        $general = $this->generalObj;

        $dexSrcTexts = [];
        $dexDestTexts = [];
        foreach (GameUnitConst::allType() as $armType => $armName) {
            $dexVal = $general->getVar('dex' . $armType);
            $dexValText = number_format($dexVal);
            $cutDex = Util::toInt($dexVal * 0.3);
            $addDex = Util::toInt($cutDex * 2 / 3);
            $addDexText = number_format($addDex);
            $newDex = $dexVal - $cutDex;
            $beforeDexLevel = getDexCall($dexVal);
            $afterDexLevel = getDexCall($newDex);

            $dexSrcTexts[$armType] = "{$armName} ({$beforeDexLevel} ⇒ {$afterDexLevel}, {$addDexText} 전환)";
            $dexDestTexts[$armType] = "{$armName} ({$dexValText})";
        }

        ob_start();
?>
        본인의 특정 병종 숙련을 30% 줄이고, 줄어든 숙련 중 2/3(20%p)를 다른 병종 숙련으로 전환합니다.<br>

        <select class='formInput' name="srcArmType" id="srcArmType" size='1' style='color:white;background-color:black;'>
            <?php foreach ($dexSrcTexts as $armType => $infoText) : ?>
                <option value="<?= $armType ?>"><?= $infoText ?></option>
            <?php endforeach; ?>
        </select>
        숙련을

        <select class='formInput' name="destArmType" id="destArmType" size='1' style='color:white;background-color:black;'>
            <?php foreach ($dexDestTexts as $armType => $infoText) : ?>
                <option value="<?= $armType ?>"><?= $infoText ?></option>
            <?php endforeach; ?>
        </select>
        숙련으로 <input type=button id="commonSubmit" value="<?= $this->getName() ?>"><br>

<?php
        return ob_get_clean();
    }
}
