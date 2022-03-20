<template>
    <div v-if="bettingDetailInfo !== undefined && info !== undefined">
        <div class="bg2">
            {{ info.name }}
            <span v-if="info.finished">(종료)</span>
            <span
                v-else-if="(yearMonth ?? 0) <= info.closeYearMonth"
            >({{ parseYearMonth(info.closeYearMonth)[0] }}년 {{ parseYearMonth(info.closeYearMonth)[1] }}월까지)</span>
            <span v-else>(베팅 마감)</span>
            (총액: {{ bettingAmount.toLocaleString() }})
        </div>

        <div class="row bettingCandidates gx-1 gy-1">
            <div
                class="col-4 col-md-2"
                v-for="(candidate, idx) in info.candidates"
                :key="idx"
                @click="toggleCandidate(idx)"
            >
                <div
                    :class="[
                        'bettingCandidate',
                        pickedBetType.has(idx) ? 'picked' : undefined,
                        (info.finished && winner.has(idx)) ? 'picked' : undefined,
                    ]"
                >
                    <div class="title bg1">{{ candidate.title }}</div>
                    <div class="info" v-if="candidate.isHtml" v-html="candidate.info"></div>
                    <div
                        class="pickRate"
                    >선택율: {{ ((partialBet.get(idx) ?? 0) / pureBettingAmount * 100).toFixed(1) }}%</div>
                </div>
            </div>
        </div>
        <div v-if="!info.finished && (yearMonth ?? 0) <= info.closeYearMonth" class="row gx-0">
            <div
                class="col-6 col-md-3 align-self-center"
            >잔여 {{ info.reqInheritancePoint ? '포인트' : '금' }} : {{ bettingDetailInfo.remainPoint.toLocaleString() }}</div>
            <div
                class="col-6 col-md-3 align-self-center"
            >사용 포인트: {{ sum(Array.from(myBettings.values())).toLocaleString() }}</div>
            <div class="col-6 col-md-3 align-self-center">대상: {{ getTypeStr(pickedBetTypeKey) }}</div>
            <div class="col-4 col-md-2 d-grid">
                <b-form-input
                    class="d-grid"
                    type="number"
                    v-model="betPoint"
                    :min="10"
                    :max="1000"
                    :step="10"
                ></b-form-input>
            </div>
            <div class="col-2 col-md-1 d-grid">
                <b-button class="d-grid" @click="submitBet">베팅</b-button>
            </div>
        </div>

        <div>
            <div class="bg2">배당 순위</div>
            <div
                class="row"
                :style="{
                    borderBottom: 'gray solid 1px'
                }"
            >
                <div class="col-5 text-center">대상</div>
                <div class="col-2 text-center">베팅액</div>
                <div class="col-3 text-center">내 베팅</div>
                <div class="col-2 text-center">{{ info.finished ? '배율' : '기대 배율' }}</div>
            </div>
            <template v-if="info.finished">
                <div class="row" v-for="[betType, amount] of detailBet" :key="betType">
                    <template
                        v-for="[matchPoint, color] of [calcMatchPointWithColor(betType)]"
                        :key="matchPoint"
                    >
                        <div
                            class="col-5"
                            :style="{
                                fontWeight: myBettings.has(betType) ? 'bold' : undefined,
                                color: color
                            }"
                        >{{ getTypeStr(betType) }}</div>
                        <div class="col-2 text-end">{{ amount.toLocaleString() }}</div>
                        <div class="col-3 text-center" v-if="myBettings.has(betType)">
                            <template
                                v-for="subPoint of [myBettings.get(betType) ?? 0]"
                            >({{ subPoint.toLocaleString() }} -> {{ calculatedReward[matchPoint] == 0 ? 0 : (subPoint * calculatedReward[matchPoint] / (calculatedSubAmount.get(matchPoint) ?? 1)).toFixed(1).toLocaleString() }})</template>
                        </div>
                        <div class="col-3 text-center" v-else></div>
                        <div
                            class="col-2 text-end"
                        >{{ (calculatedReward[matchPoint] == 0 ? 0 : (calculatedReward[matchPoint] / (calculatedSubAmount.get(matchPoint) ?? 1))).toFixed(1).toLocaleString() }}배</div>
                    </template>
                </div>
            </template>
            <template v-else>
                <div class="row" v-for="[betType, amount] of detailBet" :key="betType">
                    <div
                        class="col-5"
                        :style="{
                            fontWeight: myBettings.has(betType) ? 'bold' : undefined,
                        }"
                    >{{ getTypeStr(betType) }}</div>
                    <div class="col-2 text-end">{{ amount.toLocaleString() }}</div>
                    <div class="col-3 text-center" v-if="myBettings.has(betType)">
                        <template
                            v-for="subPoint of [myBettings.get(betType) ?? 0]"
                        >({{ subPoint.toLocaleString() }} -> {{ (subPoint * maxBettingReward / amount).toFixed(1).toLocaleString() }})</template>
                    </div>
                    <div class="col-3 text-center" v-else></div>
                    <div
                        class="col-2 text-end"
                    >{{ (maxBettingReward / amount).toFixed(1).toLocaleString() }}배</div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { BettingInfo, ToastType } from '@/defs';
import { SammoAPI, type ValidResponse } from "@/SammoAPI";
import { joinYearMonth } from '@/util/joinYearMonth';
import { parseYearMonth } from '@/util/parseYearMonth';
import { isString, range, sum } from 'lodash';
import { ref, defineProps, defineEmits, type PropType, watch } from "vue";

type BettingDetailResponse = ValidResponse & {
    bettingInfo: BettingInfo;
    bettingDetail: [string, number][];
    myBetting: [string, number][];
    remainPoint: number;
    year: number;
    month: number;
}


const props = defineProps({
    bettingID: {
        type: Number as PropType<number>,
        required: true,
    }
});

const emit = defineEmits<{
    (event: 'reqToast', content: ToastType): void,
}>();

const year = ref<number>(0);
const month = ref<number>(0);
const yearMonth = ref<number>(0);

const bettingDetailInfo = ref<BettingDetailResponse>();
const info = ref<BettingInfo>();

const bettingAmount = ref<number>(0);
const maxBettingReward = ref<number>(1);
const pureBettingAmount = ref<number>(0);

const partialBet = ref(new Map<number, number>());
const detailBet = ref<[string, number][]>([]);

const typeMap = ref(new Map<string, string>());
function getTypeStr(type: string): string {
    const typeResult = typeMap.value.get(type);
    if (typeResult !== undefined) {
        return typeResult;
    }
    const bettingSubTypes = JSON.parse(type) as number[];
    if (bettingSubTypes[0] < -1) {
        return 'Invalid';
    }

    const textBettingType = bettingSubTypes.map((idx) => {
        return bettingDetailInfo.value?.bettingInfo.candidates[idx].title;
    }).join(', ');
    typeMap.value.set(type, textBettingType);
    return textBettingType;
}

const pickedBetType = ref(new Set<number>());
const pickedBetTypeKey = ref('[]');

const betPoint = ref(0);
const myBettings = ref(new Map<string, number>());

const winner = ref(new Set<number>());
const calculatedReward = ref<number[]>([]);
const calculatedSubAmount = ref(new Map<number, number>());


function calcMatchPointWithColor(type: string): [number, 'green' | 'yellow' | 'red' | undefined] {
    if (!info.value?.finished) {
        return [0, undefined];
    }
    const bettingSubTypes = JSON.parse(type) as number[];
    if (bettingSubTypes[0] < -1) {
        return [0, undefined];
    }

    let matchPoint = 0;
    for (const subType of bettingSubTypes) {
        if (winner.value.has(subType)) {
            matchPoint += 1;
        }
    }

    if (info.value.isExclusive) {
        if (matchPoint == info.value.selectCnt) {
            return [matchPoint, 'green'];
        }
        else {
            return [matchPoint, 'red'];
        }
    }

    let color: 'green' | 'red' | 'yellow' = 'green';
    if (matchPoint == 0) {
        color = 'red';
    }
    else if (matchPoint < info.value.selectCnt) {
        color = 'yellow';
    }
    return [matchPoint, color];
}


function calcReward() {
    if (info.value === undefined || bettingDetailInfo.value === undefined) {
        throw 'no info';
    }
    const selectCnt = info.value.selectCnt;
    const rewardAmount = new Array<number>(selectCnt).fill(0);

    const subAmount = new Map<number, number>();
    for (const [bettingTypeStr, amount] of bettingDetailInfo.value.bettingDetail) {
        if (amount == 0) {
            continue;
        }
        const [matchPoint,] = calcMatchPointWithColor(bettingTypeStr);
        subAmount.set(matchPoint, (subAmount.get(matchPoint) ?? 0) + amount);
    }
    calculatedSubAmount.value = subAmount;

    if (selectCnt == 1){
        rewardAmount[selectCnt - 1] = bettingAmount.value;
        calculatedReward.value = rewardAmount;
        return;
    }

    if (info.value.isExclusive) {
        rewardAmount[selectCnt - 1] = bettingAmount.value;
        calculatedReward.value = rewardAmount;
        return;
    }

    let remainRewardAmount = bettingAmount.value;

    for (const matchPoint of range(selectCnt, 0, -1)) {
        if (!subAmount.has(matchPoint)) {
            continue;
        }

        const givenRewardAmount = remainRewardAmount / 2;
        rewardAmount[matchPoint] = givenRewardAmount;
        remainRewardAmount -= givenRewardAmount; // /2가 아니라 다른 값이 될 경우를 대비..
    }

    for (const matchPoint of range(1, selectCnt + 1)) {
        if (!subAmount.has(matchPoint)) {
            continue;
        }
        rewardAmount[matchPoint] += remainRewardAmount;
        break;
    }

    calculatedReward.value = rewardAmount;
}

async function loadBetting(bettingID: number) {
    try {
        const result = await SammoAPI.Betting.GetBettingDetail<BettingDetailResponse>({
            betting_id: bettingID
        });
        year.value = result.year;
        month.value = result.month;
        yearMonth.value = joinYearMonth(result.year, result.month);
        bettingDetailInfo.value = result;
        info.value = result.bettingInfo;

        partialBet.value.clear();

        const betSort = new Map<string, number>();


        let _bettingAmount = 0;
        let adminBettingAmount = 0;
        for (const [bettingType, amount] of result.bettingDetail) {
            console.log(amount, typeof (amount));
            let userBet = true;
            const bettingSubTypes = JSON.parse(bettingType) as number[];
            for (const bettingSubType of bettingSubTypes) {
                if (bettingSubType < 0) {
                    userBet = false;
                    continue;
                }
                const oldValue = partialBet.value.get(bettingSubType) ?? 0;
                partialBet.value.set(bettingSubType, oldValue + amount);
            }

            if (userBet) {
                const oldValue = betSort.get(bettingType) ?? 0;
                betSort.set(bettingType, oldValue + amount);
            }

            _bettingAmount += amount;
            if (!userBet) {
                adminBettingAmount += amount;
            }
        }
        console.log(_bettingAmount);
        bettingAmount.value = _bettingAmount;
        pureBettingAmount.value = _bettingAmount - adminBettingAmount;
        if (info.value.isExclusive || info.value.selectCnt == 1) {
            maxBettingReward.value = _bettingAmount;
        } else {
            maxBettingReward.value = _bettingAmount / 2;
        }

        detailBet.value = Array.from(betSort.entries());
        detailBet.value.sort(([, lhsVal], [, rhsVal]) => {
            return rhsVal - lhsVal;
        })

        pickedBetType.value.clear();
        pickedBetTypeKey.value = '[]';
        myBettings.value.clear();


        if (result.bettingInfo.winner) {
            winner.value = new Set(result.bettingInfo.winner);
        }
        else {
            winner.value.clear();
        }

        for (const [betType, amount] of result.myBetting) {
            myBettings.value.set(betType, amount);
        }
        calcReward();

    } catch (e) {
        if (isString(e)) {
            emit('reqToast', {
                content: {
                    title: "에러",
                    body: e
                },
                options: {
                    variant: 'danger',
                }
            });
        }
        console.error(e);
    }
}

void loadBetting(props.bettingID);
watch(() => props.bettingID, (newBettingID) => {
    void loadBetting(newBettingID);
});


function toggleCandidate(idx: number) {
    if (info.value === undefined) {
        return;
    }
    if (bettingDetailInfo.value === undefined) {
        return;
    }
    if (info.value.closeYearMonth < yearMonth.value) {
        return;
    }
    if (info.value.finished) {
        return;
    }
    const selectCnt = bettingDetailInfo.value.bettingInfo.selectCnt;

    if (selectCnt == 1) {
        pickedBetType.value.clear();
        pickedBetType.value.add(idx);
        pickedBetTypeKey.value = JSON.stringify([idx]);
        return;
    }

    if (pickedBetType.value.has(idx)) {
        pickedBetType.value.delete(idx);
    }
    else if (pickedBetType.value.size < selectCnt) {
        pickedBetType.value.add(idx);
    }
    else {
        emit('reqToast', {
            content: {
                title: '오류',
                body: `이미 ${selectCnt}개를 선택했습니다.`,
            },
            options: {
                variant: 'warning',
            }
        });
        return;
    }

    const typeArr = Array.from(pickedBetType.value.values());
    pickedBetTypeKey.value = JSON.stringify(typeArr.sort((lhs, rhs) => lhs - rhs));
}

async function submitBet(): Promise<void> {

    const bettingInfo = info.value;
    if (bettingInfo === undefined) {
        return;
    }

    const bettingID = bettingInfo.id;
    const bettingType = JSON.parse(pickedBetTypeKey.value);
    const amount = betPoint.value;
    try {
        await SammoAPI.Betting.Bet({
            bettingID,
            bettingType,
            amount,
        });
        emit('reqToast', {
            content: {
                title: '완료',
                body: '베팅했습니다',
            },
            options: {
                variant: 'success'
            }
        });
        await loadBetting(bettingInfo.id);
    } catch (e) {
        if (isString(e)) {
            emit('reqToast', {
                content: {
                    title: "에러",
                    body: e,
                },
                options: {
                    variant: "danger",
                }
            });
        }
        console.error(e);
    }

}
</script>