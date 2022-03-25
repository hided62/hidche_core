import type { InvalidResponse, ReserveBulkCommandResponse } from "./defs";
import type { Args } from "./processing/args";
import { callSammoAPI, type ValidResponse } from "./util/callSammoAPI";
export type { ValidResponse, InvalidResponse };

import { APIPathGen } from "./util/APIPathGen.js";

type RawArgType = Record<string,  unknown>|Record<string, unknown>[];

interface CallbackT<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends RawArgType>{
    (args?: ArgType): Promise<ResultType>;
    (args: ArgType | undefined, returnError: false): Promise<ResultType>;
    (args: ArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;
}

async function done<ResultType extends ValidResponse>(args?: RawArgType): Promise<ResultType>;
async function done<ResultType extends ValidResponse>(args: RawArgType | undefined, returnError: false): Promise<ResultType>;
async function done<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(args: RawArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;

async function done<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(args?: RawArgType, returnError = false): Promise<ResultType | ErrorType> {
    console.error(`Can't directly call. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>([], args, true);
}

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
        ReserveBulkCommand: done as CallbackT<ReserveBulkCommandResponse, InvalidResponse, {
            turnList: number[],
            action: string,
            arg: Args
        }[]>,
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
}) as typeof apiRealPath;