<?php

namespace sammo\API\Command;

use sammo\Session;
use DateTimeInterface;
use sammo\Command\UserActionCommand;
use sammo\DB;
use sammo\DTO\UserAction;
use sammo\DTO\UserActionItem;
use sammo\Enums\APIRecoveryType;
use sammo\GameConst;
use sammo\General;
use sammo\Json;
use sammo\KVStorage;
use sammo\Util;
use sammo\Validator;

use function sammo\buildUserActionCommandClass;
use function sammo\setGeneralCommand;

class ReserveUserAction extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'turnIdx',
            'action'
        ])
            ->rule('int', 'turnIdx')
            ->rule('max', 'turnIdx', GameConst::$maxTurn)
            ->rule('min', 'turnIdx', 0)
            ->rule('lengthMin', 'action', 1);
        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $turnIdx = $this->args['turnIdx'];
        $action = $this->args['action'];

        $userActionKey = UserActionCommand::USER_ACTION_KEY;

        if($turnIdx < 0 || $turnIdx >= GameConst::$maxTurn){
            return '올바르지 않은 턴입니다.';
        }

        if(!in_array($action, Util::array_flatten(GameConst::$availableUserActionCommand))){
            return '사용할 수 없는 커맨드입니다.';
        }

        $db = DB::db();
        $generalID = $session->generalID;
        $rawUserActions = $db->queryFirstField('SELECT JSON_QUERY(`aux`, %s) FROM general WHERE no=%i', "$.{$userActionKey}", $generalID);
        if(!$rawUserActions){
            $rawUserActions = '{}';
        }

        $tmp = Json::decode($rawUserActions);
        $userActions = UserAction::fromArray($tmp);


        $gameStor = KVStorage::getStorage($db, 'game_env');
        $gameStor->cacheAll();
        $general = General::createObjFromDB($generalID);

        $item = buildUserActionCommandClass($action, $general, $gameStor->getAll());
        $userActions->reserved[$turnIdx] = new UserActionItem(
            $item->getRawClassName(),
            $item->getCommandDetailTitle(),
            null,
        );

        $db->update('general', [
            'aux' => $db->sqleval('JSON_SET(`aux`, %s, JSON_EXTRACT(%s, "$"))', "$.{$userActionKey}", Json::encode($userActions->toArray())),
        ], 'no=%i', $generalID);

        return [
            'result' => true,
        ];
    }
}
