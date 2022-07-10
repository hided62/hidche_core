import type { CommandItem, TurnObj } from "@/defs";

export type ChiefResponse = {
    result: true;
    lastExecute: string;
    year: number;
    month: number;
    turnTerm: number;
    date: string;
    chiefList: Record<
        number,
        {
            name: string | undefined;
            turnTime: string | undefined;
            officerLevelText: string;
            officerLevel: number;
            npcType: number;
            turn: TurnObj[];
        }
    >;
    troopList: Record<number, string>;
    isChief: boolean;
    autorun_limit: number;
    officerLevel: number;
    commandList: {
        category: string;
        values: CommandItem[];
    }[];
    mapName: string,
    unitSet: string,
};