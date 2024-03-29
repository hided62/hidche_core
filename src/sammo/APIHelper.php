<?php

namespace sammo;

use sammo\Enums\APIRecoveryType;

class APIHelper
{

    private function __construct()
    {
        //static only
    }

    public static function getSession(int $sessionMode): Session | APIRecoveryType
    {
        if ($sessionMode === BaseAPI::NO_SESSION) {
            return DummySession::getInstance();
        }

        $session = Session::getInstance();
        if($sessionMode & BaseAPI::REQ_LOGIN || $sessionMode & BaseAPI::REQ_GAME_LOGIN){
            if(!$session->isLoggedIn()){
                return APIRecoveryType::Login;
            }
        }

        if($sessionMode & BaseAPI::REQ_GAME_LOGIN){
            if(!$session->isGameLoggedIn()){
                $tmpResult = false;
                $session->loginGame($tmpResult);
                if(!$tmpResult){
                    return APIRecoveryType::Gateway;
                }
            }
        }

        if ($sessionMode & BaseAPI::REQ_READ_ONLY) {
            $session->setReadOnly();
        }
        return $session;
    }

    public static function launch(string $rootPath, string $actionPath, array $eParams = [], bool $loadRawInput = true)
    {
        if ($loadRawInput) {
            try {
                $rawInput = file_get_contents('php://input');
                $input = Json::decode($rawInput);
            } catch (\Exception $e) {
                Json::dieWithReason($e->getMessage());
                $input = null;
            }
        } else {
            $input = null;
        }

        if (!$actionPath) {
            Json::dieWithReason('path가 지정되지 않았습니다.');
        }
        if ($input && !is_array($input)) {
            Json::dieWithReason('args가 array가 아닙니다.' . gettype($input));
        }
        if (!$input) {
            $input = [];
        }

        if (class_exists('\\sammo\\UniqueConst')) {
            $serverID = UniqueConst::$serverID;
            $logPath = "{$rootPath}/logs/{$serverID}/api_log.db";
        } else {
            $realYearMonth = date('Y_m');
            $logPath = "{$rootPath}/d_log/{$realYearMonth}_api_log.db";
        }
        $logDB = FileDB::db($logPath, __DIR__ . '/../../f_install/sql/api_log.sql');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'local';
        $date = date('Y-m-d H:i:s');

        //NOTE: array_merge([], {})의 상황이 가능함.
        $actionArgs = array_merge($input, $eParams);

        $filteredArgs = $actionArgs;

        try {
            $obj = buildAPIExecutorClass($actionPath, $rootPath, $actionArgs);
            if (!$obj::$allowExternalAPI) {
                Json::dieWithReason('외부에서는 호출할 수 없습니다.');
            }
            $validateResult = $obj->validateArgs();
            if ($validateResult !== null) {
                $logDB->insert('api_log', [
                    'user_id' => 0,
                    'ip' => $ip,
                    'date' => $date,
                    'path' => $actionPath,
                    'arg' => JSON::encode($filteredArgs),
                    'aux' => JSON::encode([
                        'result' => false,
                        'state' => 'validate',
                        'reason' => $validateResult
                    ]),
                ]);
                Json::dieWithReason($validateResult);
            }
            $filteredArgs = $obj->getFilteredArgs();

            $sessionMode = $obj->getRequiredSessionMode();
            $sessionOrFail = static::getSession($sessionMode);

            if(!($sessionOrFail instanceof Session)){
                $recoveryType = $sessionOrFail;
                Json::dieWithReason($recoveryType->info(), $recoveryType);
            }
            $session = $sessionOrFail;

            $modifiedSince = WebUtil::parseLastModified();
            $reqEtags = WebUtil::parseETag();

            $result = $obj->launch($session, $modifiedSince, $reqEtags);
            if (is_string($result)) {
                $logDB->insert('api_log', [
                    'user_id' => $session->userID ?? 0,
                    'ip' => $ip,
                    'date' => $date,
                    'path' => $actionPath,
                    'arg' => JSON::encode($filteredArgs),
                    'aux' => JSON::encode([
                        'result' => false,
                        'state' => 'launch',
                        'reason' => $result
                    ]),
                ]);
                Json::dieWithReason($result);
            }
            else if($result instanceof APIRecoveryType){
                $recoveryType = $result;
                $logDB->insert('api_log', [
                    'user_id' => $session->userID ?? 0,
                    'ip' => $ip,
                    'date' => $date,
                    'path' => $actionPath,
                    'arg' => JSON::encode($filteredArgs),
                    'aux' => JSON::encode([
                        'result' => false,
                        'state' => 'launch',
                        'reason' => $recoveryType->info(),
                        'recovery' => $recoveryType->value,
                    ]),
                ]);
                Json::dieWithReason($recoveryType->info(), $recoveryType);
            }

            $cache = $obj->tryCache();
            $setCache = false;
            if ($cache !== null) {
                if ($cache->lastModified !== null || $cache->etag !== null) {
                    $setCache = true;
                    WebUtil::setCacheHeader($cache);
                }


                if ($modifiedSince !== null && $cache->lastModified !== null) {
                    $lastModifiedUnixTime = Util::toInt(TimeUtil::DateTimeToSeconds($cache->lastModified, true));
                    $modifiedSinceUnixTime = Util::toInt(TimeUtil::DateTimeToSeconds($modifiedSince));
                    if ($lastModifiedUnixTime === $modifiedSinceUnixTime) {
                        $logDB->insert('api_log', [
                            'user_id' => $session->userID ?? 0,
                            'ip' => $ip,
                            'date' => $date,
                            'path' => $actionPath,
                            'arg' => JSON::encode($filteredArgs),
                            'aux' => JSON::encode([
                                'result' => true,
                                'state' => 'cache_not_modified',
                            ]),
                        ]);
                        WebUtil::dieWithNotModified();
                    }
                }
                if ($reqEtags !== null && $reqEtags === $cache->etag) {
                    $logDB->insert('api_log', [
                        'user_id' => $session->userID ?? 0,
                        'ip' => $ip,
                        'date' => $date,
                        'path' => $actionPath,
                        'arg' => JSON::encode($filteredArgs),
                        'aux' => JSON::encode([
                            'result' => true,
                            'state' => 'cache_not_modified',
                        ]),
                    ]);
                    WebUtil::dieWithNotModified();
                }
            }

            if ($result === null) {
                $logDB->insert('api_log', [
                    'user_id' => $session->userID ?? 0,
                    'ip' => $ip,
                    'date' => $date,
                    'path' => $actionPath,
                    'arg' => JSON::encode($filteredArgs),
                    'aux' => JSON::encode([
                        'result' => true,
                        'state' => 'success_simple',
                        'set_cache' => $setCache,
                    ]),
                ]);
                Json::die([
                    'result' => true,
                    'reason' => 'success'
                ], $setCache ? 0 : Json::NO_CACHE);
            }
            $logDB->insert('api_log', [
                'user_id' => $session->userID ?? 0,
                'ip' => $ip,
                'date' => $date,
                'path' => $actionPath,
                'arg' => JSON::encode($filteredArgs),
                'aux' => JSON::encode([
                    'result' => true,
                    'state' => 'success_complex',
                    'set_cache' => $setCache,
                ]),
            ]);
            $result['result'] = $result['result'] ?? true;
            Json::die($result, $setCache ? 0 : Json::NO_CACHE);
        } catch (\Exception $e) {
            $errMsg = $e->getMessage();
            $errTrace = $e->getTraceAsString();
            $logDB->insert('api_log', [
                'user_id' => $session->userID ?? 0,
                'ip' => $ip,
                'date' => $date,
                'path' => $actionPath,
                'arg' => JSON::encode($filteredArgs),
                'aux' => JSON::encode([
                    'result' => false,
                    'state' => 'error_exception',
                    'errMsg' => $errMsg,
                    'errTrace' => $errTrace,
                ]),
            ]);
            Json::dieWithReason("{$errMsg}\n{$errTrace}");
        } catch (\Throwable $e) {
            logExceptionByCustomHandler($e, false);
            $errMsg = $e->getMessage();
            $errTrace = $e->getTraceAsString();
            $logDB->insert('api_log', [
                'user_id' => $session->userID ?? 0,
                'ip' => $ip,
                'date' => $date,
                'path' => $actionPath,
                'arg' => JSON::encode($filteredArgs),
                'aux' => JSON::encode([
                    'result' => false,
                    'state' => 'error_throwable',
                    'errMsg' => $errMsg,
                    'errTrace' => $errTrace,
                ]),
            ]);
            Json::dieWithReason("{$errMsg}\n{$errTrace}");
        } catch (mixed $e) {
            $errStr = strval($e);
            $logDB->insert('api_log', [
                'user_id' => $session->userID ?? 0,
                'ip' => $ip,
                'date' => $date,
                'path' => $actionPath,
                'arg' => JSON::encode($filteredArgs),
                'aux' => JSON::encode([
                    'result' => false,
                    'state' => 'error_mixed',
                    'errMsg' => $errStr,
                ]),
            ]);
            Json::dieWithReason($errStr);
        }
    }
}
