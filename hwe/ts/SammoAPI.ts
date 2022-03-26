import type { InvalidResponse, ReserveBulkCommandResponse } from "./defs";
import type { Args } from "./processing/args";
import { callSammoAPI, done, type CallbackT, type RawArgType, type ValidResponse } from "./util/callSammoAPI";
export type { ValidResponse, InvalidResponse };
import { APIPathGen } from "./util/APIPathGen.js";

const apiRealPath = {
    Betting: {
        Bet: done,
        GetBettingDetail: done,
        GetBettingList: done,
    },
    Command: {
        GetReservedCommand: done,
        PushCommand: done,
        RepeatCommand: done,
        ReserveCommand: done,
        ReserveBulkCommand: done as CallbackT<{
            turnList: number[],
            action: string,
            arg: Args
        }[], ReserveBulkCommandResponse>,
    },
    General: {
        Join: done,
    },
    InheritAction: {
        BuyHiddenBuff: done,
        BuyRandomUnique: done,
        BuySpecificUnique: done,
        ResetSpecialWar: done,
        ResetTurnTime: done,
        SetNextSpecialWar: done,
    },
    Misc: { UploadImage: done },
    NationCommand: {
        GetReservedCommand: done,
        PushCommand: done,
        RepeatCommand: done,
        ReserveCommand: done,
        ReserveBulkCommand: done as CallbackT<{
            turnList: number[],
            action: string,
            arg: Args
        }[], ReserveBulkCommandResponse>,
    },
    Nation: {
        SetNotice: done,
        SetScoutMsg: done,
        SetBill: done,
        SetRate: done,
        SetSecretLimit: done,
        SetBlockWar: done,
        SetBlockScout: done,
    },
} as const;

export const SammoAPI = APIPathGen(apiRealPath, (path: string[]) => {
    return (args?: RawArgType, returnError?: boolean) => {
        if (returnError) {
            return callSammoAPI(path.join('/'), args, true);
        }
        return callSammoAPI(path.join('/'), args);
    };
});