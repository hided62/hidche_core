import type { Args } from "./processing/args";
import {
  callSammoAPI,
  extractHttpMethod,
  GET,
  PATCH,
  POST,
  PUT,
  type APITail,
  type APICallT,
  type RawArgType,
  type ValidResponse,
  type InvalidResponse,
} from "./util/callSammoAPI";
export type { ValidResponse, InvalidResponse };
import { APIPathGen, NumVar, StrVar } from "./util/APIPathGen.js";
import type { BettingDetailResponse, BettingListResponse } from "./defs/API/Betting";
import type { ReserveBulkCommandResponse, ReserveCommandResponse, ReservedCommandResponse } from "./defs/API/Command";
import type { ChiefResponse } from "./defs/API/NationCommand";
import type { inheritBuffType, InheritLogResponse } from "./defs/API/InheritAction";
import type { SetBlockWarResponse, GeneralListResponse as NationGeneralListResponse, NationInfoResponse } from "./defs/API/Nation";
import type { UploadImageResponse } from "./defs/API/Misc";
import type { GeneralLogType, GetGeneralLogResponse, JoinArgs } from "./defs/API/General";
import type {
  ExecuteResponse,
  GetConstResponse,
  GetCurrentHistoryResponse,
  GetDiplomacyResponse,
  GetFrontInfoResponse,
  GetHistoryResponse,
  GetMenuResponse,
GetRecentRecordResponse,
} from "./defs/API/Global";
import type { CachedMapResult, CommandTableResponse, GeneralListResponse, ItemTypeKey, MapResult } from "./defs";
import type { VoteDetailResult, VoteListResult } from "./defs/API/Vote";
import type { ActiveResourceAuctionList, OpenAuctionResponse, UniqueItemAuctionDetail, UniqueItemAuctionList } from "./defs/API/Auction";
import type { MabilboxListResponse, MsgResponse, MsgType } from "./defs/API/Message";

const apiRealPath = {
  Auction: {
    BidBuyRiceAuction: PUT as APICallT<{
      auctionID: number;
      amount: number;
    }>,
    BidSellRiceAuction: PUT as APICallT<{
      auctionID: number;
      amount: number;
    }>,
    GetActiveResourceAuctionList: GET as APICallT<undefined, ActiveResourceAuctionList>,
    OpenBuyRiceAuction: POST as APICallT<
      {
        amount: number;
        closeTurnCnt: number;
        startBidAmount: number;
        finishBidAmount: number;
      },
      OpenAuctionResponse
    >,
    OpenSellRiceAuction: POST as APICallT<
      {
        amount: number;
        closeTurnCnt: number;
        startBidAmount: number;
        finishBidAmount: number;
      },
      OpenAuctionResponse
    >,

    BidUniqueAuction: PUT as APICallT<{
      auctionID: number;
      amount: number;
    }>,
    GetUniqueItemAuctionDetail: GET as APICallT<{
        auctionID: number;
    }, UniqueItemAuctionDetail>,
    GetUniqueItemAuctionList: GET as APICallT<undefined, UniqueItemAuctionList>,
    OpenUniqueAuction: POST as APICallT<{
        itemID: string,
        amount: number,
    }, OpenAuctionResponse>,
  },
  Betting: {
    Bet: PUT as APICallT<{
      bettingID: number;
      bettingType: number[];
      amount: number;
    }>,
    GetBettingDetail: NumVar("betting_id", GET as APICallT<undefined, BettingDetailResponse>),
    GetBettingList: GET as APICallT<
      {
        req?: "bettingNation" | "tournament";
      },
      BettingListResponse
    >,
  },
  Command: {
    GetReservedCommand: GET as APICallT<undefined, ReservedCommandResponse>,
    PushCommand: PUT as APICallT<{
      amount: number;
    }>,
    RepeatCommand: PUT as APICallT<{
      amount: number;
    }>,
    ReserveCommand: PUT as APICallT<
      {
        turnList: number[];
        action: string;
        arg?: Args;
      },
      ReserveCommandResponse
    >,
    ReserveBulkCommand: PUT as APICallT<
      {
        turnList: number[];
        action: string;
        arg?: Args;
      }[],
      ReserveBulkCommandResponse
    >,
  },
  General: {
    Join: POST as APICallT<JoinArgs>,
    GetGeneralLog: GET as APICallT<
      {
        reqType: GeneralLogType;
        reqTo?: number;
      },
      GetGeneralLogResponse
    >,
    DropItem: PUT as APICallT<{
      itemType: ItemTypeKey;
    }>,
    DieOnPrestart: POST as APICallT<undefined>,
    BuildNationCandidate: POST as APICallT<undefined>,
    GetCommandTable: GET as APICallT<undefined, CommandTableResponse>,
    GetFrontInfo: GET as APICallT<{
      lastNationNoticeDate?: string,
      lastGeneralRecordID?: number,
      lastWorldHistoryID?: number,
    }, GetFrontInfoResponse>,
  },
  Global: {
    GeneralList: GET as APICallT<undefined, GeneralListResponse>,
    GeneralListWithToken: GET as APICallT<undefined, GeneralListResponse>,
    GetConst: GET as APICallT<undefined, GetConstResponse>,
    GetHistory: StrVar("serverID")(NumVar("year", NumVar("month", GET as APICallT<undefined, GetHistoryResponse>))),
    GetCurrentHistory: GET as APICallT<undefined, GetCurrentHistoryResponse>,
    GetMap: GET as APICallT<
      {
        neutralView?: 0 | 1;
        showMe?: 0 | 1;
      },
      MapResult
    >,
    GetCachedMap: GET as APICallT<undefined, CachedMapResult>,
    GetDiplomacy: GET as APICallT<undefined, GetDiplomacyResponse>,
    ExecuteEngine: POST as APICallT<{
      serverID: string | undefined
    }, ExecuteResponse, InvalidResponse & {
      reqRefresh?: boolean
    }>,
    /*GetRecentRecord: GET as APICallT<{
      lastGeneralRecordID: number;
      lastWorldHistoryID: number;
    } | undefined, GetRecentRecordResponse>,*/
    GetGlobalMenu: GET as APICallT<undefined, GetMenuResponse>,
  },
  InheritAction: {
    BuyHiddenBuff: PUT as APICallT<{
      type: inheritBuffType;
      level: number;
    }>,
    BuyRandomUnique: PUT as APICallT<undefined>,
    ResetSpecialWar: PUT as APICallT<undefined>,
    ResetTurnTime: PUT as APICallT<undefined>,
    SetNextSpecialWar: PUT as APICallT<{
      type: string;
    }>,
    GetMoreLog: GET as APICallT<{
      lastID: number
    }, InheritLogResponse>
  },
  Message: {
    DeleteMessage: PATCH as APICallT<{
      msgID: number,
    }>,
    DecideMessageResponse: POST as APICallT<{
      msgID: number,
      response: boolean,
    }>,
    GetContactList: GET as APICallT<undefined, MabilboxListResponse>,
    GetRecentMessage: GET as APICallT<{
      sequence?: number;
    }, MsgResponse>,
    GetOldMessage: GET as APICallT<{
      to: number;
      type: MsgType;
    }, MsgResponse>,
    SendMessage: POST as APICallT<{
      mailbox: number;
      text: string;
    }, ValidResponse & {msgID: number, msgType: MsgType}>
  },
  Misc: {
    UploadImage: POST as APICallT<
      {
        imageData: string;
      },
      UploadImageResponse
    >,
  },
  NationCommand: {
    GetReservedCommand: GET as APICallT<undefined, ChiefResponse>,
    PushCommand: PUT as APICallT<{
      amount: number;
    }>,
    RepeatCommand: PUT as APICallT<{
      amount: number;
    }>,
    ReserveCommand: PUT as APICallT<
      {
        turnList: number[];
        action: string;
        arg?: Args;
      },
      ReserveCommandResponse
    >,
    ReserveBulkCommand: PUT as APICallT<
      {
        turnList: number[];
        action: string;
        arg?: Args;
      }[],
      ReserveBulkCommandResponse
    >,
  },
  Nation: {
    GeneralList: GET as APICallT<undefined, NationGeneralListResponse>,
    SetNotice: PUT as APICallT<{
      msg: string;
    }>,
    SetScoutMsg: PUT as APICallT<{
      msg: string;
    }>,
    SetBill: PATCH as APICallT<{
      amount: number;
    }>,
    SetRate: PATCH as APICallT<{
      amount: number;
    }>,
    SetSecretLimit: PATCH as APICallT<{
      amount: number;
    }>,
    SetBlockWar: PATCH as APICallT<
      {
        value: boolean;
      },
      SetBlockWarResponse
    >,
    SetBlockScout: PATCH as APICallT<{
      value: boolean;
    }>,
    GetGeneralLog: GET as APICallT<
      {
        generalID: number;
        reqType: GeneralLogType;
        reqTo?: number;
      },
      GetGeneralLogResponse
    >,
    /** @deprecated */
    SetTroopName: PATCH as APICallT<{
      troopID: number;
      troopName: string;
    }>,
    GetNationInfo: GET as APICallT<{full?: boolean}, NationInfoResponse>,
  },
  Troop: {
    NewTroop: POST as APICallT<{
      troopName: string;
    }>,
    JoinTroop: PATCH as APICallT<{
      troopID: number;
    }>,
    ExitTroop: PATCH as APICallT<undefined>,
    SetTroopName: PATCH as APICallT<{
      troopID: number;
      troopName: string;
    }>,
    KickFromTroop: PATCH as APICallT<{
      troopID: number;
      generalID: number;
    }>
  },
  Vote: {
    AddComment: POST as APICallT<{
      voteID: number;
      text: string;
    }>,
    GetVoteList: GET as APICallT<undefined, VoteListResult>,
    GetVoteDetail: GET as APICallT<
      {
        voteID: number;
      },
      VoteDetailResult
    >,
    NewVote: POST as APICallT<{
      title: string;
      multipleOptions?: number;
      endDate?: string;
      options: string[];
      keepOldVote?: boolean;
    }>,
    Vote: POST as APICallT<
      {
        voteID: number;
        selection: number[];
      },
      ValidResponse & { wonLottery: boolean }
    >,
  },
} as const;

export const SammoAPI = APIPathGen(apiRealPath, (path: string[], tail: APITail, pathParam) => {
  const method = extractHttpMethod(tail);
  return (args?: RawArgType, returnError?: boolean) => {
    if (returnError) {
      return callSammoAPI(method, path.join("/"), args, pathParam, true);
    }
    return callSammoAPI(method, path.join("/"), args, pathParam);
  };
});
