<?php

namespace sammo;

class APIHelper
{
    private function __construct()
    {
        //static only
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
                $session = new DummySession();
            } else {
                if ($sessionMode & BaseAPI::REQ_GAME_LOGIN) {
                    $session = Session::requireGameLogin();
                } else if ($sessionMode & BaseAPI::REQ_LOGIN) {
                    $session = Session::requireLogin();
                } else {
                    $session = Session::getInstance();
                }

                if ($sessionMode & BaseAPI::REQ_READ_ONLY) {
                    $session->setReadOnly();
                }
            }

            $modifiedSince = WebUtil::parseLastModified();
            $reqEtags = WebUtil::parseETag();

            $result = $obj->launch($session, $modifiedSince, $reqEtags);
            if (is_string($result)) {
                Json::dieWithReason($result);
            }

            $cache = $obj->tryCache();
            $setCache = false;
            if ($cache !== null) {
                if($cache->lastModified !== null || $cache->etag !== null){
                    $setCache = true;
                    WebUtil::setCacheHeader($cache);
                }


                if ($modifiedSince !== null && $cache->lastModified !== null){
                    $lastModifiedUnixTime = Util::toInt(TimeUtil::DateTimeToSeconds($cache->lastModified, true));
                    $modifiedSinceUnixTime = Util::toInt(TimeUtil::DateTimeToSeconds($modifiedSince));
                    if($lastModifiedUnixTime === $modifiedSinceUnixTime){
                        WebUtil::dieWithNotModified();
                    }
                }
                if ($reqEtags !== null && $reqEtags === $cache->etag) {
                    WebUtil::dieWithNotModified();
                }
            }

            if ($result === null) {
                Json::die([
                    'result' => true,
                    'reason' => 'success'
                ], $setCache ? 0 : Json::NO_CACHE);
            }
            Json::die($result, $setCache ? 0 : Json::NO_CACHE);
        } catch (\Exception $e) {
            Json::dieWithReason($e->getMessage());
        } catch (\Throwable $e) {
            logExceptionByCustomHandler($e);
            Json::dieWithReason($e->getMessage());
        } catch (mixed $e) {
            Json::dieWithReason(strval($e));
        }
    }
}
