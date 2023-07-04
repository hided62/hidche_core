<?php

namespace sammo\Enums;

enum GeneralAccessLogColumn: string {
    case id = 'id';
    case generalID = 'general_id';
    case userID = 'user_id';
    case lastRefresh = 'last_refresh';
    case refresh = 'refresh';
    case refreshTotal = 'refresh_total';
}