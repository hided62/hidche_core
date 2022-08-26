<?php
namespace sammo;

trait LazyVarUpdater{
    protected $raw = [];
    protected $updatedVar = [];
    protected $auxVar = null;
    protected $auxUpdated = false;

    function getRaw(bool $extractAux=false):array{
        if($extractAux){
            $this->getAuxVar('');

        }
        return $this->raw;
    }

    function getVar(string $key){
        return $this->raw[$key];
    }

    function getVars(string ...$keys){
        return array_map([$this, 'getVar'], $keys);
    }

    function touchVar(string $key):bool{
        if(key_exists($key, $this->raw)){
            return false;
        }
        $this->raw[$key] = null;

        return true;
    }

    function unpackAux(){
        if(!key_exists('auxVar', $this->raw)){
            if(!key_exists('aux', $this->raw)){
                throw new \RuntimeException('aux is not set');
            }
            $this->raw['auxVar'] = Json::decode($this->raw['aux']??'{}');
        }
    }

    function setVar(string $key, $value){
        return $this->updateVar($key, $value);
    }

    function getAuxVar(string $key){
        $this->unpackAux();
        return $this->raw['auxVar'][$key]??null;
    }

    function setAuxVar(string $key, $var){
        $oldVar = $this->getAuxVar($key);

        if($oldVar === $var){
            return;
        }

        if($var === null){
            unset($this->raw['auxVar'][$key]);
            $this->auxUpdated = true;
            return;
        }
        $this->raw['auxVar'][$key] = $var;
        $this->auxUpdated = true;
    }

    function updateVar(string $key, $value){
        if(($this->raw[$key]??null) === $value){
            return;
        }
        if(!key_exists($key, $this->updatedVar)){
            $this->updatedVar[$key] = true;
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
        if($this->auxUpdated){
            $this->setVar('aux', Json::encode($this->raw['auxVar']));
            $this->auxUpdated = false;
        }
        $updateVals = [];
        foreach(array_keys($this->updatedVar) as $key){
            $updateVals[$key] = $this->raw[$key];
        }
        return $updateVals;
    }

    function flushUpdateValues():void {
        $this->updatedVar = [];
        if(key_exists('auxVar', $this->raw)){
            $this->auxUpdated = false;
            unset($this->raw['auxVar']);
        }
    }
}