<?php

namespace sammo\API\Global;

use sammo\Session;
use DateTimeInterface;
use Nette\Caching\Cache;
use sammo\APICacheResult;
use sammo\Enums\APIRecoveryType;
use sammo\GameConst;
use sammo\MapRequest;
use sammo\TimeUtil;
use sammo\UniqueConst;
use sammo\Util;
use sammo\Validator;

use function sammo\getGlobalHistoryLogRecent;
use function sammo\getWorldMap;
use function sammo\prepareDir;

class GetCachedMap extends \sammo\BaseAPI
{
  const CACHE_SECONDS = 600;
  private ?\DateTimeInterface $cachedTime = null;

  public function validateArgs(): ?string
  {
    return null;
  }

  public function tryCache(): ?APICacheResult
  {
    if ($this->cachedTime === null) {
      return null;
    }

    $now = TimeUtil::nowDateTimeImmutable();
    $diff = TimeUtil::DateIntervalToSeconds($this->cachedTime->diff($now));
    $nextCacheTime = self::CACHE_SECONDS - $diff;
    return new APICacheResult($this->cachedTime, null, Util::toInt($nextCacheTime), true);
  }

  public function getRequiredSessionMode(): int
  {
    return static::NO_SESSION;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    if (!class_exists('\\sammo\\UniqueConst')) {
      return '서버 초기화되지 않음';
    }

    if (!prepareDir('data/file_cache')) {
      return 'cache 불가';
    }

    $storage = new \Nette\Caching\Storages\FileStorage('data/file_cache');
    $cache = new Cache($storage);

    $now = TimeUtil::nowDateTimeImmutable();
    if ($modifiedSince) {
      $diff = TimeUtil::DateIntervalToSeconds($modifiedSince->diff($now));
      if (0 <= $diff && $diff < self::CACHE_SECONDS) {
        $this->cachedTime = $modifiedSince;
        return null;
      }
    }

    $mapInfo = $cache->load("recent_map");
    if ($mapInfo) {
      $cachedTime = TimeUtil::secondsToDateTime($mapInfo['timestamp'], true, true);
      $diff = $cachedTime->diff($now);
      if (0 <= $diff && $diff < self::CACHE_SECONDS) {
        $this->cachedTime = $cachedTime;
        return $mapInfo['data'];
      }
    }

    $history = getGlobalHistoryLogRecent(10);
    $cachedMap = getWorldMap([
      'year' => null,
      'month' => null,
      'aux' => null,
      'neutralView' => true,
      'showMe' => false,
    ]);

    $cachedMap['history'] = $history;
    $cachedMap['theme'] = GameConst::$mapName;
    $timestamp = $now->getTimestamp();
    $this->cachedTime = $now;

    $map = [
      'timestamp' => $timestamp,
      'data' => $cachedMap,
    ];
    $cache->save("recent_map", $map);

    return $cachedMap;
  }
}
