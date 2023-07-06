<?php

namespace sammo\Enums;

enum GeneralAccessLogColumn: string {
    case id = 'id';
    case generalID = 'general_id';
    case userID = 'user_id';
    case lastRefresh = 'last_refresh';
    case refresh = 'refresh'; //순간 갱신 횟수(00:00에 초기화)
    case refreshTotal = 'refresh_total'; //누적 갱신 횟수
    case refreshScore = 'refresh_score'; //순간 벌점(턴 시간에 초기화)
    case refreshScoreTotal = 'refresh_score_total'; //누적 벌점(지속적으로 감소, refreshScoreTotal <= refreshTotal)
}