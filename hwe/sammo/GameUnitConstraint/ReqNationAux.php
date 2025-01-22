<?php

namespace sammo\GameUnitConstraint;

use sammo\CityConst;
use sammo\Enums\NationAuxKey;
use sammo\General;

class ReqNationAux extends BaseGameUnitConstraint
{

    const AVAILABLE_CMP = [
        '==' => true,
        '!=' => true,
        '<' => true,
        '>' => true,
        '<=' => true,
        '>=' => true,
    ];

    public function __construct(
        public readonly NationAuxKey $reqNationAuxKey,
        public readonly string $cmp,
        public readonly int|float $value
    ) {
        if (!array_key_exists($cmp, self::AVAILABLE_CMP)) {
            throw new \InvalidArgumentException('올바르지 않은 비교연산자입니다');
        }
    }

    public function test(General $general, array $ownCities, array $ownRegions, int $relativeYear, int $tech, array $nationAux): bool
    {

        $lhs = $nationAux[$this->reqNationAuxKey->value] ?? 0;
        $rhs = $this->value;

        $value = false;
        switch ($this->cmp) {
            case '==':
                $value = ($lhs == $rhs);
                break;
            case '!=':
                $value = ($lhs != $rhs);
                break;
            case '<=':
                $value = ($lhs <= $rhs);
                break;
            case '>=':
                $value = ($lhs >= $rhs);
                break;
            case '<':
                $value = ($lhs < $rhs);
                break;
            case '>':
                $value = ($lhs > $rhs);
                break;
        }

        return $value;
    }

    public function getInfo(): string
    {
        //Enum별 특수한 경우
        switch ($this->reqNationAuxKey) {
            case NationAuxKey::can_대검병사용:
                if ($this->cmp == "==" && $this->value == 1) return "대검병 연구 시 가능";
                break;
            case NationAuxKey::can_극병사용:
                if ($this->cmp == "==" && $this->value == 1) return "극병 연구 시 가능";
                break;
            case NationAuxKey::can_화시병사용:
                if ($this->cmp == "==" && $this->value == 1) return "화시병 연구 시 가능";
                break;
            case NationAuxKey::can_원융노병사용:
                if ($this->cmp == "==" && $this->value == 1) return "원융노병 연구 시 가능";
                break;
            case NationAuxKey::can_산저병사용:
                if ($this->cmp == "==" && $this->value == 1) return "산저병 연구 시 가능";
                break;
            case NationAuxKey::can_상병사용:
                if ($this->cmp == "==" && $this->value == 1) return "상병 연구 시 가능";
                break;
            case NationAuxKey::can_음귀병사용:
                if ($this->cmp == "==" && $this->value == 1) return "음귀병 연구 시 가능";
                break;
            case NationAuxKey::can_무희사용:
                if ($this->cmp == "==" && $this->value == 1) return "무희 연구 시 가능";
                break;
            case NationAuxKey::can_화륜차사용:
                if ($this->cmp == "==" && $this->value == 1) return "화륜차 연구 시 가능";
                break;
            case NationAuxKey::did_특성초토화:
                if ($this->cmp == ">=" && $this->value == 1) return "특성 초토화 시 가능";
                break;
        }

        //범용
        switch ($this->cmp) {
            case '==': {
                    if ($this->value == 0) {
                        return "{$this->reqNationAuxKey->value} 없을 때";
                    }
                    if ($this->value == 1) {
                        return "{$this->reqNationAuxKey->value} 있을 때";
                    }
                    return "{$this->reqNationAuxKey->value} = {$this->value} 일 때";
                };
            case '!=': {
                    if ($this->value == 0) {
                        return "{$this->reqNationAuxKey->value} 없을 때";
                    }
                    if ($this->value == 1) {
                        return "{$this->reqNationAuxKey->value} 있을 때";
                    }
                    return "{$this->reqNationAuxKey->value} != {$this->value} 일 때";
                };
            default:
                return "{$this->reqNationAuxKey->value} {$this->cmp} {$this->value} 일 때";
        }
    }
}
