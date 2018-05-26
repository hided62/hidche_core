<?php
namespace sammo;

class KVStorage{
    private $db;
    private $tableName;
    private $storNamespace;
    private $cacheData = null;

    static private $storageList = [];

    static public function getStorage(\MeekroDB $db, string $storNamespace, string $tableName='storage'):self{
        $obj_id = spl_object_hash($db);
        $fullKey = $obj_id.','.$storNamespace.','.$tableName;
        if(key_exists($fullKey, static::$storageList)){
            return static::$storageList[$fullKey];
        }
        $obj = new static($db, $storNamespace, $tableName);
        static::$storageList[$fullKey] = $obj;
        return $obj;
    }

    public function __construct(\MeekroDB $db, string $storNamespace, string $tableName='storage'){
        $this->db = $db;
        $this->storNamespace = $storNamespace;
        $this->tableName = $tableName;
    }

    public function __get($key) {
        return $this->getValue($key);
    }
    
    public function __set($key, $value) {
        return $this->setValue($key, $value);
    }

    public function __unset($key){
        $this->deleteValue($key);
    }

    public function turnOnCache(): self{
        if($this->cacheData === null){
            $this->cacheData = [];
        }
        return $this;
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
        $key = mb_strtolower($key);
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
        $keys = array_map('mb_strtolower', $keys);
        
        foreach($keys as $key){
            if(key_exists($key, $this->cacheData)){
                unset($this->cacheData[$key]);
            }
        }
        
        return $this;
    }

    public function cacheAll(bool $invalidateAll=true):self{
        if(!$invalidateAll && $this->cacheData !== null && count($this->cacheData)>0){
            return $this;
        }
        $this->cacheData = $this->getDBAll();
        return $this;
    }

    public function cacheValues(array $keys, bool $invalidateAll=false):self{
        if($this->cacheData === null){
            $this->cacheData = [];
        }
        $keys = array_map('mb_strtolower', $keys);

        if($invalidateAll){
            $notExists = $keys;
        }
        else{
            $notExists = [];
            foreach($keys as $key){
                if(!key_exists($key, $this->cacheData)){
                    $notExists[] = $key;
                }
            }
            if(!$notExists){
                return $this;
            }
        }

        $values = $this->getDBValues($notExists);
        foreach($notExists as $key){
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
        $result = $this->getDBAll();
        if($this->cacheData !== null){
            $this->cacheData = $result;
        }
        return $result;
    }

    public function getValuesAsArray(array $keys, bool $onlyCache=false): array{
        if(!$keys){
            return [];
        }
        $keys = array_map('mb_strtolower', $keys);
        $dictResult = $this->getValues($keys, $onlyCache);
        $result = [];
        foreach($keys as $key){
            $result[] = $dictResult[$key]??null;
        }
        return $result;
    }

    public function getValues(array $keys, bool $onlyCache=false): array{
        if(!$keys){
            return [];
        }
        $keys = array_map('mb_strtolower', $keys);
        if ($this->cacheData === null) {
            return $this->getDBValues($keys);
        }

        $result = [];
        $notExists = [];
        
        //TODO: DB Select에서 as를 쓸 수 있으면 좋을 듯.
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
        $key = mb_strtolower($key);
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
        $key = mb_strtolower($key);
        if($value === null){
            return $this->deleteValue($key);
        }

        if($this->cacheData){
            $this->cacheData[$key] = $value;
        }
        return $this->setDBValue($key, $value);
    }

    public function deleteValue($key):self{
        $key = mb_strtolower($key);
        if(isset($this->cacheData[$key])){
            unset($this->cacheData[$key]);
        }
        return $this->deleteDBValue($key);
    }

    private function getDBAll(): array{
        $result = [];
        foreach($this->db->queryAllLists(
            'SELECT `key`, `value` FROM %b WHERE `namespace`=%s',
            $this->tableName,
            $this->storNamespace
        ) as list($key, $value))
        {
            $result[$key] = Json::decode($value);
        }
        return $result;
    }

    private function getDBValues(array $keys): array{
        if(!$keys){
            return [];
        }
        $result = [];
        foreach($this->db->queryAllLists(
            'SELECT `key`, `value` FROM %b WHERE `namespace`=%s AND `key` IN %ls', 
            $this->tableName,
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
        return $result;
    }

    private function getDBValue(string $key){
        $value = $this->db->queryFirstField(
            'SELECT `value` FROM %b WHERE `namespace`=%s AND `key`=%s',
            $this->tableName,
            $this->storNamespace,
            $key
        );
        if($value === null){
            return null;
        }
        return Json::decode($value);
    }

    private function setDBValue(string $key, $value):self{
        if($value === null){
            return $this->deleteDBValue($key);
        }
        $this->db->insertUpdate($this->tableName, [
            'namespace'=>$this->storNamespace,
            'key'=>$key,
            'value'=>Json::encode($value)
        ]);
        return $this;
    }

    private function deleteDBValue(string $key):self{
        $this->db->delete(
            $this->tableName,
            '`namespace`=%s AND `key`=%s',
            $this->storNamespace,
            $key
        );
        return $this;
    }

    private function resetDBNamespace():self{
        $this->db->delete($this->tableName, 'namespace=%s', $this->storNamespace);
        return $this;
    }
}