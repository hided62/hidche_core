import { APIPathGen } from "./util/APIPathGen";

const apiRealPath = {
    Command: {
        GetReservedCommand: '',
        PushCommand: '',
        RepeatCommand: '',
        ReserveCommand: '',
    },
    General: {
        Join: '',
    },
    InheritAction: {
        BuyHiddenBuff: '',
        BuyRandomUnique: '',
        BuySpecificUnique: '',
        ResetSpecialWar: '',
        ResetTurnTime: '',
        SetNextSpecialWar: '',
    },
    Misc: { UploadImage: '' },
    NationCommand: {
        GetReservedCommand: '',
        PushCommand: '',
        RepeatCommand: '',
        ReserveCommand: '',
    },
    Nation: {
        SetNotice: '',
        SetScoutMsg: '',
    },
} as const;

export const APIPath = APIPathGen(apiRealPath);