<?php
namespace sammo;

trait LazyVarAndAuxUpdater{
    use LazyVarUpdater;

    protected $auxVar = null;
    protected $auxUpdated = false;

    function getRaw(bool $extractAux=false):array{
        if($extractAux){
            $this->getAuxVar('');

        }
        return $this->raw;
    }

    function unpackAux(){
        if(!key_exists('auxVar', $this->raw)){
            if(!key_exists('aux', $this->raw)){
                throw new \RuntimeException('aux is not set');
            }
            $this->raw['auxVar'] = Json::decode($this->raw['aux']??'{}');
        }
    }

    function getAuxVar(string|\BackedEnum $key){
        if($key instanceof \BackedEnum){
            $key = $key->value;
        }
        $this->unpackAux();
        return $this->raw['auxVar'][$key]??null;
    }

    function setAuxVar(string|\BackedEnum $key, $var){
        if($key instanceof \BackedEnum){
            $key = $key->value;
        }
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