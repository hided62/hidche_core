export type InvalidResponse = {
    result: false;
    reason: string;
}

export type BasicGeneralListResponse = {
    result: true,
    nationID: number,
    generalID: number,
    column:['no', 'name', 'npc'],
    list:Record<string, [number, string, number][]>,
    nation:Record<string, {
        nation: number,
        name: string,
        color: string,
    }>
}