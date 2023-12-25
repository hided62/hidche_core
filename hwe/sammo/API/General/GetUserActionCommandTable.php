<?php

namespace sammo\API\General;

use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\GameConst;
use sammo\General;
use sammo\Session;
use sammo\KVStorage;
use sammo\Util;

use function sammo\buildUserActionCommandClass;

class GetUserActionCommandTable extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $generalID = $session->generalID;
        $general = General::createObjFromDB($generalID);

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $gameStor->turnOnCache();
        $env = $gameStor->getAll();

        $commandTable = [];
        foreach (GameConst::$availableUserActionCommand as $commandCategory => $commandList) {
            $subList = [];
            foreach ($commandList as $commandClassName) {
                $commandObj = buildUserActionCommandClass($commandClassName, $general, $env);
                if (!$commandObj->canDisplay()) {
                    continue;
                }
                $subList[] = [
                    'value' => Util::getClassNameFromObj($commandObj),
                    'compensation' => $commandObj->getCompensationStyle(),
                    'possible' => $commandObj->hasMinConditionMet(),
                    'title' => $commandObj->getCommandDetailTitle(),
                    'simpleName' => $commandObj->getName(),
                    'reqArg' => $commandObj::$reqArg,
                ];
            }

            $commandTable[] = [
                'category' => $commandCategory,
                'values' => $subList
            ];
        }

        return [
            'result' => true,
            'commandTable' => $commandTable,
        ];
    }
}