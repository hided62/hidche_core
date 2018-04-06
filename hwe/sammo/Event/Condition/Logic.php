<?php
namespace sammo\Event\Condition;

class Logic extends \sammo\Event\Condition{
    private $mode = 'and';

    /** @var \sammo\Event\Condition[] */
    private $conditions = [];
    const AVAILABLE_LOGIC_NAME = [
        'not'=>false, 
        'and'=>true,
        'or'=>true,
        'xor'=>true
    ];

    public function __construct(string $mode, ...$conditions){
        $mode = strtolower($mode);
        if(!array_key_exists($mode, self::AVAILABLE_LOGIC_NAME)){
            throw new \InvalidArgumentException('첫번째 인자는 not, and, or, xor 중 하나여야 합니다.');
        }

        if(!self::AVAILABLE_LOGIC_NAME[$mode] && count($conditions)>1){
            throw new \InvalidArgumentException('조건을 하나만 받을 수 있습니다.');
        }

        $this->mode = $mode;
        $this->conditions = $conditions;
    }

    public function eval($env=null){
        switch($this->mode){
            case 'not':
                return $this->logicNot($env);
            case 'and':
                return $this->logicAnd($env);
            case 'or':
                return $this->logicOr($env);
            case 'xor':
                return $this->logicXor($env);
        }

        throw new \InvalidArgumentException('올바르지 않은 mode.');
    }

    private function logicNot($env){
        $result = self::_eval($this->conditions[0], $env);
        $result['value'] = !$result['value'];
        $result['chain'][] = 'not';
        return $result;
    }

    private function logicAnd($env){
        $value = true;
        $chain = [];

        foreach($this->conditions as $cond){
            $sub = self::_eval($cond, $env);
            $chain[] = $sub['chain'];
            if(!$sub['value']){
                $value = false;
                break;
            }
        }

        return [
            'value'=>$value,
            'chain'=>[$chain, 'and']
        ];
    }

    private function logicOr($env){
        $value = false;
        $chain = [];

        foreach($this->conditions as $cond){
            $sub = self::_eval($cond, $env);
            $chain[] = $sub['chain'];
            if($sub['value']){
                $value = true;
                break;
            }
        }

        return [
            'value'=>$value,
            'chain'=>[$chain, 'or']
        ];
    }

    private function logicXor($env){
        $value = false;
        $chain = [];

        foreach($this->conditions as $cond){
            $sub = self::_eval($cond, $env);
            $chain[] = $sub['chain'];
            $value ^= $sub['value'];
        }

        return [
            'value'=>$value,
            'chain'=>[$chain, 'xor']
        ];
    }


}