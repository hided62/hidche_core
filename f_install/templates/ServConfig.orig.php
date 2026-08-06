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
    public static $remoteUserIconUploadEnabled = _tK_remoteUserIconUploadEnabled_;
    public static $remoteUserIconUploadPath = '_tK_remoteUserIconUploadPath_';
    public static $remoteUserIconUploadSecretFile = '_tK_remoteUserIconUploadSecretFile_';
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
        return static::$imageRequestPath;
    }

    public static function isRemoteUserIconUploadEnabled(): bool
    {
        return static::$remoteUserIconUploadEnabled;
    }

    public static function getRemoteUserIconUploadURI(string $filename): string
    {
        return rtrim(static::$remoteUserIconUploadPath, '/') . '/v1/uploads/user-icons/core/' . $filename;
    }

    public static function getRemoteUserIconUploadSecret(): string
    {
        $path = static::$remoteUserIconUploadSecretFile;
        if ($path === '' || str_contains($path, "\0")) {
            throw new \RuntimeException('Remote user icon upload secret file is not configured');
        }
        if ($path[0] !== '/') {
            $path = ROOT . '/' . $path;
        }
        $secret = trim((string)file_get_contents($path));
        if (strlen($secret) < 32) {
            throw new \RuntimeException('Remote user icon upload secret must be at least 32 characters');
        }
        return $secret;
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
