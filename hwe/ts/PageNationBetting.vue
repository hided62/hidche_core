<template>
  <MyToast v-model="toasts" />
  <div id="container" class="pageNationBetting bg0">
    <TopBackBar :title="title" />
    <div v-if="bettingInfo !== undefined">
      <template v-for="(info, idx) in [bettingInfo.bettingInfo]" :key="idx">
        <div>
          {{ info.name }}
          <span v-if="info.finished">(종료)</span>
          <span
            v-else-if="(yearMonth ?? 0) <= info.closeYearMonth"
          >({{ parseYearMonth(info.closeYearMonth)[0] }}년 {{ parseYearMonth(info.closeYearMonth)[1] }}월까지)</span>
          <span v-else>(베팅 마감)</span>
          (총액: {{ bettingAmount.toLocaleString() }})
        </div>

        <div class="row bettingCandidates">
          <div
            class="col-4 col-md-2"
            v-for="(candidate, idx) in bettingInfo.bettingInfo.candidates"
            :key="idx"
            @click="toggleCandidate(idx)"
          >
            <div>{{ candidate.title }}</div>
            <div v-if="candidate.isHtml" v-html="candidate.info"></div>
            <div>선택율: {{ ((partialBet.get(idx) ?? 0) / pureBettingAmount * 100).toFixed(1) }}%</div>
          </div>
        </div>
        <div v-if="!info.finished && (yearMonth ?? 0) <= info.closeYearMonth" class="row">
          <div
            class="col-3"
          >잔여 {{ info.reqInheritancePoint ? '포인트' : '금' }} : {{ bettingInfo.remainPoint.toLocaleString() }}</div>
          <div class="col-3">
            <b-form-input type="number" v-model="betPoint" :min="10" :max="1000" :step="10"></b-form-input>
          </div>
          <div class="col-3">{{ getTypeStr(choosedBetTypeKey) }}</div>
          <div class="col-3">
            <b-button @click="submitBet">베팅</b-button>
          </div>
        </div>

        <div>
          배당 순위
          <div class="row" v-for="[betType, amount] of detailBet" :key="betType">
            <div
              class="col-6 col-md-3"
              :style="{
                fontWeight: myBettings.has(betType) ? 'bold' : undefined
              }"
            >{{ getTypeStr(betType) }}</div>
            <div class="col-3 col-md-6">{{ amount.toLocaleString() }}{{ myBettings.has(betType)?`(${myBettings.get(betType)?.toLocaleString()})`:'' }}</div>
            <div class="col-3 col-md-3">{{ (bettingAmount / amount).toFixed(2) }}배</div>
          </div>
        </div>
      </template>
    </div>
    <div v-if="bettingList === undefined">로딩 중...</div>
    <div class="bettingList" v-else>
      <div
        class="bettingItem"
        v-for="info of Object.values(bettingList).reverse()"
        :key="info.id"
        @click="loadBetting(info.id)"
      >
        {{ info.name }}
        <span v-if="info.finished">(종료)</span>
        <span
          v-else-if="(yearMonth ?? 0) <= info.closeYearMonth"
        >({{ parseYearMonth(info.closeYearMonth)[0] }}년 {{ parseYearMonth(info.closeYearMonth)[1] }}월까지)</span>
        <span v-else>(베팅 마감)</span>
      </div>
    </div>

    <BottomBar />
  </div>
</template>

<script lang="ts" setup>
import MyToast from "@/components/MyToast.vue";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { ToastType } from "@/defs";
import { onMounted, ref } from "vue";
import { SammoAPI, ValidResponse } from "./SammoAPI";
import { isString } from "lodash";
import { parseYearMonth } from "@/util/parseYearMonth";
import { joinYearMonth } from "./util/joinYearMonth";

type SelectItem = {
  title: string;
  info?: string;
  isHtml?: boolean;
  aux?: Record<string, unknown>;
}

type BettingInfo = {
  id: number;
  type: 'nationBetting',
  name: string;
  finished: boolean;
  selectCnt: number;
  reqInheritancePoint: boolean;
  openYearMonth: number;
  closeYearMonth: number;
  candidates: SelectItem[];
}

type BettingListResponse = ValidResponse & {
  bettingList: Record<number, Omit<BettingInfo & { totalAmount: number }, 'candidates'>>,
  year: number,
  month: number,
};

type BettingDetailResponse = ValidResponse & {
  bettingInfo: BettingInfo;
  bettingDetail: [string, number][];
  myBetting: [string, number][];
  remainPoint: number;
  year: number;
  month: number;
}

const toasts = ref<ToastType[]>([]);
const year = ref<number>();
const month = ref<number>();
const yearMonth = ref<number>();
const bettingList = ref<BettingListResponse['bettingList']>();
const bettingInfo = ref<BettingDetailResponse>();

const bettingAmount = ref<number>(0);
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
    return bettingInfo.value?.bettingInfo.candidates[idx].title;
  }).join(', ');
  typeMap.value.set(type, textBettingType);
  return textBettingType;
}

const choosedBetType = ref(new Set<number>());
const choosedBetTypeKey = ref('[]');

const betPoint = ref(0);
const myBettings = ref(new Map<string, number>());

function toggleCandidate(idx: number) {
  if (bettingInfo.value === undefined) {
    return;
  }
  const selectCnt = bettingInfo.value.bettingInfo.selectCnt;

  if (selectCnt == 1) {
    choosedBetTypeKey.value = JSON.stringify([idx]);
    return;
  }

  if (choosedBetType.value.has(idx)) {
    choosedBetType.value.delete(idx);
  }
  else if (choosedBetType.value.size < selectCnt) {
    choosedBetType.value.add(idx);
  }
  else {
    return;
  }


  const typeArr = Array.from(choosedBetType.value.values());
  choosedBetTypeKey.value = JSON.stringify(typeArr.sort());
}

async function loadBetting(bettingID: number) {
  try {
    const result = await SammoAPI.Betting.GetBettingDetail<BettingDetailResponse>({
      betting_id: bettingID
    });
    year.value = result.year;
    month.value = result.month;
    yearMonth.value = joinYearMonth(result.year, result.month);
    bettingInfo.value = result;

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

    detailBet.value = Array.from(betSort.entries());
    detailBet.value.sort(([, lhsVal], [, rhsVal]) => {
      return rhsVal - lhsVal;
    })

    choosedBetType.value.clear();
    choosedBetTypeKey.value = '[]';
    myBettings.value.clear();

    for(const [betType, amount] of result.myBetting){
      myBettings.value.set(betType, amount);
    }


  } catch (e) {
    if (isString(e)) {
      toasts.value.push({
        title: "에러",
        content: e,
        type: "danger",
      });
    }
    console.error(e);
  }
}

async function submitBet(): Promise<void> {
  const info = bettingInfo.value;
  if (info === undefined) {
    return;
  }

  const bettingID = info.bettingInfo.id;
  const bettingType = JSON.parse(choosedBetTypeKey.value);
  const amount = betPoint.value;
  try {
    await SammoAPI.Betting.Bet({
      bettingID,
      bettingType,
      amount,
    });
    toasts.value.push({
      title: '완료',
      content: '베팅했습니다',
      type: 'success'
    });
    await loadBetting(info.bettingInfo.id);
  } catch (e) {
    if (isString(e)) {
      toasts.value.push({
        title: "에러",
        content: e,
        type: "danger",
      });
    }
    console.error(e);
  }

}

console.log('시작!');
onMounted(async () => {
  try {
    const result = await SammoAPI.Betting.GetBettingList<BettingListResponse>();
    year.value = result.year;
    month.value = result.month;
    yearMonth.value = joinYearMonth(result.year, result.month);
    bettingList.value = result.bettingList;
    console.log(result);
  } catch (e) {
    if (isString(e)) {
      toasts.value.push({
        title: "에러",
        content: e,
        type: "danger",
      });
    }
    console.error(e);
  }
});


const title = '국가 베팅장';


</script>