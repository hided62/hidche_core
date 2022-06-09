<template>
  <div class="bg0">
    <div>
      내 가명: <span class="isMe">{{ obfuscatedName }}</span>
    </div>
    <template v-if="currentAuction !== undefined">
      <div class="bg2">경매 {{ currentAuction.auction.id }}번 상세</div>
      <div class="row gx-0 text-center">
        <div class="col-2 col-md-1 bg1">경매명</div>
        <div class="col-4 col-md-2">{{ currentAuction.auction.title }}</div>

        <div class="col-2 col-md-1 bg1">주최자(익명)</div>
        <div :class="['col-4 col-md-2', currentAuction.auction.isCallerHost ? 'isMe' : '']">
          {{ currentAuction.auction.hostName }}
        </div>

        <div class="col-2 col-md-1 bg1">종료일시</div>
        <div class="col-4 col-md-2 f_tnum">{{ cutDateTime(currentAuction.auction.closeDate, true) }}</div>

        <div class="col-2 col-md-1 bg1">최대지연</div>
        <div class="col-4 col-md-2 f_tnum">
          {{ cutDateTime(currentAuction.auction.availableLatestBidCloseDate, true) }}
        </div>
      </div>
      <div class="bg1">입찰자 목록</div>
      <div
        class="row gx-0 px-md-5 text-center"
        :style="{
          borderBottom: 'solid 1px white',
        }"
      >
        <div class="col-4 offset-md-2 col-md-3">입찰자</div>
        <div class="col-4 col-md-2 text-end px-5">입찰포인트</div>
        <div class="col-4 col-md-3">시각</div>
      </div>
      <div v-for="bidder of currentAuction.bidList" :key="bidder.amount" class="row gx-0 px-md-5 text-center">
        <div :class="['col-4 offset-md-2 col-md-3', bidder.isCallerHighestBidder ? 'isMe' : '']">{{ bidder.generalName }}</div>
        <div class="col-4 col-md-2 text-end px-5 f_tnum">{{ bidder.amount.toLocaleString() }}</div>
        <div class="col-4 col-md-3 f_tnum">{{ cutDateTime(bidder.date) }}</div>
      </div>
      <div class="bg1">입찰하기</div>
      <div class="row">
        <label class="col-5 offset-md-3 col-md-3 col-form-label text-center">유산포인트 (잔여: {{ currentAuction.remainPoint.toLocaleString() }}포인트)</label>
        <div class="col-4 col-md-2">
          <NumberInputWithInfo
            v-model="bidAmount"
            :int="true"
            :min="currentAuction.bidList[0].amount"
            :max="currentAuction.remainPoint"
            title=""
            :step="1"
          ></NumberInputWithInfo>
        </div>
        <div class="col-3 col-md-1 d-grid"><BButton @click="bidAuction">입찰</BButton></div>
      </div>
    </template>

    <div class="bg1">진행중인 경매 목록</div>
    <div
      class="row gx-0 text-center"
      :style="{
        borderBottom: 'solid 1px white',
      }"
    >
      <div class="col-1">번호</div>
      <div class="col-4">경매명</div>
      <div class="col-1">주최자</div>
      <div class="col-2">종료일시</div>
      <div class="col-1">연장</div>
      <div class="col-1">1순위</div>
      <div class="col-2 text-end px-2">포인트</div>
    </div>
    <div
      v-for="[auctionID, auction] of ongoingAuctionList"
      :key="auctionID"
      class="row gx-0 text-center clickableRow"
      @click="currentAuctionID = auctionID"
    >
      <div class="col-1">{{ auction.id }}</div>
      <div class="col-4">{{ auction.title }}</div>
      <div :class="['col-1', auction.isCallerHost ? 'isMe' : '']">{{ auction.hostName }}</div>
      <div class="col-2 f_tnum">{{ cutDateTime(auction.closeDate) }}</div>
      <div class="col-1">{{ auction.remainCloseDateExtensionCnt > 0 ? "남음" : "소진" }}</div>
      <div :class="['col-1', auction.highestBid.isCallerHighestBidder ? 'isMe' : '']">
        {{ auction.highestBid.generalName }}
      </div>
      <div class="col-2 text-end px-2 f_tnum">{{ auction.highestBid.amount.toLocaleString() }}</div>
    </div>
    <div class="bg1">종료된 경매 목록</div>
    <div
      class="row gx-0 text-center"
      :style="{
        borderBottom: 'solid 1px white',
      }"
    >
      <div class="col-1">번호</div>
      <div class="col-4">경매명</div>
      <div class="col-1">주최자</div>
      <div class="col-2">종료일시</div>
      <div class="col-1">연장</div>
      <div class="col-1">1순위</div>
      <div class="col-2 text-end px-2">포인트</div>
    </div>
    <div
      v-for="[auctionID, auction] of finishedAuctionList"
      :key="auctionID"
      class="row gx-0 text-center clickableRow"
      @click="currentAuctionID = auctionID"
    >
      <div class="col-1">{{ auction.id }}</div>
      <div class="col-4">{{ auction.title }}</div>
      <div :class="['col-1', auction.isCallerHost ? 'isMe' : '']">{{ auction.hostName }}</div>
      <div class="col-2 f_tnum">{{ cutDateTime(auction.closeDate) }}</div>
      <div class="col-1">{{ auction.remainCloseDateExtensionCnt > 0 ? "남음" : "소진" }}</div>
      <div :class="['col-1', auction.highestBid.isCallerHighestBidder ? 'isMe' : '']">
        {{ auction.highestBid.generalName }}
      </div>
      <div class="col-2 text-end px-2 f_tnum">{{ auction.highestBid.amount.toLocaleString() }}</div>
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
const ongoingAuctionList = ref(new Map<number, AuctionItemInfo>());
const finishedAuctionList = ref(new Map<number, AuctionItemInfo>());
const obfuscatedName = ref("");

const toasts = unwrap(useToast());

function cutDateTime(dateTime: string, showSecond = false) {
  if (showSecond) {
    return dateTime.substring(5, 19);
  }
  return dateTime.substring(5, 16);
}

async function refreshList() {
  try {
    const result = await SammoAPI.Auction.GetUniqueItemAuctionList();
    obfuscatedName.value = result.obfuscatedName;
    finishedAuctionList.value = new Map(
      result.list.filter((auction) => auction.finished).map((auction) => [auction.id, auction])
    );
    ongoingAuctionList.value = new Map(
      result.list.filter((auction) => !auction.finished).map((auction) => [auction.id, auction])
    );
    if (currentAuctionID.value === undefined && ongoingAuctionList.value.size > 0) {
      const auctionIterator = ongoingAuctionList.value.values().next();
      if (!auctionIterator.done) {
        currentAuctionID.value = auctionIterator.value.id;
        bidAmount.value = auctionIterator.value.highestBid.amount;
      }
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
}

async function refresh() {
  const waiters = [refreshList(), refreshDetail()];
  await Promise.all(waiters);
}

defineExpose({
  refresh,
});

onMounted(() => {
  void refreshList();
});
</script>

<style>
.isMe {
  font-weight: bold;
  color: aquamarine;
}

.clickableRow{
  cursor: pointer;
}

.clickableRow:hover{
  background-color: rgba(255, 255, 255, 0.3);
}
</style>
