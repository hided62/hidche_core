<template>
  <div class="bg0">
    <div class="bg2">거래장</div>
    <div style="background-color: orange">쌀 구매</div>
    <div class="auctionItem gx-0">
      <div class="idx">번호</div>
      <div class="host">판매자</div>
      <div class="amount">수량</div>
      <div class="highestBidder">입찰자</div>
      <div class="highestBid">입찰가</div>
      <div class="bidRatio">단가</div>
      <div class="finishBid">마감가</div>
      <div class="closeDate">거래 종료</div>
    </div>
    <div
      v-for="auction of buyRice"
      :key="auction.id"
      class="auctionItem gx-0"
      @click="selectedBuyRiceAuction = auction"
    >
      <div class="idx f_tnum">{{ auction.id }}</div>
      <div class="host">{{ auction.hostName }}</div>
      <div class="amount f_tnum">쌀 {{ auction.amount.toLocaleString() }}</div>
      <div class="highestBidder">{{ auction.highestBid?.generalName ?? "-" }}</div>
      <div :class="['highestBid f_tnum', auction.highestBid ? '' : 'noBid']">
        금 {{ (auction.highestBid?.amount ?? auction.startBidAmount).toLocaleString() }}
      </div>
      <div class="bidRatio f_tnum">
        {{ auction.highestBid ? (auction.highestBid.amount / auction.amount).toFixed(2) : "-" }}
      </div>
      <div class="finishBid f_tnum">금 {{ auction.finishBidAmount.toLocaleString() }}</div>
      <div class="closeDate f_tnum">{{ cutDateTime(auction.closeDate) }}</div>
    </div>
    <div v-if="selectedBuyRiceAuction !== undefined" class="row gx-1">
      <div class="offset-1 col-4 offset-md-3 col-md-2 align-self-center f_tnum text-end">
        {{ selectedBuyRiceAuction.id }}번 쌀 {{ selectedBuyRiceAuction.amount }} 경매에 금
      </div>
      <div class="col-3 col-md-2">
        <NumberInputWithInfo
          v-model="bidAmountBuyRiceAuction"
          :int="true"
          :min="selectedBuyRiceAuction.startBidAmount"
          :max="selectedBuyRiceAuction.finishBidAmount"
          :step="10"
        ></NumberInputWithInfo>
      </div>
      <div class="col-2 col-md-1 d-grid"><BButton @click="bidBuyRiceAuction">입찰</BButton></div>
    </div>

    <div style="background-color: skyblue">쌀 판매</div>
    <div class="auctionItem gx-0">
      <div class="idx">번호</div>
      <div class="host">판매자</div>
      <div class="amount">수량</div>
      <div class="highestBidder">입찰자</div>
      <div class="highestBid">입찰가</div>
      <div class="bidRatio">단가</div>
      <div class="finishBid">마감가</div>
      <div class="closeDate">거래 종료</div>
    </div>
    <div
      v-for="auction of sellRice"
      :key="auction.id"
      class="auctionItem gx-0"
      @click="selectedSellRiceAuction = auction"
    >
      <div class="idx f_tnum">{{ auction.id }}</div>
      <div class="host">{{ auction.hostName }}</div>
      <div class="amount f_tnum">금{{ auction.amount.toLocaleString() }}</div>
      <div class="highestBidder">{{ auction.highestBid?.generalName ?? "-" }}</div>
      <div :class="['highestBid f_tnum', auction.highestBid ? '' : 'noBid']">
        쌀 {{ (auction.highestBid?.amount ?? auction.startBidAmount).toLocaleString() }}
      </div>
      <div class="bidRatio f_tnum">
        {{ auction.highestBid ? (auction.highestBid.amount / auction.amount).toFixed(2) : "-" }}
      </div>
      <div class="finishBid f_tnum">쌀 {{ auction.finishBidAmount.toLocaleString() }}</div>
      <div class="closeDate f_tnum">{{ cutDateTime(auction.closeDate) }}</div>
    </div>
    <div v-if="selectedSellRiceAuction !== undefined" class="row gx-1">
      <div class="offset-1 col-4 offset-md-3 col-md-2 align-self-center f_tnum text-end">
        {{ selectedSellRiceAuction.id }}번 금 {{ selectedSellRiceAuction.amount }} 경매에 쌀
      </div>
      <div class="col-3 col-md-2">
        <NumberInputWithInfo
          v-model="bidAmountSellRiceAuction"
          :int="true"
          :min="selectedSellRiceAuction.startBidAmount"
          :max="selectedSellRiceAuction.finishBidAmount"
          :step="10"
        ></NumberInputWithInfo>
      </div>
      <div class="col-2 col-md-1 d-grid"><BButton @click="bidSellRiceAuction">입찰</BButton></div>
    </div>

    <div>경매 등록</div>
    <div class="row gx-1">
      <div class="col-2 offset-md-2 col-md-1">
        매물<br />
        <BButtonGroup>
          <BButton :pressed="openAuctionInfo.type == 'buyRice'" @click="openAuctionInfo.type = 'buyRice'"> 쌀 </BButton>
          <BButton :pressed="openAuctionInfo.type == 'sellRice'" @click="openAuctionInfo.type = 'sellRice'">
            금
          </BButton>
        </BButtonGroup>
      </div>

      <div class="col col-md-2">
        수량 ({{ openAuctionInfo.type == "buyRice" ? "쌀" : "금" }})<br />
        <NumberInputWithInfo
          v-model="openAuctionInfo.amount"
          :int="true"
          :min="100"
          :max="10000"
          :step="10"
        ></NumberInputWithInfo>
      </div>
      <div class="col-2 col-md-1">
        기간(턴)
        <NumberInputWithInfo
          v-model="openAuctionInfo.closeTurnCnt"
          :int="true"
          :min="3"
          :max="24"
          :step="1"
        ></NumberInputWithInfo>
      </div>
      <div class="col col-md-2">
        시작가 ({{ openAuctionInfo.type == "buyRice" ? "금" : "쌀" }})
        <NumberInputWithInfo
          v-model="openAuctionInfo.startBidAmount"
          :int="true"
          :min="100"
          :max="10000"
          :step="10"
        ></NumberInputWithInfo>
              </div>
      <div class="col col-md-2">
        마감가 ({{ openAuctionInfo.type == "buyRice" ? "금" : "쌀" }})
        <NumberInputWithInfo
          v-model="openAuctionInfo.finishBidAmount"
          :int="true"
          :min="100"
          :max="10000"
          :step="10"
        ></NumberInputWithInfo>
      </div>
      <div class="col-1 d-grid">
        <BButton @click="openAuction">등록</BButton>
      </div>
    </div>
    <div>이전 경매(최근 20건)</div>
    <div v-for="(log, idx) in recentLogs" :key="idx">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="formatLog(log)" />
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { BasicResourceAuctionInfo } from "@/defs/API/Auction";
import { SammoAPI } from "@/SammoAPI";
import { unwrap } from "@/util/unwrap";
import { useToast, BButtonGroup, BButton } from "bootstrap-vue-3";
import { isString } from "lodash-es";
import { onMounted, reactive, ref, watch } from "vue";
import NumberInputWithInfo from "@/components/NumberInputWithInfo.vue";
import { formatLog } from "@/utilGame/formatLog";
const toasts = unwrap(useToast());

const buyRice = ref<BasicResourceAuctionInfo[]>([]);
const sellRice = ref<BasicResourceAuctionInfo[]>([]);
const recentLogs = ref<string[]>([]);

const selectedBuyRiceAuction = ref<BasicResourceAuctionInfo | undefined>(undefined);
const bidAmountBuyRiceAuction = ref<number>(0);

watch(selectedBuyRiceAuction, (auction) => {
  if (!auction) {
    return;
  }
  bidAmountBuyRiceAuction.value = auction.highestBid ? auction.highestBid.amount : auction.startBidAmount;
});

function cutDateTime(dateTime: string, showSecond = false) {
  if (showSecond) {
    return dateTime.substring(5, 19);
  }
  return dateTime.substring(5, 16);
}

async function bidBuyRiceAuction() {
  if (selectedBuyRiceAuction.value === undefined) {
    return;
  }
  try {
    await SammoAPI.Auction.BidBuyRiceAuction({
      auctionID: selectedBuyRiceAuction.value.id,
      amount: bidAmountBuyRiceAuction.value,
    });
    toasts.success({
      title: "입찰 완료",
      body: `입찰했습니다.`,
    });
    await refresh();
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
  }
}

const selectedSellRiceAuction = ref<BasicResourceAuctionInfo | undefined>(undefined);
const bidAmountSellRiceAuction = ref<number>(0);

watch(selectedSellRiceAuction, (auction) => {
  if (!auction) {
    return;
  }
  bidAmountSellRiceAuction.value = auction.highestBid ? auction.highestBid.amount : auction.startBidAmount;
});

async function bidSellRiceAuction() {
  if (selectedSellRiceAuction.value === undefined) {
    return;
  }
  try {
    await SammoAPI.Auction.BidSellRiceAuction({
      auctionID: selectedSellRiceAuction.value.id,
      amount: bidAmountSellRiceAuction.value,
    });
    toasts.success({
      title: "입찰 완료",
      body: `입찰했습니다.`,
    });
    await refresh();
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
  }
}

type openAuctionT = {
  type: "buyRice" | "sellRice";
  amount: number;
  startBidAmount: number;
  finishBidAmount: number;
  closeTurnCnt: number;
};

const openAuctionInfo = reactive<openAuctionT>({
  type: "buyRice",
  amount: 1000,
  startBidAmount: 500,
  finishBidAmount: 2000,
  closeTurnCnt: 24,
});

async function refresh() {
  try {
    const result = await SammoAPI.Auction.GetActiveResourceAuctionList();
    buyRice.value = result.buyRice;
    sellRice.value = result.sellRice;
    recentLogs.value = result.recentLogs;
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
  }
}

async function openAuction() {
  const { type, amount, startBidAmount, finishBidAmount, closeTurnCnt } = openAuctionInfo;

  try {
    const apiCall = type === "buyRice" ? SammoAPI.Auction.OpenBuyRiceAuction : SammoAPI.Auction.OpenSellRiceAuction;
    const result = await apiCall({
      amount,
      startBidAmount,
      finishBidAmount,
      closeTurnCnt,
    });
    toasts.success({
      title: "성공",
      body: `${result.auctionID}번 경매로 등록되었습니다.`,
    });
    await refresh();
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
  }
}

defineExpose({
  refresh,
});

onMounted(async () => {
  void refresh();
  console.log("mounted");
});
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

.auctionItem {
  display: grid;
  text-align: center;

  > div {
    align-self: center;
  }

  .noBid {
    color: #ccc;
  }

  border-bottom: solid gray 1px;
}

@include media-500px {
  .auctionItem {
    grid-template-columns: 1fr 3fr 3fr 1fr 2fr 2fr;
    grid-template-rows: 1fr 1fr;

    .idx {
      grid-column: 1 / 2;
      grid-row: 1 / 3;
    }

    .host {
      grid-column: 2 / 3;
      grid-row: 1 / 2;
    }

    .amount {
      grid-column: 2 / 3;
      grid-row: 2/ 3;
    }

    .highestBidder {
      grid-column: 3 / 4;
      grid-row: 1 / 2;
    }

    .highestBid {
      grid-column: 3 / 4;
      grid-row: 2 / 3;
    }

    .bidRatio {
      grid-column: 4 / 5;
      grid-row: 1 / 3;
    }

    .finishBid {
      grid-column: 5 / 6;
      grid-row: 1 / 3;
    }

    .closeDate {
      grid-column: 6 / 7;
      grid-row: 1 / 3;
    }
  }
}

@include media-1000px {
  .auctionItem {
    grid-template-columns: 1fr 2fr 2fr 2fr 2fr 1fr 3fr 2fr;
    grid-template-rows: 1fr;
  }
}
</style>
