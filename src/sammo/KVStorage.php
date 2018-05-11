<?php
namespace sammo;

class KVStorage{
    private $db = null;
    private $storNamespace;
    private $cacheData = null;

    public function __construct(\MeekroDB $db, string $storNamespace, bool $cacheMode=false){
        $this->db = $db;
        $this->storNamespace = $storNamespace;
        if($cacheMode){
            $this->cacheData = [];
        }
    }

    public function __get($key) {
        return $this->getValue($key);
    }
    
    public function __set($key, $value) {
        return $this->setValue($key, $value);
    }

    public function resetCache(bool $disableCache=true):self{
        if($disableCache){
            $this->cacheData = null;
        }
        else{
            $this->cacheData = [];
        }
        return $this;
    }

    public function resetValues():self{
        if($this->cacheData !== null){
            $this->cacheData = [];
        }
        return $this->resetDBNamespace();
    }

    public function invalidateCacheValue($key):self{
        if($this->cacheData === null){
            return $this;
        }
        if(key_exists($key, $this->cacheData)){
            unset($this->cacheData[$key]);
        }
        return $this;
    }

    public function invalidateCacheValues(array $keys):self{
        if($this->cacheData === null){
            return $this;
        }
        foreach($keys as $key){
            if(key_exists($key, $this->cacheData)){
                unset($this->cacheData[$key]);
            }
        }
        
        return $this;
    }

    public function cacheAll():self{
        $this->cacheData = $this->getDBAll();
        return $this;
    }

    public function cacheValues(array $keys):self{
        if($this->cacheData === null){
            $this->cacheData = [];
        }
        $values = $this->getDBValues($keys);
        foreach($keys as $key){
            if(key_exists($key, $values)){
                $this->cacheData[$key] = $values[$key];
            }
            else{
                $this->cacheData[$key] = null;
            }
        }
        
        return $this;
    }

    public function getAll(bool $onlyCache=false): array{
        if($onlyCache && $this->cacheData !== null && count($this->cacheData) > 0){
            return $this->cacheData;
        }
        $result = $this->getAll();
        if($this->cacheData !== null){
            $this->cacheData = $result;
        }
        return $result;
    }

    public function getValues(array $keys, bool $onlyCache=false): array{
        if ($this->cacheData === null) {
            return $this->getDBValues($keys);
        }

        $result = [];
        $notExists = [];
        
        foreach($keys as $key){
            if(!key_exists($key, $this->cacheData)){
                $notExists[] = $key;
                continue;
            }

            $result[$key] = $this->cacheData[$key];
        }

        if($onlyCache){
            foreach($notExists as $emptyKey){
                $result[$emptyKey] = null;
            }

            return $result;
        }

        $dbResult = $this->getDBValues($notExists);
        foreach($dbResult as $key=>$value){
            $result[$key] = $value;
            $this->cacheData[$key] = $value;
        }
        return $result;
    }

    public function getValue($key, bool $onlyCache=false){
        if($this->cacheData !== null && ($onlyCache || key_exists($key, $this->cacheData))){
            return $this->cacheData[$key] ?? null;
        }

        $value = $this->getDBValue($key);
        if($this->cacheData !== null){
            $this->cacheData[$key] = $value;
        }
        return $value;
    }

    public function setValue($key, $value):self{
        if($value === null){
            return $this->deleteValue($key);
        }

        if($this->cacheData){
            $this->cacheData[$key] = $value;
        }
        return $this->setDBValue($key, $value);
    }

    public function deleteValue($key):self{
        if(isset($this->cacheData[$key])){
            unset($this->cacheData[$key]);
        }
        return $this->deleteDBValue($key);
    }

    private function getDBAll(): array{
        $result = [];
        foreach($this->db->queryAllLists(
            'SELECT `key`, `value` FROM storage WHERE `namespace`=%s',
            $this->storNamespace
        ) as list($key, $value))
        {
            $result[$key] = Json::decode($value);
        }
        return $value;
    }

    private function getDBValues(array $keys): array{
        $result = [];
        foreach($this->db->queryAllLists(
            'SELECT `key`, `value` FROM storage WHERE `namespace`=%s AND `key` IN %ls', 
            $this->storNamespace, 
            $keys
        ) as list($key, $value))
        {
            $result[$key] = Json::decode($value);
        }
        foreach($keys as $key){
            if(!key_exists($key, $result)){
                $result[$key] = null;
            }
        }
        return $value;
    }

    private function getDBValue($key){
        $value = $this->db->queryFirstField(
            'SELECT `value` FROM storage WHERE `namespace`=%s AND `key`=%s',
            $this->storNamespace,
            $key
        );
        if($value === null){
            return null;
        }
        return Json::decode($value);
    }

    private function setDBValue($key, $value):self{
        if($value === null){
            return $this->deleteDBValue($key);
        }
        $this->db->insertUpdate('storage', [
            'namespace'=>$this->storNamespace,
            'key'=>$key,
            'value'=>Json::encode($value)
        ]);
        return $this;
    }

    private function deleteDBValue($key):self{
        $this->db->delete('storage', [
            'namespace'=>$this->storNamespace,
            'key'=>$key
        ]);
        return $this;
    }

    private function resetDBNamespace():self{
        $this->db->delete('storage', [
            'namespace'=>$this->storNamespace
        ]);
        return $this;
    }
}