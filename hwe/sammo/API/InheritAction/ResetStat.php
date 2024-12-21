<?php

namespace sammo\API\InheritAction;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\RankColumn;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\UniqueConst;
use sammo\UserLogger;
use sammo\Util;
use sammo\Validator;

class ResetStat extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'leadership',
            'strength',
            'intel',
        ])
            ->rule('int', [
                'leadership',
                'strength',
                'intel',
            ])
            ->rule('min', [
                'leadership',
                'strength',
                'intel'
            ], GameConst::$defaultStatMin)
            ->rule('max', [
                'leadership',
                'strength',
                'intel'
            ], GameConst::$defaultStatMax)
            ->rule('integerArray', 'inheritBonusStat');

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        //General.aux 쓰므로 lock;
        return static::REQ_GAME_LOGIN;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $userID = $session->userID;
        $generalID = $session->generalID;


        $leadership = $this->args['leadership'];
        $strength = $this->args['strength'];
        $intel = $this->args['intel'];
        $inheritBonusStat = $this->args['inheritBonusStat'] ?? null;

        if ($leadership + $strength + $intel != GameConst::$defaultStatTotal) {
            return "능력치 총합이 " . GameConst::$defaultStatTotal . "이 아닙니다. 다시 입력해주세요!";
        }

        if ($inheritBonusStat) {
            if (count($inheritBonusStat) != 3) {
                return "보너스 능력치가 잘못 지정되었습니다. 다시 입력해주세요!";
            }
            foreach ($inheritBonusStat as $stat) {
                if ($stat < 0) {
                    return "보너스 능력치가 음수입니다. 다시 입력해주세요!";
                }
            }
            $sum = array_sum($inheritBonusStat);
            if ($sum == 0) {
                $inheritBonusStat = null;
            } else if ($sum < 3 || $sum > 5) {
                return "보너스 능력치 합이 잘못 지정되었습니다. 다시 입력해주세요!";
            }
        }

        $general = General::createObjFromDB($generalID);
        if ($userID != $general->getVar('owner')) {
            return '로그인 상태가 이상합니다. 다시 로그인해 주세요.';
        }
        $userLogger = new UserLogger($userID);

        if($general->getNPCType() != 0){
            return 'NPC는 능력치 초기화를 할 수 없습니다.';
        }


        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $gameStor->cacheValues([
            'isunited',
            'season',
        ]);
        if ($gameStor->isunited) {
            return '이미 천하가 통일되었습니다.';
        }
        $userStor = KVStorage::getStorage($db, "user_{$userID}");

        $lastUserStatReset = $userStor->getValue('last_stat_reset') ?? [];
        $gameSeason = $gameStor->getValue('season');

        if (array_search($gameSeason, $lastUserStatReset) !== false) {
            return '이번 시즌에 이미 능력치를 초기화하셨습니다.';
        }


        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $previousPoint = ($inheritStor->getValue('previous') ?? [0, 0])[0];
        $reqAmount = 0;
        if ($inheritBonusStat !== null) {
            $reqAmount += GameConst::$inheritBornStatPoint;
        }

        if ($previousPoint < $reqAmount) {
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $userLogger->push("통솔 {$leadership}, 무력 {$strength}, 지력 {$intel} 스탯 재설정", "inheritPoint");

        if ($inheritBonusStat) {
            $pleadership = $inheritBonusStat[0] ?? 0;
            $pstrength = $inheritBonusStat[1] ?? 0;
            $pintel = $inheritBonusStat[2] ?? 0;
            $userLogger->push("{$reqAmount}로 통솔 {$pleadership}, 무력 {$pstrength}, 지력 {$pintel} 보너스 능력치 적용", "inheritPoint");
        } else {
            $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
                UniqueConst::$hiddenSeed,
                'ResetStat',
                $userID,
            )));
            $pleadership = 0;
            $pstrength = 0;
            $pintel = 0;
            foreach (Util::range($rng->nextRangeInt(3, 5)) as $statIdx) {
                switch ($rng->choiceUsingWeight([$leadership, $strength, $intel])) {
                    case 0:
                        $pleadership++;
                        break;
                    case 1:
                        $pstrength++;
                        break;
                    case 2:
                        $pintel++;
                        break;
                }
            }
            $userLogger->push("통솔 {$pleadership}, 무력 {$pstrength}, 지력 {$pintel} 보너스 능력치 적용", "inheritPoint");
        }

        $leadership += $pleadership;
        $strength += $pstrength;
        $intel += $pintel;

        $lastUserStatReset[] = $gameSeason;


        $general->setVar('leadership', $leadership);
        $general->setVar('strength', $strength);
        $general->setVar('intel', $intel);

        $userLogger->flush();

        $inheritStor->setValue('previous', [$previousPoint - $reqAmount, null]);
        $userStor->setValue('last_stat_reset', $lastUserStatReset);
        $general->increaseRankVar(RankColumn::inherit_point_spent_dynamic, $reqAmount);
        $general->applyDB($db);
        return null;
    }
}
