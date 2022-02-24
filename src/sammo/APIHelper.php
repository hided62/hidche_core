<?php

namespace sammo;

class APIHelper
{
    private function __construct()
    {
        //static only
    }

    public static function launch(string $rootPath, ?string $actionPath = null)
    {
        //TODO: path를 php://input에서 받는게 아니라 api.php?~~~~~ 로 받아오는것이 etag 캐시 측면에서 훨씬 나을 듯!
        try {
            $rawInput = file_get_contents('php://input');
            $input = Json::decode($rawInput);
        } catch (\Exception $e) {
            Json::dieWithReason($e->getMessage());
        }

        if($actionPath !== null){
            if ($input && !is_array($input)) {
                Json::dieWithReason('args가 array가 아닙니다.' . gettype($input));
            }
            if(!$input){
                $input = [];
            }
            $actionArgs = $input;
        }
        else{
            if(!$input){
                Json::dieWithReason("input이 비어있습니다. {$rawInput}");
            }

            if (!key_exists('path', $input)) {
                Json::dieWithReason('path가 지정되지 않았습니다.');
            }
            $actionPath = $input['path'];

            if (key_exists('args', $input) && !is_array($input['args'])) {
                Json::dieWithReason('args가 array가 아닙니다.' . gettype($input['args']));
            }
            $actionArgs = $input['args'] ?? null;
        }



        try {
            $obj = buildAPIExecutorClass($actionPath, $rootPath, $actionArgs);
            $validateResult = $obj->validateArgs();
            if ($validateResult !== null) {
                Json::dieWithReason($validateResult);
            }

            $sessionMode = $obj->getRequiredSessionMode();
            if ($sessionMode === BaseAPI::NO_SESSION) {
                $session = Session::getInstance();//XXX: NoSession이면 진짜 NoSession이어야..?
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
            if ($cacheResult !== null) {
                /** @var \DateTimeInterface $lastModified  */
                [$lastModified, $etag] = $cacheResult;

                if ($lastModified !== null) {
                    header("Last-Modified: " . gmdate("D, d M Y H:i:s", Util::toInt(TimeUtil::DateTimeToSeconds($lastModified, true))) . " GMT");
                }
                if ($etag !== null) {
                    header("Etag: $etag");
                }

                if ($modifiedSince !== null && $lastModified !== null && TimeUtil::DateIntervalToSeconds($modifiedSince->diff($lastModified)) == 0) {
                    header("HTTP/1.1 304 Not Modified");
                    die();
                }
                if ($reqEtags !== null && $reqEtags === $etag) {
                    header("HTTP/1.1 304 Not Modified");
                    die();
                }
            }

            if ($result === null) {
                Json::die([
                    'result' => true,
                    'reason' => 'success'
                ], $cacheResult === null ? Json::NO_CACHE : 0);
            }
            Json::die($result, $cacheResult === null ? Json::NO_CACHE : 0);
        } catch (\Throwable $e) {
            Json::dieWithReason($e->getMessage());
        } catch (mixed $e) {
            Json::dieWithReason(strval($e));
        }
    }
}
