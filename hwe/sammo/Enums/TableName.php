<?php
namespace sammo\Enums;

enum TableName: string {
    case general = 'general';
    case generalTurn = 'general_turn';
    case generalAccessLog = 'general_access_log';

    case userRecord = 'user_record';

    case nation = 'nation';
    case nationTurn = 'nation_turn';
    case nationEnv = 'nation_env';

    case board = 'board';
    case comment = 'comment';

    case city = 'city';

    case troop = 'troop';

    case plock = 'plock';

    case message = 'message';

    case rankData = 'rank_data';
    case hall = 'hall';

    case oldNations = 'ng_old_nations';
    case oldGenerals = 'ng_old_generals';

    case emperior = 'emperior';

    case diplomacy = 'diplomacy';
    case diplomaticNotes = 'ng_diplomacy';

    case tournament = 'tournament';
    case betting = 'ng_betting';
    case vote = 'vote';
    case voteComment = 'vote_comment';

    case auction = 'ng_auction';
    case auctionBid = 'ng_auction_bid';

    case statistic = 'statistic';

    case history = 'ng_history';
    case worldHistory = 'world_history';
    case generalRecord = 'general_record';

    case event = 'event';
    case storage = 'storage';


    case selectNPCToken = 'select_npc_token';
    case selectPool = 'select_pool';

    case games = 'ng_games';
    case reservedOpen = 'reserved_open';
}