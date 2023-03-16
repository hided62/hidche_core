<?php

namespace sammo\API\Global;

use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Json;
use sammo\KVStorage;
use sammo\RootDB;
use sammo\Session;

use function sammo\calcLeadershipBonus;
use function sammo\checkLimit;
use function sammo\getDed;
use function sammo\getExpLevel;
use function sammo\getGenChar;
use function sammo\getGeneralSpecialDomesticName;
use function sammo\getGeneralSpecialWarName;
use function sammo\getHonor;
use function sammo\getNationStaticInfo;
use function sammo\getOfficerLevelText;
use function sammo\increaseRefresh;

class GeneralList extends \sammo\BaseAPI
{
    static $withToken = false;
    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_LOGIN;
    }

    public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        $userID = $session->userID;
        if ($session->isGameLoggedIn()) {
            increaseRefresh("장수일람", 2);
            $me = $db->queryFirstRow('SELECT con, turntime FROM general WHERE owner=%i', $userID);
            $con = checkLimit($me['con']);
            if ($con >= 2) {
                return '접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다.';
            }
        } else {
            $availableNextCall = $session->availableNextCallGetGeneralList ?? '2000-01-01 00:00:00';
            $now = new \DateTimeImmutable();

            if ($now <= new \DateTimeImmutable($availableNextCall) && $session->userGrade < 5) {
                return "장수 리스트는 10초에 한번 갱신 가능합니다.\n다음 시간 : " . $availableNextCall;
            }

            $availableNextCall = $now->add(new \DateInterval('PT10S'))->format('Y-m-d H:i:s');
            $session->availableNextCallGetGeneralList = $availableNextCall;
        }

        $session->setReadOnly();


        $rawGeneralList = $db->queryAllLists('SELECT owner,no,picture,imgsvr,npc,age,nation,special,special2,personal,name,owner_name as ownerName,injury,leadership,strength,intel,experience,dedication,officer_level,killturn,connect from general');

        $ownerNameList = [];
        if ($gameStor->isunited) {
            foreach (RootDB::db()->queryAllLists('SELECT no, name FROM member') as [$ownerID, $ownerName]) {
                $ownerNameList[$ownerID] = $ownerName;
            }
        }

        $generalList = [];
        foreach ($rawGeneralList as $rawGeneral) {
            [$owner, $no, $picture, $imgsvr, $npc, $age, $nation, $special, $special2, $personal, $name, $ownerName, $injury, $leadership, $strength, $intel, $experience, $dedication, $officerLevel, $killturn, $connectCnt] = $rawGeneral;

            if (key_exists($owner, $ownerNameList)) {
                $ownerName = $ownerNameList[$owner];
            }

            $nationArr = getNationStaticInfo($nation);
            $lbonus = calcLeadershipBonus($officerLevel, $nationArr['level']);

            $generalList[] = [
                $no,
                $picture,
                $imgsvr,
                $npc,
                $age,
                $nationArr['name'],
                getGeneralSpecialDomesticName($special),
                getGeneralSpecialWarName($special2),
                getGenChar($personal),
                $name,
                $npc == 1 ? $ownerName : null,
                $injury,
                $leadership,
                $lbonus,
                $strength,
                $intel,
                getExpLevel($experience),
                getHonor($experience),
                getDed($dedication),
                getOfficerLevelText($officerLevel, $nationArr['level']),
                $killturn,
                $connectCnt
            ];
        }

        $resultColumns = [
            'no',
            'picture',
            'imgsvr',
            'npc',
            'age',
            'nationName',
            'special',
            'special2',
            'personal',
            'name',
            'ownerName',
            'injury',
            'leadership',
            'lbonus',
            'strength',
            'intel',
            'explevel',
            'honorText',
            'dedLevelText',
            'officerLevelText',
        ];

        $result = [
            'result' => 'true',
            'column' => $resultColumns,
            'list' => $generalList,
        ];


        if (static::$withToken) {
            $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $tokens = [];
            foreach ($db->query('SELECT * FROM select_npc_token WHERE `valid_until`>=%s', $now) as $token) {
                $validUntil = $token['valid_until'];

                foreach (Json::decode($token['pick_result']) as $pickResult) {
                    $tokens[$pickResult['no']] = $pickResult['keepCnt'];
                }
            }
            $result['token'] = $tokens;
        }
        return $result;
    }
}
