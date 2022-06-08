<template>
  <div class="bg0">
    <div class="bg2">유니크 경매장</div>
    <div>내 가명: {{ obfuscatedName }}</div>
    <template v-if="currentAuction !== undefined">
      <div class="bg1">상세</div>
      <div class="row gx-0">
        <div class="col-4 col-md-2 bg2">경매 번호</div>
        <div class="col-4 col-md-2">{{ currentAuction.auction.id }}</div>

        <div class="col-4 col-md-2 bg2">경매명</div>
        <div class="col-4 col-md-2">{{ currentAuction.auction.title }}</div>

        <div class="col-4 col-md-2 bg2">주최자(익명)</div>
        <div class="col-4 col-md-2">{{ currentAuction.auction.hostName }}</div>

        <div class="col-4 col-md-2 bg2">종료시점</div>
        <div class="col-4 col-md-2">{{ currentAuction.auction.closeDate }}</div>

        <div class="col-4 col-md-2 bg2">최대지연</div>
        <div class="col-4 col-md-2">{{ currentAuction.auction.availableLatestBidCloseDate }}</div>
      </div>
      <div class="bg1">입찰자 목록</div>
      <div class="row gx-0 bg2">
        <div class="col-4">입찰자</div>
        <div class="col-4">입찰포인트</div>
        <div class="col-4">시각</div>
      </div>
      <div v-for="bidder of currentAuction.bidList" :key="bidder.amount" class="row gx-0">
        <div class="col-4">{{ bidder.generalName }}</div>
        <div class="col-4">{{ bidder.amount }}</div>
        <div class="col-4">{{ bidder.date }}</div>
      </div>
      <div class="bg1">입찰하기</div>
      <div class="row">
        <div class="col">
          <NumberInputWithInfo
            v-model="bidAmount"
            :int="true"
            :min="currentAuction.bidList[0].amount"
            title="유산포인트"
            :step="1"
          ></NumberInputWithInfo>
        </div>
        <div class="col"><BButton @click="bidAuction">입찰</BButton></div>
      </div>
    </template>

    <div class="bg1">목록</div>
    <div v-for="[auctionID, auction] of auctionList" :key="auctionID" class="row" @click="currentAuctionID = auctionID">
      <div class="col">{{ auction.id }}</div>
      <div class="col">{{ auction.finished ? "종료됨" : "진행중" }}</div>
      <div class="col">{{ auction.title }}</div>
      <div class="col">{{ auction.hostName }}</div>
      <div class="col">{{ auction.closeDate }}</div>
      <div class="col">{{ auction.remainCloseDateExtensionCnt > 0 ? "남음" : "소진" }}</div>
      <div class="col">{{ auction.highestBid.generalName }}</div>
      <div class="col">{{ auction.highestBid.amount }}</div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { UniqueItemAuctionDetail, UniqueItemAuctionList } from "@/defs/API/Auction";
import { SammoAPI } from "@/SammoAPI";
import { unwrap } from "@/util/unwrap";
import { useToast, BButton } from "bootstrap-vue-3";
import { isString } from "lodash";
import { onMounted, ref, watch } from "vue";
import NumberInputWithInfo from "@/components/NumberInputWithInfo.vue";

type AuctionItemInfo = UniqueItemAuctionList["list"][0];

const currentAuctionID = ref<number>();
const currentAuction = ref<UniqueItemAuctionDetail | undefined>(undefined);
const bidAmount = ref<number>(5000);

async function refreshDetail() {
  if (currentAuctionID.value === undefined) {
    return;
  }
  const auctionID = currentAuctionID.value;
  try {
    currentAuction.value = await SammoAPI.Auction.GetUniqueItemAuctionDetail({ auctionID });
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      unwrap(useToast()).danger({
        title: "에러",
        body: e,
      });
    }
  }
}
watch(currentAuctionID, () => {
  void refreshDetail();
});
const auctionList = ref(new Map<number, AuctionItemInfo>());
const obfuscatedName = ref("");

const toasts = unwrap(useToast());

async function refresh() {
  try {
    const result = await SammoAPI.Auction.GetUniqueItemAuctionList();
    obfuscatedName.value = result.obfuscatedName;
    auctionList.value = new Map(result.list.map((auction) => [auction.id, auction]));
    if (currentAuctionID.value === undefined && result.list.length > 0) {
      currentAuctionID.value = result.list[0].id;
      bidAmount.value = result.list[0].highestBid.amount;
    }
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

async function bidAuction() {
  if (currentAuction.value === undefined) {
    return;
  }

  const amount = bidAmount.value;
  const auctionInfo = currentAuction.value.auction;

  if (confirm(`${auctionInfo.title}에 ${amount}유산포인트를 입찰하시겠습니까?`)) {
    try {
      await SammoAPI.Auction.BidUniqueAuction({ auctionID: auctionInfo.id, amount });
      toasts.success({
        title: "성공",
        body: "입찰이 완료되었습니다.",
      });
      void refreshDetail();
      void refresh();
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
}

onMounted(() => {
  void refresh();
});
</script>
