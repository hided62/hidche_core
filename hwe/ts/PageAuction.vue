<template>
  <BContainer id="container" :toast="{ root: true }" class="bg0">
    <TopBackBar type="close" :title="isResAuction ? '경매장' : '유니크 경매장'" :reloadable="true" @reload="tryReload">
      <BButton @click="isResAuction = true">금/쌀</BButton>
      <BButton @click="isResAuction = false">유니크</BButton>
    </TopBackBar>
    <AuctionResource v-if="isResAuction"></AuctionResource>
    <AuctionUniqueItem v-else></AuctionUniqueItem>
    <BottomBar type="close"></BottomBar>
  </BContainer>
</template>
<script lang="ts">
/*declare const staticValues: {
    serverID: string,
    turnterm: number,
    serverNick: string,
};*/
</script>
<script lang="ts" setup>
import { BButton, BContainer } from "bootstrap-vue-3";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "./components/BottomBar.vue";
import AuctionResource from "@/components/AuctionResource.vue";
import AuctionUniqueItem from "@/components/AuctionUniqueItem.vue";
import { ref } from "vue";

const isResAuction = ref(true);

function tryReload() {
  console.log('갱신');
}
</script>
