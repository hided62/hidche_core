<template>
  <div class="bg0">
    <div class="bg2">거래장</div>
    <div style="background-color: orange">쌀 구매</div>
    <div class="row gx-0">
      <div class="col">번호</div>
      <div class="col">판매자</div>
      <div class="col">물품</div>
      <div class="col">수량</div>
      <div class="col">시작 구매가</div>
      <div class="col">현재 구매가</div>
      <div class="col">즉시 구매가</div>
      <div class="col">단가</div>
      <div class="col">구매 예정자</div>
      <div class="col">거래 종료</div>
    </div>
    <div v-for="auction of buyRice" :key="auction.id" class="row gx-0" @click="selectedBuyRiceAuction = auction">
      <div class="col">{{ auction.id }}</div>
      <div class="col">{{ auction.hostName }}</div>
      <div class="col">쌀</div>
      <div class="col">{{ auction.amount }}</div>
      <div class="col">{{ auction.startBidAmount }}</div>
      <div class="col">{{ auction.highestBid ? auction.highestBid.amount : "-" }}</div>
      <div class="col">{{ auction.finishBidAmount }}</div>
      <div class="col">{{ auction.highestBid ? (auction.highestBid.amount / auction.amount).toFixed(2) : "-" }}</div>
      <div class="col">{{ auction.highestBid ? auction.highestBid.generalName : "-" }}</div>
      <div class="col">{{ auction.closeDate }}</div>
    </div>
    <div v-if="selectedBuyRiceAuction !== undefined" class="row">
      <div class="col">{{ selectedBuyRiceAuction.id }}번 쌀 {{ selectedBuyRiceAuction.amount }} 경매에</div>
      <div class="col">
        <NumberInputWithInfo
          v-model="bidAmountBuyRiceAuction"
          :int="true"
          :min="selectedBuyRiceAuction.startBidAmount"
          :max="selectedBuyRiceAuction.finishBidAmount"
          title="금"
          :step="10"
        ></NumberInputWithInfo>
      </div>
      <div class="col"><BButton @click="bidBuyRiceAuction">입찰</BButton></div>
    </div>

    <div style="background-color: skyblue">쌀 판매</div>
    <div class="row gx-0">
      <div class="col">번호</div>
      <div class="col">판매자</div>
      <div class="col">물품</div>
      <div class="col">수량</div>
      <div class="col">시작 구매가</div>
      <div class="col">현재 구매가</div>
      <div class="col">즉시 구매가</div>
      <div class="col">단가</div>
      <div class="col">구매 예정자</div>
      <div class="col">거래 종료</div>
    </div>
    <div v-for="auction of sellRice" :key="auction.id" class="row gx-0" @click="selectedSellRiceAuction = auction">
      <div class="col">{{ auction.id }}</div>
      <div class="col">{{ auction.hostName }}</div>
      <div class="col">금</div>
      <div class="col">{{ auction.amount }}</div>
      <div class="col">{{ auction.startBidAmount }}</div>
      <div class="col">{{ auction.highestBid ? auction.highestBid.amount : "-" }}</div>
      <div class="col">{{ auction.finishBidAmount }}</div>
      <div class="col">{{ auction.highestBid ? (auction.highestBid.amount / auction.amount).toFixed(2) : "-" }}</div>
      <div class="col">{{ auction.highestBid ? auction.highestBid.generalName : "-" }}</div>
      <div class="col">{{ auction.closeDate }}</div>
    </div>
    <div v-if="selectedSellRiceAuction !== undefined" class="row">
      <div class="col">{{ selectedSellRiceAuction.id }}번 금 {{ selectedSellRiceAuction.amount }} 경매에</div>
      <div class="col">
        <NumberInputWithInfo
          v-model="bidAmountSellRiceAuction"
          :int="true"
          :min="selectedSellRiceAuction.startBidAmount"
          :max="selectedSellRiceAuction.finishBidAmount"
          title="쌀"
          :step="10"
        ></NumberInputWithInfo>
      </div>
      <div class="col"><BButton @click="bidSellRiceAuction">입찰</BButton></div>
    </div>

    <div>경매 등록</div>
    <div class="row">
      <div class="col">
        매물
        <BButtonGroup>
          <BButton :pressed="openAuctionInfo.type == 'buyRice'" @click="openAuctionInfo.type = 'buyRice'"> 쌀 </BButton>
          <BButton :pressed="openAuctionInfo.type == 'sellRice'" @click="openAuctionInfo.type = 'sellRice'">
            금
          </BButton>
        </BButtonGroup>
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="openAuctionInfo.closeTurnCnt"
          :int="true"
          :min="3"
          :max="24"
          title="기간(턴)"
          :step="1"
        ></NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="openAuctionInfo.amount"
          :int="true"
          :min="100"
          :max="10000"
          title="수량"
          :step="10"
        ></NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="openAuctionInfo.startBidAmount"
          :int="true"
          :min="100"
          :max="10000"
          title="시작구매가"
          :step="10"
        ></NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="openAuctionInfo.finishBidAmount"
          :int="true"
          :min="100"
          :max="10000"
          title="즉시구매가"
          :step="10"
        ></NumberInputWithInfo>
      </div>
      <div class="col">
        <BButton @click="openAuction">등록</BButton>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { BasicResourceAuctionInfo } from "@/defs/API/Auction";
import { SammoAPI } from "@/SammoAPI";
import { unwrap } from "@/util/unwrap";
import { useToast, BButtonGroup, BButton } from "bootstrap-vue-3";
import { isString } from "lodash";
import { onMounted, reactive, ref, watch } from "vue";
import NumberInputWithInfo from "@/components/NumberInputWithInfo.vue";
const toasts = unwrap(useToast());

const buyRice = ref<BasicResourceAuctionInfo[]>([]);
const sellRice = ref<BasicResourceAuctionInfo[]>([]);

const selectedBuyRiceAuction = ref<BasicResourceAuctionInfo | undefined>(undefined);
const bidAmountBuyRiceAuction = ref<number>(0);

watch(selectedBuyRiceAuction, (auction) => {
  if (!auction) {
    return;
  }
  bidAmountBuyRiceAuction.value = auction.highestBid ? auction.highestBid.amount : auction.startBidAmount;
});


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
})

onMounted(async () => {
  void refresh();
  console.log("mounted");
});
</script>
