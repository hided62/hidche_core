<template>
  <BContainer v-if="asyncReady" id="container" :toast="{ root: true }">
    <div class="card">
      <h3 class="card-header">{{ serverName }} 현황</h3>
      <MapViewer
        v-if="cachedMap"
        :server-nick="serverNick"
        :serverID="serverID"
        :map-name="unwrap(gameConstStore?.gameConst.mapName)"
        :model-value="cachedMap"
        :is-detail-map="true"
        :city-position="cityPosition"
        :format-city-info="formatCityInfoText"
        :image-path="imagePath"
        :disallow-click="true"
      />
      <div v-if="cachedMap" class="card-body">
        <template v-for="(item, idx) in cachedMap.history" :key="idx">
          <span v-html="formatLog(item)" />
          <br />
        </template>
      </div>
    </div>
  </BContainer>
</template>

<script lang="ts">
declare const staticValues: {
  serverName: string;
  serverNick: string;
  serverID: string;
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>
<script lang="ts" setup>
import { onMounted, provide, ref } from "vue";
import { BContainer } from "bootstrap-vue-3";
import { SammoAPI } from "./SammoAPI";
import { formatLog } from "./utilGame/formatLog";
import MapViewer, { type CityPositionMap, type MapCityParsedRaw, type MapCityParsed } from "./components/MapViewer.vue";
import { getGameConstStore, type GameConstStore } from "./GameConstStore";
import { unwrap } from "@/util/unwrap";
import type { CachedMapResult } from "./defs";

const serverName = staticValues.serverName;
const serverNick = staticValues.serverNick;
const serverID = staticValues.serverID;

const asyncReady = ref<boolean>(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

const cityPosition = getCityPosition();
const formatCityInfoText = formatCityInfo;
const imagePath = window.pathConfig.gameImage;

const cachedMap = ref<CachedMapResult>();

onMounted(async () => {
  try {
    cachedMap.value = await SammoAPI.Global.GetCachedMap();
  } catch (e) {
    console.error(e);
  }
});
</script>

<style lang="scss">
@import "@/../scss/common/bootstrap5.scss";
@include media-1000px {
  #container {
    width: 700px;
  }
}

@include media-500px {
  #container {
    width: 500px;
  }
}
</style>
