export type InvalidResponse = {
    result: false;
    reason: string;
}

export type BasicGeneralListResponse = {
    result: true,
    nationID: number,
    generalID: number,
    column: ['no', 'name', 'npc'],
    list: Record<string, [number, string, number][]>,
    nation: Record<string, {
        nation: number,
        name: string,
        color: string,
    }>
}

export type GeneralListResponse = {
    result: true,
    list: [
        number,
        string, 0|1,
        number, number, string,
        string, string, string,
        string, string | null,
        number,
        number, number, number, number,
        number,
        string, string, string,
        number, number
    ][],
    token: Record<number, number>,
}