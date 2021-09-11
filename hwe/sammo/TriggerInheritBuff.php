<?php

namespace sammo;

class TriggerInheritBuff implements iAction
{
    use DefaultAction;

    //TODO Ratio와 Prob 혼용 중지
    const WAR_AVOID_RATIO = 'warAvoidRatio';
    const WAR_CRITICAL_RATIO = 'warCriticalRatio';
    const WAR_MAGIC_TRIAL_PROB = 'warMagicTrialProb';
    const DOMESTIC_SUCCESS_PROB = 'domesticSuccessProb';
    const DOMESTIC_FAIL_PROB = 'domesticFailProb';
    const OPPOSE_WAR_AVOID_RATIO = 'warAvoidRatioOppose';
    const OPPOSE_WAR_CRITICAL_RATIO = 'warCriticalRatioOppose';
    const OPPOSE_WAR_MAGIC_TRIAL_PROB = 'warMagicTrialProbOppose';

    const MAX_STEP = 5;

    const BUFF_KEY_MAP = [
        self::WAR_AVOID_RATIO => 'warAvoidRatio',
        self::WAR_CRITICAL_RATIO => 'warCriticalRatio',
        self::WAR_MAGIC_TRIAL_PROB => 'warMagicTrialProb',

        self::DOMESTIC_SUCCESS_PROB => 'success',
        self::DOMESTIC_FAIL_PROB => 'fail',

        self::OPPOSE_WAR_AVOID_RATIO => 'warAvoidRatio',
        self::OPPOSE_WAR_CRITICAL_RATIO => 'warCriticalRatio',
        self::OPPOSE_WAR_MAGIC_TRIAL_PROB => 'warMagicTrialProb',
    ];

    const CALC_DOMESTIC = [
        self::BUFF_KEY_MAP[self::DOMESTIC_SUCCESS_PROB] => [self::DOMESTIC_SUCCESS_PROB, 0.01],
        self::BUFF_KEY_MAP[self::DOMESTIC_FAIL_PROB] => [self::DOMESTIC_FAIL_PROB, -0.01],
    ];

    const CALC_STAT = [
        self::BUFF_KEY_MAP[self::WAR_AVOID_RATIO] => [self::WAR_AVOID_RATIO, 0.01],
        self::BUFF_KEY_MAP[self::WAR_CRITICAL_RATIO] => [self::WAR_CRITICAL_RATIO, 0.01],
        self::BUFF_KEY_MAP[self::WAR_MAGIC_TRIAL_PROB] => [self::WAR_MAGIC_TRIAL_PROB, 0.01],
    ];

    const CALC_OPPOSE_STAT = [
        self::BUFF_KEY_MAP[self::OPPOSE_WAR_AVOID_RATIO] => [self::OPPOSE_WAR_AVOID_RATIO, -0.01],
        self::BUFF_KEY_MAP[self::OPPOSE_WAR_CRITICAL_RATIO] => [self::OPPOSE_WAR_CRITICAL_RATIO, -0.01],
        self::BUFF_KEY_MAP[self::OPPOSE_WAR_MAGIC_TRIAL_PROB] => [self::OPPOSE_WAR_MAGIC_TRIAL_PROB, -0.01],
    ];


    const BUFF_KEY_TEXT = [
        self::WAR_AVOID_RATIO => '회피 확률 증가',
        self::WAR_CRITICAL_RATIO => '필살 확률 증가',
        self::WAR_MAGIC_TRIAL_PROB => '전투계략 시도 확률 증가',

        self::DOMESTIC_SUCCESS_PROB => '내정 성공률 증가',
        self::DOMESTIC_FAIL_PROB => '내정 실패율 감소',

        self::OPPOSE_WAR_AVOID_RATIO => '상대 회피 확률 감소',
        self::OPPOSE_WAR_CRITICAL_RATIO => '상대 필살 확률 감소',
        self::OPPOSE_WAR_MAGIC_TRIAL_PROB => '상대 전투계략 시도 확률 감소',
    ];

    const DOMESTIC_TARGET = [
        '상업' => 1,
        '농업' => 1,
        '치안' => 1,
        '성벽' => 1,
        '수비' => 1,
        '민심' => 1,
        '인구' => 1,
        '기술' => 1,
    ];

    protected array $inheritBuffList;

    public function __construct(array $inheritBuffList)
    {
        $this->inheritBuffList = $inheritBuffList;
    }

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        if (count($this->inheritBuffList) == 0) {
            return $value;
        }
        if (!key_exists($turnType, self::DOMESTIC_TARGET)) {
            return $value;
        }
        if(!key_exists($varType, static::CALC_DOMESTIC)){
            return $value;
        }
        [$iKey, $coeff] = static::CALC_DOMESTIC[$varType];
        if(!key_exists($iKey, $this->inheritBuffList)){
            return $value;
        }
        return $value + $coeff * $this->inheritBuffList[$iKey];
    }

    public function onCalcStat(General $general, string $statName, $value, $aux = null)
    {
        if (count($this->inheritBuffList) == 0) {
            return $value;
        }
        if(!key_exists($statName, static::CALC_STAT)){
            return $value;
        }
        [$iKey, $coeff] = static::CALC_STAT[$statName];
        if(!key_exists($iKey, $this->inheritBuffList)){
            return $value;
        }
        return $value + $coeff * $this->inheritBuffList[$iKey];
    }

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        if (count($this->inheritBuffList) == 0) {
            return $value;
        }
        if(!key_exists($statName, static::CALC_OPPOSE_STAT)){
            return $value;
        }
        [$iKey, $coeff] = static::CALC_OPPOSE_STAT[$statName];
        if(!key_exists($iKey, $this->inheritBuffList)){
            return $value;
        }
        return $value + $coeff * $this->inheritBuffList[$iKey];
    }
}
