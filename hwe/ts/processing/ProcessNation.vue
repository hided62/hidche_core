<template>
  <TopBackBar v-model:searchable="searchable" :title="commandName" :type="procEntryMode" />
  <div v-if="asyncReady" class="bg0">
    <MapViewer
      v-if="map"
      v-model="selectedCityObj"
      :server-nick="serverNick"
      :serverID="serverID"
      :map-name="unwrap(gameConstStore?.gameConst.mapName)"
      :mapData="map"
      :isDetailMap="false"
      :cityPosition="cityPosition"
      :formatCityInfo="formatCityInfoText"
      :image-path="imagePath"
    />

    <div v-if="commandName == '선전포고'">
      타국에게 선전 포고합니다.<br />
      선전 포고할 국가를 목록에서 선택하세요.<br />
      고립되지 않은 아국 도시에서 인접한 국가에 선포 가능합니다.<br />
      초반제한 해제 2년전부터 선포가 가능합니다. ({{ startYear + 1 }}년 1월부터 가능)<br />
      현재 선포가 불가능한 국가는 배경색이
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '급습'">
      선택된 국가에 급습을 발동합니다.<br />
      선포, 전쟁중인 상대국에만 가능합니다.<br />
      상대 국가를 목록에서 선택하세요.<br />
      현재 급습이 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '불가침 파기 제의'">
      불가침중인 국가에 조약 파기를 제의합니다.<br />
      제의할 국가를 목록에서 선택하세요.<br />
      현재 제의가 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '이호경식'">
      선택된 국가에 이호경식을 발동합니다.<br />
      선포, 전쟁중인 상대국에만 가능합니다.<br />
      상대 국가를 목록에서 선택하세요.<br />
      현재 이호경식이 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '종전 제의'">
      전쟁중인 국가에 종전을 제의합니다.<br />
      제의할 국가를 목록에서 선택하세요.<br />
      현재 제의가 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '허보'">
      전쟁중인 국가에 종전을 제의합니다.<br />
      제의할 국가를 목록에서 선택하세요.<br />
      현재 제의가 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div class="row">
      <div class="col-6 col-lg-3">
        국가 :
        <SelectNation v-model="selectedNationID" :nations="nationList" :searchable="searchable" />
      </div>
      <div class="col-4 col-lg-2 d-grid">
        <b-button @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" :type="procEntryMode" />
</template>

<script lang="ts">
declare const staticValues: {
  serverNick: string;
  serverID: string;
  commandName: string;
  entryInfo: ["General" | "Nation", unknown];
};
declare const procRes: {
  nationList: procNationList;
  startYear: number;
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>
<script lang="ts" setup>
import MapViewer, { type CityPositionMap, type MapCityParsed, type MapCityParsedRaw } from "@/components/MapViewer.vue";
import SelectNation from "@/processing/SelectNation.vue";
import { ref, type Ref, watch, onMounted, provide } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { getProcSearchable, type procNationItem, type procNationList } from "./processingRes";
import { getGameConstStore, type GameConstStore } from "@/GameConstStore";
import { SammoAPI } from "@/SammoAPI";
import type { MapResult } from "@/defs";

const serverNick = staticValues.serverNick;
const serverID = staticValues.serverID;
const startYear = procRes.startYear;

const cityPosition = getCityPosition();
const formatCityInfoText = formatCityInfo;
const imagePath = window.pathConfig.gameImage;

const asyncReady = ref<boolean>(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

const nationList = ref(new Map<number, procNationItem>());

watch(
  () => procRes.nationList,
  (newNationList) => {
    const tmpNationList = new Map<number, procNationItem>();
    for (const nationItem of newNationList) {
      tmpNationList.set(nationItem.id, nationItem);
    }
    nationList.value = tmpNationList;
  },
  { immediate: true }
);

const selectedNationID = ref(procRes.nationList[0].id);

const map = ref<MapResult>();

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      destNationID: selectedNationID.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}

const searchable = getProcSearchable();

const procEntryMode: Ref<"chief" | "normal"> = ref(staticValues.entryInfo[0] == "Nation" ? "chief" : "normal");
const selectedCityObj = ref<MapCityParsed>();
const commandName = ref(staticValues.commandName);

watch(selectedCityObj, (city?: MapCityParsed) => {
  if (city === undefined) {
    return;
  }
  if(city.nationID === undefined){
    return;
  }

  selectedNationID.value = city.nationID;
});

onMounted(async () => {
  try{
    map.value = await SammoAPI.Global.GetMap({neutralView:0, showMe: 1});
  }
  catch(e){
    console.error(e);
  }
});
</script>
