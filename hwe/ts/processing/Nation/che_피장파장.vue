<template>
  <TopBackBar v-model:searchable="searchable" :title="commandName" type="chief" />
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
    <div>
      선택된 국가에 피장파장을 발동합니다.<br />
      지정한 전략을 상대국이
      {{ delayCnt }}턴 동안 사용할 수 없게됩니다.<br />
      대신 아국은 지정한 전략을 {{ postReqTurn }}턴 동안 사용할 수 없습니다.<br />
      선포, 전쟁중인 상대국에만 가능합니다.<br />
      상대 국가를 목록에서 선택하세요.<br />
      현재 피장파장이 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div class="row">
      <div class="col-6 col-md-3">
        국가 :
        <SelectNation v-model="selectedNationID" :nations="nationList" :searchable="searchable" />
      </div>
      <div class="col-3 col-md-2">
        <label>전략 :</label>
        <b-form-select v-model="selectedCommandID" :options="commandTypesOption" />
      </div>
      <div class="col-3 col-md-2 d-grid">
        <b-button @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" type="chief" />
</template>

<script lang="ts">
declare const staticValues: {
  serverNick: string;
  serverID: string;
  mapName: string;
  commandName: string;
};

declare const procRes: {
  nationList: procNationList;
  startYear: number;
  delayCnt: number;
  postReqTurn: number;
  availableCommandTypeList: Record<
    number,
    {
      name: string;
      remainTurn: number;
    }
  >;
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>
<script lang="ts" setup>
import MapViewer, { type CityPositionMap, type MapCityParsed, type MapCityParsedRaw } from "@/components/MapViewer.vue";
import SelectNation from "@/processing/SelectNation.vue";
import { ref, watch, onMounted, provide } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { getProcSearchable, type procNationItem, type procNationList } from "../processingRes";
import type { MapResult } from "@/defs";
import { SammoAPI } from "@/SammoAPI";
import { getGameConstStore, type GameConstStore } from "@/GameConstStore";

const serverNick = staticValues.serverNick;
const serverID = staticValues.serverID;

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


const nationList = new Map<number, procNationItem>();
for (const nationItem of procRes.nationList) {
  nationList.set(nationItem.id, nationItem);
}

const selectedNationID = ref(procRes.nationList[0].id);

const map = ref<MapResult>();

const delayCnt = procRes.delayCnt;
const postReqTurn = procRes.postReqTurn;

const commandTypesOption: { html: string; value: string }[] = [];
for (const [commandTypeID, commandTypeInfo] of Object.entries(procRes.availableCommandTypeList)) {
  const notAvailable = commandTypeInfo.remainTurn > 0;
  const notAvailableText = notAvailable ? " (불가)" : "";
  const name = `${commandTypeInfo.name}${notAvailableText}`;
  const html = notAvailable ? `<span style='color:red;'>${name}</span>` : name;
  commandTypesOption.push({
    html,
    value: commandTypeID,
  });
}

const selectedCommandID = ref(Object.keys(procRes.availableCommandTypeList)[0]);

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      destNationID: selectedNationID.value,
      commandType: selectedCommandID.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}

const searchable = getProcSearchable();

const selectedCityObj = ref<MapCityParsed>();
const commandName = ref(staticValues.commandName);

watch(selectedCityObj, (city?: MapCityParsed) => {
  if (city === undefined) {
    return;
  }
  if (city.nationID === undefined) {
    return;
  }
  selectedNationID.value = city.nationID;
});

onMounted(async () => {
  try {
    map.value = await SammoAPI.Global.GetMap({ neutralView: 0, showMe: 1 });
  } catch (e) {
    console.error(e);
  }
});
</script>
