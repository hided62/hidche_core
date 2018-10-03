<?php
namespace sammo;

trait LazyVarUpdater{
    protected $raw = [];
    protected $updatedVar = [];

    function getRaw():array{
        return $this->raw;
    }

    function getVar(string $key){
        return $this->raw[$key];
    }

    function touchVar(string $key):bool{
        if(key_exists($key, $this->raw)){
            return false;
        }
        $this->raw[$key] = null;

        return true;
    }

    function setVar(string $key, $value){
        return $this->updateVar($key, $value);
    }

    function updateVar(string $key, $value){
        if($this->raw[$key] === $value){
            return;
        }
        if(!key_exists($key, $this->updatedVar)){
            $this->updatedVar[$key] = $this->raw[$key];
        }
        $this->raw[$key] = $value;
    }

    function updateVarWithLimit(string $key, $value, $min = null, $max = null){
        if($min !== null && $value < $min){
            $value = $min;
        }
        if($max !== null && $value > $max){
            $value = $max;
        }
        $this->updateVar($key, $value);
    }

    function increaseVar(string $key, $value)
    {
        if($value === 0){
            return;
        }
        $targetValue = $this->raw[$key] + $value;
        $this->updateVar($key, $targetValue);
    }

    function increaseVarWithLimit(string $key, $value, $min = null, $max = null){
        $targetValue = $this->raw[$key] + $value;
        if($min !== null && $targetValue < $min){
            $targetValue = $min;
        }
        if($max !== null && $targetValue > $max){
            $targetValue = $max;
        }
        $this->updateVar($key, $targetValue);
    }

    function multiplyVar(string $key, $value)
    {
        if($value === 1){
            return;
        }
        $targetValue = $this->raw[$key] * $value;
        $this->updateVar($key, $targetValue);
    }

    function multiplyVarWithLimit(string $key, $value, $min = null, $max = null){
        $targetValue = $this->raw[$key] * $value;
        if($min !== null && $targetValue < $min){
            $targetValue = $min;
        }
        if($max !== null && $targetValue > $max){
            $targetValue = $max;
        }
        $this->updateVar($key, $targetValue);
    }

    function getUpdatedValues():array {
        $updateVals = [];
        foreach(array_keys($this->updatedVar) as $key){
            $updateVals[$key] = $this->raw[$key];
        }
        return $updateVals;
    }
}