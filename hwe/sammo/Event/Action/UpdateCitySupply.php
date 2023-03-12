<?php

namespace sammo\Event\Action;

use sammo\ActionLogger;
use sammo\CityConst;
use sammo\DB;
use sammo\JosaUtil;
use sammo\Util;

class UpdateCitySupply extends \sammo\Event\Action
{
    public function run(array $env)
    {
        $db = DB::db();

        $cities = [];
        foreach ($db->query('SELECT city, nation FROM city WHERE nation != 0') as $city) {
            $newCity = new \stdClass();
            $newCity->id = Util::toInt($city['city']);
            $newCity->nation = Util::toInt($city['nation']);
            $newCity->supply = false;

            $cities[$newCity->id] = $newCity;
        }

        $queue = new \SplQueue();
        foreach ($db->queryAllLists('SELECT capital, nation FROM nation WHERE `level` > 0') as list($capitalID, $nationID)) {
            if (!key_exists($capitalID, $cities)) {
                continue;
            }
            $city = $cities[$capitalID];
            if ($nationID != $city->nation) {
                continue;
            }
            $city->supply = true;
            $queue->enqueue($city);
        }

        while (!$queue->isEmpty()) {
            $cityLink = $queue->dequeue();
            $city = CityConst::byID($cityLink->id);

            foreach (array_keys($city->path) as $connCityID) {
                if (!key_exists($connCityID, $cities)) {
                    continue;
                }
                $connCity = $cities[$connCityID];
                if ($connCity->nation != $cityLink->nation) {
                    continue;
                }
                if ($connCity->supply) {
                    continue;
                }
                $connCity->supply = true;
                $queue->enqueue($connCity);
            }
        }

        $db->update('city', [
            'supply' => 1
        ], 'nation=0');

        $db->update('city', [
            'supply' => 0
        ], 'nation!=0');

        $supply = [];

        foreach ($cities as $city) {
            if ($city->supply) {
                $supply[] = $city->id;
            }
        }

        if ($supply) {
            $db->update('city', [
                'supply' => 1
            ], 'city IN %li', $supply);
        }

        //미보급도시 10% 감소
        $db->update('city', [
            'pop' => $db->sqleval('pop * 0.9'),
            'trust' => $db->sqleval('trust * 0.9'),
            'agri' => $db->sqleval('agri * 0.9'),
            'comm' => $db->sqleval('comm * 0.9'),
            'secu' => $db->sqleval('secu * 0.9'),
            'def' => $db->sqleval('def * 0.9'),
            'wall' => $db->sqleval('wall * 0.9'),
        ], 'supply = 0');
        //미보급도시 장수 병 훈 사 5%감소
        //NOTE: update inner join도 가능하지만, meekrodb 기준으로 깔끔하게.
        $unsuppliedCities = $db->query('SELECT city, nation, trust, name FROM city WHERE supply = 0');
        foreach (Util::arrayGroupBy($unsuppliedCities, 'nation') as $nationID => $cityList) {
            $cityIDList = Util::squeezeFromArray($cityList, 'city');
            $db->update('general', [
                'crew' => $db->sqleval('crew*0.95'),
                'atmos' => $db->sqleval('atmos*0.95'),
                'train' => $db->sqleval('train*0.95'),
            ], 'city IN %li AND nation = %i', $cityIDList, $nationID);
        }

        //민심30이하 공백지 처리
        $lostCities = [];
        foreach ($unsuppliedCities as $unsuppliedCity) {
            if ($unsuppliedCity['trust'] >= 30) {
                continue;
            }
            $lostCities[$unsuppliedCity['city']] = $unsuppliedCity;
        }

        $logger = new ActionLogger(0, 0, $env['year'], $env['month']);

        if ($lostCities) {
            foreach ($lostCities as $lostCity) {
                $josaYi = JosaUtil::pick($lostCity['name'], '이');
                $logger->pushGlobalHistoryLog("<R><b>【고립】</b></><G><b>{$lostCity['name']}</b></>{$josaYi} 보급이 끊겨 <R>미지배</> 도시가 되었습니다.");
            }
            $db->update('general', [
                'officer_level' => 1,
                'officer_city' => 0
            ], 'officer_city IN %li', array_keys($lostCities));
            $db->update('city', [
                'nation' => 0,
                'officer_set' => 0,
                'conflict' => '{}',
                'term' => 0,
                'front' => 0
            ], 'city IN %li', array_keys($lostCities));
        }
    }
}
