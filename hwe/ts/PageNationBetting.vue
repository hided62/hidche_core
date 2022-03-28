<template>
  <BContainer id="container" :toast="{ root: true }" class="pageNationBetting bg0">
    <TopBackBar :title="title" />
    <BettingDetail v-if="targetBettingID !== undefined" :bettingID="targetBettingID" @reqToast="addToast" />
    <div v-if="bettingList === undefined">로딩 중...</div>
    <div v-else class="bettingList">
      <div class="bg2">베팅 목록</div>
      <div
        v-for="info of Object.values(bettingList).reverse()"
        :key="info.id"
        class="bettingItem"
        @click="targetBettingID = info.id"
      >
        [{{ parseYearMonth(info.openYearMonth)[0] }}년 {{ parseYearMonth(info.openYearMonth)[1] }}월] {{ info.name }}
        <span v-if="info.finished">(종료)</span>
        <span v-else-if="(yearMonth ?? 0) <= info.closeYearMonth"
          >({{ parseYearMonth(info.closeYearMonth)[0] }}년 {{ parseYearMonth(info.closeYearMonth)[1] }}월까지)</span
        >
        <span v-else>(베팅 마감)</span>
      </div>
    </div>

    <BottomBar />
  </BContainer>
</template>

<script lang="ts" setup>
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import type { BettingInfo, ToastType } from "@/defs";
import { onMounted, ref } from "vue";
import { SammoAPI, type ValidResponse } from "./SammoAPI";
import { isString } from "lodash";
import { parseYearMonth } from "@/util/parseYearMonth";
import { joinYearMonth } from "./util/joinYearMonth";
import BettingDetail from "@/components/BettingDetail.vue";
import { BContainer, useToast } from "bootstrap-vue-3";
import { unwrap } from "./util/unwrap";

type BettingListResponse = ValidResponse & {
  bettingList: Record<number, Omit<BettingInfo & { totalAmount: number }, "candidates">>;
  year: number;
  month: number;
};

const toasts = unwrap(useToast());
const year = ref<number>();
const month = ref<number>();
const yearMonth = ref<number>();
const bettingList = ref<BettingListResponse["bettingList"]>();

const targetBettingID = ref<number>();

function addToast(msg: ToastType) {
  toasts.show(msg.content, msg.options);
}

console.log("시작!");
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
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    console.error(e);
  }
});

const title = "국가 베팅장";
</script>
