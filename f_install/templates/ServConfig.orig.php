<?php

namespace sammo;

class ServConfig
{
    private function __construct()
    {
    }

    public static $serverWebPath = '_tK_serverBasePath_';
    public static $sharedIconPath = '_tK_sharedIconPath_';
    public static $gameImagePath = "_tK_gameImagePath_";
    public static $imageRequestPath = "_tK_imageRequestPath_";
    public static $imageRequestKey = '_tK_imageRequestKey_';
    private static $serverList = null;

    public static function getSharedIconPath(string $filepath = ''): string
    {
        if ($filepath) {
            return static::$sharedIconPath . "/{$filepath}";
        }
        return static::$sharedIconPath;
    }

    public static function getUserIconPath(string $filepath = ''): string
    {
        return AppConf::getUserIconPathWeb($filepath);
    }

    public static function getGameImagePath(string $filepath = ''): string
    {
        if ($filepath) {
            return static::$gameImagePath . "/{$filepath}";
        }
        return static::$gameImagePath;
    }

    public static function getImagePullURI(): string
    {
        $now = time();
        $req_hash = Util::hashPassword(sprintf("%016x", $now), static::$imageRequestKey);
        return static::$imageRequestPath . "?req={$req_hash}&time={$now}";
    }

    /**
     * 서버 설정 반환
     *
     * @return \sammo\Setting[]
     */
    public static function getServerList(): array{
        $servKeyList = [/*_tK_serverList_*/];
        $servKeyList[] = ['hwe', '훼', 'red'];

        if (self::$serverList === null) {
            self::$serverList = [];
            foreach($servKeyList as [$servKey, $servNick, $servColor]){
                self::$serverList[$servKey] = new Setting(ROOT.'/'.$servKey, $servNick, $servColor);
            }
        }
        return self::$serverList;
    }


    /**
     * 서버 주소 반환. 서버의 경로가 하부 디렉토리인 경우에 하부 디렉토리까지 포함
     *
     * @return string
     */
    public static function getServerBasepath(): string
    {
        return self::$serverWebPath;
    }
}
