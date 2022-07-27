<template>
  <BContainer v-if="asyncReady" id="container" :toast="{ root: true }" class="bg0">
    <TopBackBar type="close" :title="isResAuction ? '경매장' : '유니크 경매장'" :reloadable="true" @reload="tryReload">
      <BButton @click="isResAuction = true">금/쌀</BButton>
      <BButton @click="isResAuction = false">유니크</BButton>
    </TopBackBar>
    <AuctionResource v-if="isResAuction" ref="auctionResource"></AuctionResource>
    <AuctionUniqueItem v-else ref="auctionUniqueItem"></AuctionUniqueItem>
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
import { provide, ref } from "vue";
import { getGameConstStore, type GameConstStore } from "./GameConstStore";

const props = defineProps({
  isResAuction: {
    type: Boolean,
    default: true,
  },
});

const asyncReady = ref(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

const auctionResource = ref<InstanceType<typeof AuctionResource> | null>(null);
const auctionUniqueItem = ref<InstanceType<typeof AuctionUniqueItem> | null>(null);

const isResAuction = ref(props.isResAuction);

async function tryReload() {
  console.log(auctionResource.value);
  console.log(auctionUniqueItem.value);
  if(isResAuction.value && auctionResource.value){
    await auctionResource.value.refresh();
  }
  if(!isResAuction.value && auctionUniqueItem.value){
    await auctionUniqueItem.value.refresh();
  }
}
</script>
