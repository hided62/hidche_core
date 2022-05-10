<?php
namespace sammo\Event\Condition;

/** 시작년도 기준으로 '년'을 처리하는 Condition */
class DateRelative extends \sammo\Event\Condition{

    const AVAILABLE_CMP = [
        '=='=>true,
        '!='=>true,
        '<'=>true,
        '>'=>true,
        '<='=>true,
        '>='=>true,
    ];

    private $cmp;
    private $year;
    private $month;

    public function __construct(string $cmp, ?int $year, ?int $month){
        //Cmp('==', '!=', '<=', '>=', '<', '>'), Year, Month(Optional)
        if(!array_key_exists($cmp, self::AVAILABLE_CMP)){
            throw new \InvalidArgumentException('올바르지 않은 비교연산자입니다');
        }

        if($year === null && $month === null){
            throw new \InvalidArgumentException('year과 month가 둘다 null일 수 없습니다.');
        }

        $this->cmp = $cmp;
        $this->year = $year;
        $this->month = $month;
    }

    public function eval($env=null){
        if($env === null){
            return [
                'value'=>false,
                'chain'=>[__CLASS__]
            ];
        }

        if($this->year !== null){
            if(!isset($env['year'])){
                throw new \InvalidArgumentException('env에 year가 없습니다.');
            }
            if(!isset($env['startyear'])){
                throw new \InvalidArgumentException('env에 startyear가 없습니다.');
            }
        }

        if($this->month !== null && !isset($env['month'])){
            throw new \InvalidArgumentException('env에 month가 없습니다.');
        }

        $lhs = [
            $this->year!==null?(int)($env['year'] - $env['startyear']):null,
            $this->month!==null?(int)$env['month']:null
        ];

        $rhs = [
            $this->year,
            $this->month
        ];


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