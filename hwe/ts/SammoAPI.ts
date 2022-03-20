import type { InvalidResponse } from "./defs";
import { APIPathGen } from "./util/APIPathGen";
import { callSammoAPI, type ValidResponse } from "./util/callSammoAPI";
export type { ValidResponse, InvalidResponse };

async function done<ResultType extends ValidResponse>(args?: Record<string, unknown>): Promise<ResultType>;
async function done<ResultType extends ValidResponse>(args: Record<string, unknown> | undefined, returnError: false): Promise<ResultType>;
async function done<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(args: Record<string, unknown> | undefined, returnError: true): Promise<ResultType | ErrorType>;

async function done<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(args?: Record<string, unknown>, returnError = false): Promise<ResultType | ErrorType> {
    console.error(`Can't directly call. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>([], args, true);
}

const apiRealPath = {
    Betting:{
        Bet: done,
        GetBettingDetail: done,
        GetBettingList: done,
    },
    Command: {
        GetReservedCommand: done,
        PushCommand: done,
        RepeatCommand: done,
        ReserveCommand: done,
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

export const SammoAPI = APIPathGen<typeof done, typeof apiRealPath>(apiRealPath, (path: string[]) => {
    return (args?: Record<string, unknown>, returnError?: boolean) => {
        if (returnError) {
            return callSammoAPI(path.join('/'), args, true);
        }
        return callSammoAPI(path.join('/'), args);
    };
});