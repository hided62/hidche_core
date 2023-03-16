<?php
namespace sammo\API\General;

use sammo\Enums\APIRecoveryType;
use sammo\General;
use sammo\Session;

use function sammo\getCommandTable;

//getCommandTable 호출을 대신하는 API

class GetCommandTable extends \sammo\BaseAPI{
    public function validateArgs(): ?string{
        return null;
    }

    public function getRequiredSessionMode(): int{
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $generalID = $session->generalID;
        $me = General::createGeneralObjFromDB($generalID);
        $commandTable = getCommandTable($me);

        return [
            'result'=>true,
            'commandTable' => $commandTable,
        ];
    }
}