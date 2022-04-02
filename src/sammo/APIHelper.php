<?php

namespace sammo;

class APIHelper
{
    private function __construct()
    {
        //static only
    }

    private static function setCacheHeader(){
        header('cache-control: private, max-age=60');
        header("Pragma: cache");
        header_remove('expires');
    }

    private static function DieWithNotModified(): never{
        static::setCacheHeader();
        header("HTTP/1.1 304 Not Modified");
        die();
    }

    public static function launch(string $rootPath, string $actionPath, array $eParams = [], bool $loadRawInput = true)
    {
        if($loadRawInput){
            try {
                $rawInput = file_get_contents('php://input');
                $input = Json::decode($rawInput);
            } catch (\Exception $e) {
                Json::dieWithReason($e->getMessage());
                $input = null;
            }
        }
        else{
            $input = null;
        }

        if(!$actionPath){
            Json::dieWithReason('path가 지정되지 않았습니다.');
        }
        if ($input && !is_array($input)) {
            Json::dieWithReason('args가 array가 아닙니다.' . gettype($input));
        }
        if (!$input) {
            $input = [];
        }

        //NOTE: array_merge([], {})의 상황이 가능함.
        $actionArgs = array_merge($input, $eParams);

        try {
            $obj = buildAPIExecutorClass($actionPath, $rootPath, $actionArgs);
            $validateResult = $obj->validateArgs();
            if ($validateResult !== null) {
                Json::dieWithReason($validateResult);
            }

            $sessionMode = $obj->getRequiredSessionMode();
            if ($sessionMode === BaseAPI::NO_SESSION) {
                $session = Session::getInstance(); //XXX: NoSession이면 진짜 NoSession이어야..?
            } else {
                if ($sessionMode & BaseAPI::REQ_GAME_LOGIN) {
                    $session = Session::requireGameLogin();
                } else if ($sessionMode & BaseAPI::REQ_LOGIN) {
                    $session = Session::requireLogin();
                } else {
                    Json::dieWithReason("올바르지 않은 SessionMode: {$sessionMode}");
                }

                if ($sessionMode & BaseAPI::REQ_READ_ONLY) {
                    $session->setReadOnly();
                }
            }

            $modifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
                ? new \DateTimeImmutable($_SERVER['HTTP_IF_MODIFIED_SINCE'], new \DateTimeZone("UTC"))
                : null;
            $reqEtags = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : null;

            $result = $obj->launch($session, $modifiedSince, $reqEtags);
            if (is_string($result)) {
                Json::dieWithReason($result);
            }

            $cacheResult = $obj->tryCache();
            $setCache = false;
            if ($cacheResult !== null) {
                $lastModified = $cacheResult->lastModified;
                $etag = $cacheResult->etag;

                if ($lastModified !== null) {
                    header("Last-Modified: " . gmdate("D, d M Y H:i:s", Util::toInt(TimeUtil::DateTimeToSeconds($lastModified, true))) . " GMT");
                    $setCache = true;
                }
                if ($etag !== null) {
                    header("Etag: $etag");
                    $setCache = true;
                }

                if ($modifiedSince !== null && $lastModified !== null && TimeUtil::DateIntervalToSeconds($modifiedSince->diff($lastModified)) == 0) {
                    static::DieWithNotModified();
                }
                if ($reqEtags !== null && $reqEtags === $etag) {
                    static::DieWithNotModified();
                }
            }

            if ($result === null) {
                if ($setCache) {
                    static::setCacheHeader();
                }
                Json::die([
                    'result' => true,
                    'reason' => 'success'
                ], $setCache ? 0 : Json::NO_CACHE);
            }
            if ($setCache) {
                static::setCacheHeader();
            }
            Json::die($result, $setCache ? 0 : Json::NO_CACHE);
        } catch (\Throwable $e) {
            Json::dieWithReason($e->getMessage());
        } catch (mixed $e) {
            Json::dieWithReason(strval($e));
        }
    }
}
