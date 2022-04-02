import type { BettingDetailResponse, ReserveBulkCommandResponse } from "./defs";
import type { Args } from "./processing/args";
import { callSammoAPI, extractHttpMethod, GET, PATCH, POST, PUT, type APITail, type APICallT, type RawArgType, type ValidResponse, type InvalidResponse } from "./util/callSammoAPI";
export type { ValidResponse, InvalidResponse };
import { APIPathGen, NumVar } from "./util/APIPathGen.js";

const apiRealPath = {
    Betting: {
        Bet: PUT,
        GetBettingDetail: NumVar('betting_id',
            GET as APICallT<undefined, BettingDetailResponse>
        ),
        GetBettingList: GET,
    },
    Command: {
        GetReservedCommand: GET as APICallT<undefined>,
        PushCommand: PATCH,
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
        BuyHiddenBuff: PUT,
        BuyRandomUnique: PUT,
        BuySpecificUnique: PUT,
        ResetSpecialWar: PUT,
        ResetTurnTime: PUT,
        SetNextSpecialWar: PUT,
    },
    Misc: { UploadImage: POST },
    NationCommand: {
        GetReservedCommand: GET,
        PushCommand: PATCH,
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