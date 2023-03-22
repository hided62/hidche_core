<?php

namespace sammo\API\Global;

use sammo\Session;
use DateTimeInterface;
use sammo\VersionGit;
use Nette\Caching\Cache;
use RuntimeException;
use sammo\APICacheResult;
use sammo\CityConst;
use sammo\Enums\APIRecoveryType;
use sammo\GameConst;
use sammo\GameUnitConst;
use sammo\Json;
use sammo\TimeUtil;
use sammo\UniqueConst;

use function sammo\prepareDir;

class GetConst extends \sammo\BaseAPI
{
    /** 반환하는 StaticValues 타입이 달라지면 +1 */
    const CONST_API_VERSION = 3;
    const CACHE_KEY = 'JSConst';

    private ?string $cacheKey = null;

    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::NO_SESSION;
    }

    public function findLastModified(): ?int
    {
        $rawResult = explode("\n", shell_exec('git status -u -s --no-rename'));
        $files = [];
        foreach ($rawResult as $rawLine) {
            if (strlen($rawLine) < 3) {
                continue;
            }
            $filePath = trim(substr($rawLine, 3));
            if ($filePath[0] == '"') {
                $filePath = substr($filePath, 1, strlen($filePath) - 2);
            }
            if (!file_exists($filePath)) {
                continue;
            }
            $files[] = $filePath;
        }

        $lastModified = null;
        foreach ($files as $filePath) {
            $fileModified = filemtime($filePath);
            if ($fileModified === false) {
                continue;
            }
            if ($lastModified === null) {
                $lastModified = $fileModified;
                continue;
            }
            if ($lastModified > $fileModified) {
                continue;
            }
            $lastModified = $fileModified;
        }
        if ($lastModified === null) {
            return null;
        }
        return $lastModified;
    }

    public function getCacheKey(): string
    {
        if ($this->cacheKey !== null) {
            return $this->cacheKey;
        }

        if (is_subclass_of('\\sammo\\VersionGit', '\\sammo\VersionGitDynamic')) {
            $lastModified = $this->findLastModified();
            if ($lastModified !== null) {
                $versionHash = "lt{$lastModified}";
            } else {
                $versionHash = VersionGit::getVersion();
            }
        } else {
            $versionHash = VersionGit::getVersion();
        }

        $apiVersion = static::CONST_API_VERSION;
        $serverID = UniqueConst::$serverID;


        $cacheKey = "{$apiVersion}_{$serverID}_{$versionHash}";
        $this->cacheKey = $cacheKey;

        return $cacheKey;
    }

    public function tryCache(): ?APICacheResult
    {
        if (is_subclass_of('\\sammo\\VersionGit', '\\sammo\VersionGitDynamic')) {
            return new APICacheResult(TimeUtil::secondsToDateTime($this->findLastModified()??\time(), true, true));
        }

        return new APICacheResult(null, $this->getCacheKey(), 60, true);
    }

    public function readCache(Cache $cache): null|string|array|int|float|bool
    {
        $rawJSONCache = $cache->load(static::CACHE_KEY);
        if ($rawJSONCache === null) {
            return null;
        }
        $jsonCache = Json::decode($rawJSONCache);
        if (!key_exists('cacheKey', $jsonCache)) {
            return null;
        }

        $jsonCacheKey = $jsonCache['cacheKey'];
        if ($jsonCacheKey !== $this->getCacheKey()) {
            return null;
        }

        return $jsonCache['data'] ?? null;
    }

    public function extractObjClassInfo(string $objKey, callable $callerFunction): array
    {
        /** @var \sammo\iAction */
        $target = $callerFunction($objKey);
        if (!($target instanceof \sammo\iAction)) {
            throw new \RuntimeException("{$objKey}의 대상이 iAction이 아님");
        }
        return [
            'value' => $objKey,
            'name' => $target->getName(),
            'info' => $target->getInfo(),
        ];
    }

    public function extractObjClassInfoFromArray(array $constArray, callable $callerFunction): array
    {
        $result = [];
        foreach ($constArray as $key => $target) {
            if (is_string($target)) {
                $actionInfo = $this->extractObjClassInfo($target, $callerFunction);
                if (is_string($key)) {
                    $result[$key] = $actionInfo;
                } else {
                    $result[$target] = $actionInfo;
                }
                continue;
            }
            if (is_array($target)) {
                $result[$key] = $this->extractObjClassInfoFromArray($target, $callerFunction);
                continue;
            }
            if(is_string($key) && is_int($target)){
                //역전된 상황이다.
                $result[$key] = $this->extractObjClassInfo($key, $callerFunction);
                continue;
            }
            $result[$key] = $target;
        }
        return $result;
    }

    public function extractObjClassInfoFromGameConst(string $gameConstKey, callable $callerFunction): array
    {
        $gameConstKeyList = explode(".", $gameConstKey);
        $target = (GameConst::${$gameConstKeyList[0]}) ?? [];
        foreach(\array_slice($gameConstKeyList, 1) as $gameConstSubKey){
            $target = $target[$gameConstSubKey] ?? [];
        }

        if (is_string($target)) {
            return [$target => $this->extractObjClassInfo($target, $callerFunction)];
        }

        if (!is_array($target)) {
            throw new \RuntimeException("GameConst::{$gameConstKey}의 값이 {$target}임");
        }

        return $this->extractObjClassInfoFromArray($target, $callerFunction);
    }

    public function genConstData()
    {
        /** @var array<string,array{0:string,1:string[],2?:int> */
        $gameConstKeys = [
            'nationType' => [
                '\sammo\buildNationTypeClass',
                ['availableNationType', 'neutralNationType']
            ],
            'specialDomestic' => [
                '\sammo\buildGeneralSpecialDomesticClass',
                ['defaultSpecialDomestic', 'availableSpecialDomestic', 'optionalSpecialDomestic']
            ],
            'specialWar' => [
                '\sammo\buildGeneralSpecialWarClass',
                ['defaultSpecialWar', 'availableSpecialWar', 'optionalSpecialWar']
            ],
            'personality' => [
                '\sammo\buildPersonalityClass',
                ['neutralPersonality', 'availablePersonality', 'optionalPersonality']
            ],
            'item' => [
                '\sammo\buildItemClass',
                ['allItems'],
                1
            ],
        ];
        //GameConst 중 BaseCommand는 시간 흐름, 장수에 따라 정보가 달라지므로 따로 처리해야함


        $iActionInfo = [];
        $iActionKeyMap = [];
        foreach ($gameConstKeys as $mappedKey => $callerTarget) {
            $flatLevel = 0;
            if (is_array($callerTarget)) {
                $callerFunction = $callerTarget[0];
                $gameConstSubKey = $callerTarget[1];
                if(count($callerTarget) > 2){
                    /** @var int */
                    $flatLevel = $callerTarget[2];
                }
            } else {
                $gameConstSubKey = [$mappedKey];
                $callerFunction = $callerTarget;
            }

            if (!is_callable($callerFunction)) {
                throw new \RuntimeException("{$mappedKey} => {$callerFunction}이 callable이 아님");
            }

            $actionInfo = [];
            foreach ($gameConstSubKey as $key) {
                $appendInfo = $this->extractObjClassInfoFromGameConst($key, $callerFunction);
                $actionInfo = array_merge($actionInfo, $appendInfo);
                $iActionKeyMap[$key] = $mappedKey;
            }

            if($flatLevel > 0){
                foreach(range(0, $flatLevel - 1) as $tryFlatLevel){
                    $actionInfo = array_merge(...array_values($actionInfo));
                }
            }


            $iActionInfo[$mappedKey] = $actionInfo;
        }

        $crewtypeMap = [];
        foreach(GameUnitConst::all() as $crewtypeObj){
            $crewtypeMap[$crewtypeObj->id] = [
                'value'=>(string)$crewtypeObj->id,
                'name'=>$crewtypeObj->name,
                'info'=>$crewtypeObj->getInfo(),
            ];
        }
        $iActionInfo['crewtype'] = $crewtypeMap;


        return [
            'gameConst' => get_class_vars('\sammo\GameConst'),
            'gameUnitConst' => GameUnitConst::all(),
            'cityConst' => CityConst::all(),
            'cityConstMap' => [
                'region' => CityConst::$regionMap,
                'level' => CityConst::$levelMap,
            ],
            'iActionInfo' => $iActionInfo,
            'iActionKeyMap' => $iActionKeyMap,
            'version' => VersionGit::getVersion(),
        ];
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $cacheDir = $this->rootPath . '/data/file_cache';

        if (!prepareDir($cacheDir)) {
            throw new RuntimeException('cache 폴더 없음');
        }
        $storage = new \Nette\Caching\Storages\FileStorage($cacheDir);
        $cache = new Cache($storage);

        $currentCacheKey = $this->tryCache();
        if($modifiedSince !== null && $currentCacheKey->lastModified == $modifiedSince){
            return null;
        }
        if($reqEtag !== null && $currentCacheKey->etag == $reqEtag){
            return null;
        }

        $constCache = $this->readCache($cache);
        if ($constCache !== null) {
            return [
                'result' => true,
                'cacheKey' => $this->getCacheKey(),
                'data' => $constCache,
            ];
        }

        $constCache = $this->genConstData();
        $cache->save(static::CACHE_KEY, Json::encode([
            'cacheKey' => $this->getCacheKey(),
            'data' => $constCache
        ]));

        return [
            'result' => true,
            'cacheKey' => $this->getCacheKey(),
            'data' => $constCache,
        ];
    }
}
