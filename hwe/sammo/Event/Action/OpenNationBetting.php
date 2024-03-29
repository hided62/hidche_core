<?php

namespace sammo\Event\Action;

use DateTime;
use sammo\Betting;
use \sammo\GameConst;
use \sammo\Util;
use \sammo\DB;
use sammo\DTO\BettingInfo;
use sammo\DTO\SelectItem;
use sammo\Enums\MessageType;
use sammo\Json;
use sammo\KVStorage;
use sammo\Message;
use sammo\MessageTarget;

use function sammo\getAllNationStaticInfo;
use function sammo\GetImageURL;
use function sammo\getNationStaticInfo;

class OpenNationBetting extends \sammo\Event\Action
{
    public function __construct(private int $nationCnt = 1, private int $bonusPoint = 0)
    {
        if ($nationCnt < 1) {
            throw new \RuntimeException("1 미만의 숫자");
        }
        if ($bonusPoint < 0) {
            throw new \RuntimeException("0 미만의 보너스 포인트");
        }
    }

    public function run(array $env)
    {
        $db = DB::db();

        [$year, $month] = [$env['year'], $env['month']];

        if ($this->nationCnt == 1) {
            $name = "천통국";
        } else {
            $name = "최후 {$this->nationCnt}국";
        }


        $openYearMonth = Util::joinYearMonth($year, $month);
        $closeYearMonth = $openYearMonth + 24;

        $citiesCnt = [];
        foreach ($db->queryAllLists('SELECT nation, COUNT(*) AS cnt FROM city WHERE nation > 0 GROUP BY nation') as [$nationID, $cnt]) {
            $citiesCnt[$nationID] = $cnt;
        }

        /** @var \sammo\DTO\SelectItem[] */
        $candidates = [];

        $nations = getAllNationStaticInfo();
        uasort($nations, function ($lhs, $rhs) {
            return - ($lhs['power'] <=> $rhs['power']);
        });

        foreach ($nations as $nationRaw) {
            $nationID = $nationRaw['nation'];
            $cityCnt = $citiesCnt[$nationID] ?? 0;
            $nationRaw['city_cnt'] = $cityCnt;
            $info = [
                "국력: {$nationRaw['power']}",
                "장수 수: {$nationRaw['gennum']}",
                "도시 수: {$cityCnt}",
            ];

            //NOTE: 도시 정보도 추가?
            $candidates[] = new SelectItem(
                title: $nationRaw['name'],
                info: join("<br>", $info),
                isHtml: true,
                aux: $nationRaw,
            );
        }

        $bettingID = \sammo\Betting::genNextBettingID();
        Betting::openBetting(new BettingInfo(
            id: $bettingID,
            type: 'bettingNation',
            name: "{$name} 예상",
            finished: false,
            selectCnt: $this->nationCnt,
            isExclusive: null,
            reqInheritancePoint: true,
            openYearMonth: $openYearMonth,
            closeYearMonth: $closeYearMonth,
            candidates: $candidates,
            winner: null,
        ));

        $db->insert('event', [
            'target' => 'DESTROY_NATION',
            'priority' => 1000,
            'condition' => Json::encode(
                ["RemainNation", "<=", $this->nationCnt],
            ),
            'action' => Json::encode([
                ["FinishNationBetting", $bettingID],
                ["DeleteEvent"],
            ]),
        ]);

        if ($this->bonusPoint > 0) {
            $db->insert('ng_betting', [
                'betting_id' => $bettingID,
                'general_id' => 0,
                'betting_type' => '[-1]',
                'amount' => $this->bonusPoint
            ]);
        }

        $logger = new \sammo\ActionLogger(0, 0, $year, $month);
        if ($this->nationCnt > 1) {
            $logger->pushGlobalHistoryLog("<B><b>【내기】</b></>중원의 강자를 점치는 <C>내기</>가 진행중입니다! 호사가의 참여를 기다립니다!");
        } else {
            $logger->pushGlobalHistoryLog("<B><b>【내기】</b></>천하통일 후보를 점치는 <C>내기</>가 진행중입니다! 호사가의 참여를 기다립니다!");
        }

        $logger->flush();
        $now = new DateTime();
        $text = "새로운 {$name} 내기가 열렸습니다. 천통국 베팅란을 확인해주세요.";

        $src = new MessageTarget(0, '', 0, 'System', '#000000');
        foreach ($db->query('SELECT `no`, `name`, `nation`, `imgsvr`, `picture` FROM `general` WHERE `npc` <= 1') as $general) {
            $generalID = $general['no'];
            $staticNation = getNationStaticInfo($general['nation']);
            $dest = new MessageTarget(
                $generalID,
                $general['name'],
                $general['nation'],
                $staticNation['name'],
                $staticNation['color'],
                GetImageURL($general['imgsvr'], $general['picture'])
            );
            $msg = new Message(
                MessageType::private,
                $src,
                $dest,
                $text,
                $now,
                new DateTime('9999-12-31'),
                []
            );
            $msg->send(true);
        }

        return [__CLASS__, true];
    }
}
