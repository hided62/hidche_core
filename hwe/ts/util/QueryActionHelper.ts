import type { TurnObj } from "@/defs";
import { clone, isString, range } from "lodash";
import { ref, type Ref } from "vue";
import { unwrap } from "./unwrap";

export type TurnObjWithTime = TurnObj & {
    time: string;
    year?: number;
    month?: number;
    tooltip?: string;
    style?: Record<string, string>;
};

export const getEmptyTurn = (maxTurn: number): TurnObjWithTime[] => Array.from<TurnObjWithTime>({
    length: maxTurn,
}).fill({
    arg: {},
    brief: "",
    action: "",
    year: undefined,
    month: undefined,
    time: "",
});

export class QueryActionHelper {
    public readonly reservedCommandList: Ref<TurnObjWithTime[]>;
    public readonly selectedTurnList: Ref<Set<number>>;
    public readonly prevSelectedTurnList: Ref<Set<number>>;

    constructor(
        protected maxTurn: number,
    ) {
        this.reservedCommandList = ref(getEmptyTurn(maxTurn));
        this.selectedTurnList = ref(new Set());
        this.prevSelectedTurnList = ref(new Set([0]));
    }

    toggleTurn(...reqTurnList: number[] | string[]) {
        for (let turnIdx of reqTurnList) {
            if (isString(turnIdx)) {
                turnIdx = parseInt(turnIdx);
            }
            if (this.selectedTurnList.value.has(turnIdx)) {
                this.selectedTurnList.value.delete(turnIdx);
            } else {
                this.selectedTurnList.value.add(turnIdx);
            }
        }
    }

    selectTurn(...reqTurnList: number[] | string[]) {
        this.selectedTurnList.value.clear();
        for (const turnIdx of reqTurnList) {
            if (isString(turnIdx)) {
                this.selectedTurnList.value.add(parseInt(turnIdx));
            } else {
                this.selectedTurnList.value.add(turnIdx);
            }
        }
    }

    selectStep(begin: number, step: number) {
        this.selectedTurnList.value.clear();
        for (const idx of range(0, this.maxTurn)) {
            if ((idx - begin) % step == 0) {
                this.selectedTurnList.value.add(idx);
            }
        }
    }

    selectAll() {
        for (let i = 0; i < this.maxTurn; i++) {
            this.selectedTurnList.value.add(i);
        }
    }

    getSelectedTurnList(useSort = true): number[] {
        let result: number[];
        if (this.selectedTurnList.value.size) {
            result = Array.from(this.selectedTurnList.value);
        }
        else if (this.prevSelectedTurnList.value.size) {
            result = Array.from(this.prevSelectedTurnList.value);
        }
        else {
            return [0];
        }

        if (useSort) {
            return result.sort((a, b) => a - b);
        }
        return result;
    }

    releaseSelectedTurnList() {
        if (this.selectedTurnList.value.size > 0) {
            this.prevSelectedTurnList.value.clear();
            for (const v of this.selectedTurnList.value) {
                this.prevSelectedTurnList.value.add(v);
            }
            this.selectedTurnList.value.clear();
        }
    }

    extractQueryActions(): [number[], TurnObj][] {
        const reqTurnList = this.getSelectedTurnList();
        const selectedMinTurnIdx = unwrap(Math.min(...reqTurnList));

        const buffer = new Map<string, [number[], TurnObj]>();
        for (const rawTurnIdx of reqTurnList) {
            const turnIdx = rawTurnIdx - selectedMinTurnIdx;
            const rawAction = this.reservedCommandList.value[rawTurnIdx]
            const actionStr = JSON.stringify([rawAction.action, rawAction.arg]);
            if (buffer.has(actionStr)) {
                const items = unwrap(buffer.get(actionStr));
                items[0].push(turnIdx);
            }
            else {
                buffer.set(actionStr, [[turnIdx], {
                    action: rawAction.action,
                    arg: clone(rawAction.arg),
                    brief: rawAction.brief
                }])
            }
        }
        return Array.from(buffer.values());
    }

    amplifyQueryActions(rawActions: [number[], TurnObj][], reqTurnList: number[]): [number[], TurnObj][] {
        if (reqTurnList.length < 1) {
            return [];
        }

        let minQueryIdx = this.maxTurn;
        let maxQueryIdx = 0;
        for (const [turnList] of rawActions) {
            for (const turnIdx of turnList) {
                minQueryIdx = Math.min(minQueryIdx, turnIdx);
                maxQueryIdx = Math.max(maxQueryIdx, turnIdx);
            }
        }
        const queryLength = maxQueryIdx - minQueryIdx + 1;

        const queryTurnList: number[] = [reqTurnList[0]];
        for (const reqTurnIdx of reqTurnList) {
            const last = queryTurnList[queryTurnList.length - 1];
            if (reqTurnIdx < last + queryLength) {
                continue;
            }
            queryTurnList.push(reqTurnIdx);
        }

        const actions: [number[], TurnObj][] = [];
        for (const [baseTurnList, action] of rawActions) {
            const subTurnList: number[] = [];
            for (const baseTurnIdx of baseTurnList) {
                for (const queryTurnIdx of queryTurnList) {
                    const targetTurn = baseTurnIdx + queryTurnIdx;
                    if (targetTurn >= this.maxTurn) {
                        continue;
                    }
                    subTurnList.push(baseTurnIdx + queryTurnIdx);
                }
            }
            if (subTurnList.length == 0) {
                continue;
            }
            actions.push([subTurnList, action]);
        }

        return actions;
    }
}
