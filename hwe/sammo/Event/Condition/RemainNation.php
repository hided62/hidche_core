<?php
namespace sammo\Event\Condition;

use function sammo\getAllNationStaticInfo;

class RemainNation extends \sammo\Event\Condition{

    const AVAILABLE_CMP = [
        '=='=>true,
        '!='=>true,
        '<'=>true,
        '>'=>true,
        '<='=>true,
        '>='=>true,
    ];

    private $cmp;
    private $cnt;

    public function __construct(string $cmp, int $cnt){
        //Cmp('==', '!=', '<=', '>=', '<', '>'), Cnt
        if(!array_key_exists($cmp, self::AVAILABLE_CMP)){
            throw new \InvalidArgumentException('올바르지 않은 비교연산자입니다');
        }

        $this->cmp = $cmp;
        $this->cnt = $cnt;
    }

    public function eval($env=null){
        $lhs = count(getAllNationStaticInfo());
        $rhs = $this->cnt;

        $value = false;
        switch($this->cmp){
            case '==': $value = ($lhs == $rhs); break;
            case '!=': $value = ($lhs != $rhs); break;
            case '<=': $value = ($lhs <= $rhs); break;
            case '>=': $value = ($lhs >= $rhs); break;
            case '<': $value = ($lhs < $rhs); break;
            case '>': $value = ($lhs > $rhs); break;
        }

        return [
            'value'=>$value,
            'chain'=>[__CLASS__]
        ];

    }
}