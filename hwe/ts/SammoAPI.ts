import type { BettingDetailResponse, ReserveBulkCommandResponse } from "./defs";
import type { Args } from "./processing/args";
import { callSammoAPI, extractHttpMethod, GET, PATCH, POST, PUT, type APITail, type APICallT, type RawArgType, type ValidResponse, type InvalidResponse } from "./util/callSammoAPI";
export type { ValidResponse, InvalidResponse };
import { APIPathGen, NumVar } from "./util/APIPathGen.js";
import type { BettingListResponse } from "./defs/API/Betting";
import type { ReservedCommandResponse } from "./defs/API/Command";
import type { ChiefResponse } from "./defs/API/NationCommand";
import type { inheritBuffType } from "./defs/API/InheritAction";

const apiRealPath = {
    Betting: {
        Bet: PUT,
        GetBettingDetail: NumVar('betting_id',
            GET as APICallT<undefined, BettingDetailResponse>
        ),
        GetBettingList: GET as APICallT<undefined, BettingListResponse>,
    },
    Command: {
        GetReservedCommand: GET as APICallT<undefined, ReservedCommandResponse>,
        PushCommand: PATCH as APICallT<{amount: number}>,
        RepeatCommand: PATCH,
        ReserveCommand: PUT,
        ReserveBulkCommand: PUT as APICallT<{
            turnList: number[],
            action: string,
            arg: Args
        }[], ReserveBulkCommandResponse>,
    },
    General: {
        Join: POST,
    },
    InheritAction: {
        BuyHiddenBuff: PUT as APICallT<{type: inheritBuffType, level: number}>,
        BuyRandomUnique: PUT as APICallT<undefined>,
        BuySpecificUnique: PUT,
        ResetSpecialWar: PUT as APICallT<undefined>,
        ResetTurnTime: PUT as APICallT<undefined>,
        SetNextSpecialWar: PUT,
    },
    Misc: { UploadImage: POST },
    NationCommand: {
        GetReservedCommand: GET as APICallT<undefined, ChiefResponse>,
        PushCommand: PATCH as APICallT<{amount: number}>,
        RepeatCommand: PATCH,
        ReserveCommand: PUT,
        ReserveBulkCommand: PUT as APICallT<{
            turnList: number[],
            action: string,
            arg: Args
        }[], ReserveBulkCommandResponse>,
    },
    Nation: {
        SetNotice: PUT,
        SetScoutMsg: PUT,
        SetBill: PUT,
        SetRate: PUT,
        SetSecretLimit: PUT,
        SetBlockWar: PUT,
        SetBlockScout: PUT,
    },
    Test: NumVar('id', {
        SetThis: PUT,
    })
} as const;

export const SammoAPI = APIPathGen(apiRealPath, (path: string[], tail: APITail, pathParam) => {
    const method = extractHttpMethod(tail);
    return (args?: RawArgType, returnError?: boolean) => {
        if (returnError) {
            return callSammoAPI(method, path.join('/'), args, pathParam, true);
        }
        return callSammoAPI(method, path.join('/'), args, pathParam);
    };
});