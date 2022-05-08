<?php
namespace sammo;

use MeekroDBException;

class KVStorage{
    private $db;
    private $tableName;
    private $storNamespace;
    private $cacheData = null;

    static private $storageList = [];

    static private function convBackedEnum(string|int|\BackedEnum $key): string|int{
        if($key instanceof \BackedEnum){
            return $key->value;
        }
        return $key;
    }

    /**
     * @param (string|int|\BackedEnum)[] $keys
     * @return (string|int)[]
     */
    static private function convBackedEnums(array $keys): array{
        $convKeys = [];
        foreach($keys as $key){
            if($key instanceof \BackedEnum){
                $convKeys[] = $key->value;
            }
            else{
                $convKeys[] = $key;
            }
        }
        return $convKeys;
    }

    static public function getStorage(\MeekroDB $db, $storNamespace, string $tableName='storage'):self{
        $obj_id = spl_object_hash($db);
        $fullKey = $obj_id.','.$storNamespace.','.$tableName;
        if(key_exists($fullKey, static::$storageList)){
            return static::$storageList[$fullKey];
        }
        $obj = new static($db, $storNamespace, $tableName);
        static::$storageList[$fullKey] = $obj;
        return $obj;
    }

    public function __construct(\MeekroDB $db, $storNamespace, string $tableName='storage'){
        $this->db = $db;
        $this->storNamespace = $storNamespace;
        $this->tableName = $tableName;
        $this->turnOnCache();
    }

    public static function getValuesFromInterNamespace(\MeekroDB $db, string $tableName, string|int|\BackedEnum $key):array{
        $result = [];
        $key = self::convBackedEnum($key);
        foreach($db->queryAllLists(
            'SELECT `namespace`, `value` FROM %b WHERE `key`=%s', $tableName, $key
        ) as [$namespaceName, $value])
        {
            $result[$namespaceName] = Json::decode($value);
        }
        return $result;
    }

    public function __get(string|int|\BackedEnum $key) {
        return $this->getValue($key);
    }

    public function __set(string|int|\BackedEnum $key, $value) {
        $this->setValue($key, $value);
    }

    public function __unset(string|int|\BackedEnum $key){
        $this->deleteValue($key);
    }

    public function turnOnCache(): self{
        if($this->cacheData === null){
            $this->cacheData = [];
        }
        return $this;
    }

    public function turnOffCache(): self{
        if($this->cacheData !== null){
            $this->cacheData = null;
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

    public function invalidateCacheValue(string|int|\BackedEnum $key):self{
        if($this->cacheData === null){
            return $this;
        }
        $key = static::convBackedEnum($key);
        if(key_exists($key, $this->cacheData)){
            unset($this->cacheData[$key]);
        }
        return $this;
    }

    public function invalidateCacheValues(array $keys):self{
        if($this->cacheData === null){
            return $this;
        }
        $keys = static::convBackedEnums($keys);

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
        $keys = self::convBackedEnums($keys);

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
        $keys = static::convBackedEnums($keys);

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
        $keys = static::convBackedEnums($keys);

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

    public function getValue(string|int|\BackedEnum $key, bool $onlyCache=false){
        $key = static::convBackedEnum($key);
        if($this->cacheData !== null && ($onlyCache || key_exists($key, $this->cacheData))){
            return $this->cacheData[$key] ?? null;
        }

        $value = $this->getDBValue($key);
        if($this->cacheData !== null){
            $this->cacheData[$key] = $value;
        }
        return $value;
    }

    /**
     * @param mixed $value
     * @return KVStorage
     * @throws MeekroDBException
     */
    public function setValue(string|int|\BackedEnum $key, $value):self{
        $key = static::convBackedEnum($key);

        if($value === null){
            return $this->deleteValue($key);
        }

        if($this->cacheData){
            $this->cacheData[$key] = $value;
        }
        return $this->setDBValue($key, $value);
    }

    /**
     *
     * @param string|int $key
     * @param mixed $value
     * @return KVStorage
     * @throws MeekroDBException
     */
    public function deleteValue(string|int|\BackedEnum $key):self{
        $key = static::convBackedEnum($key);

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