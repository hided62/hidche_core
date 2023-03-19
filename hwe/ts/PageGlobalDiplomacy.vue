<template>
  <!-- eslint-disable vue/no-v-html -->
  <BContainer v-if="asyncReady" id="container" :toast="{ root: true }" class="pageGlobalDiplomacy">
    <TopBackBar title="중원 정보"></TopBackBar>
    <div class="diplomacy bg0">
      <div class="s-border-tb center tb-title" style="background-color: blue">외교 현황</div>
      <div class="diplomacy_area">
        <table v-if="diplomacy" class="center" style="margin: auto; min-width: 400px">
          <thead>
            <tr>
              <th></th>
              <th
                v-for="nation of diplomacy.nations"
                :key="nation.nation"
                class="thead-nation"
                :style="{
                  color: isBrightColor(nation.color) ? '#000' : '#fff',
                  backgroundColor: nation.color,
                }"
              >
                {{ nation.name }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="me of diplomacy.nations" :key="me.nation">
              <th
                class="tbody-nation"
                :style="{
                  color: isBrightColor(me.color) ? '#000' : '#fff',
                  backgroundColor: me.color,
                }"
              >
                {{ me.name }}
              </th>
              <template v-for="you of diplomacy.nations" :key="you.nation">
                <td v-if="me.nation == you.nation" class="tbody-cell">＼</td>
                <td
                  v-else-if="me.nation == diplomacy.myNationID || you.nation == diplomacy.myNationID"
                  class="tbody-cell"
                  style="background-color: #660000"
                  v-html="infomativeStateCharMap[diplomacy.diplomacyList[me.nation][you.nation]]"
                />
                <td
                  v-else
                  class="tbody-cell"
                  v-html="neutralStateCharMap[diplomacy.diplomacyList[me.nation][you.nation]]"
                />
              </template>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td :colspan="Object.keys(diplomacy.nations).length + 1" class="center">
                불가침 :
                <span style="color: limegreen">@</span>, 통상 : ㆍ, 선포 : <span style="color: magenta">▲</span>, 교전 :
                <span style="color: red">★</span>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div v-if="diplomacy && diplomacy.conflict.length > 0" class="conflict-area gx-0 bg0">
      <div class="s-border-tb center tb-title" style="background-color: magenta">분쟁 현황</div>
      <div v-for="[cityID, conflictNations] of diplomacy.conflict" :key="cityID" class="row gx-0">
        <div class="conflictCityName">{{ gameConstStore?.cityConst[cityID].name }}</div>
        <div class="conflictNation col">
          <div
            v-for="[nation, percent] of Object.entries(conflictNations).map(
              ([nationID, percent])=>[nationMap.get(parseInt(nationID)),percent] as [SimpleNationObj, number]
            )"
            :key="nation.nation"
            class="row gx-0"
          >
            <div
              class="conflictNationName"
              :style="{
                color: isBrightColor(nation.color) ? '#000' : '#fff',
                backgroundColor: nation.color,
                flexBasis: '16ch',
                paddingLeft: '1ch',
              }"
            >
              {{ nation.name }}
            </div>
            <div
              class="conflictNationPercent"
              :style="{
                flexBasis: '6ch',
                textAlign: 'right',
                paddingRight: '0.5ch',
              }"
            >
              {{ percent.toLocaleString(undefined, { minimumFractionDigits: 1 }) }}%
            </div>
            <div class="col align-self-center">
              <div
                class="progress"
                :style="{
                  width: `${percent}%`,
                  marginLeft: 0,
                  height: '1.2em',
                  backgroundColor: nation.color,
                }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="map_area mx-0 bg0">
      <div class="s-border-tb center tb-title" style="background-color: green">중원 지도</div>
      <div class="row g-0">
        <div class="map_position">
          <MapViewer
            v-if="map"
            :server-nick="serverNick"
            :serverID="serverID"
            :map-name="unwrap(gameConstStore?.gameConst.mapName)"
            :map-data="map"
            :is-detail-map="true"
            :city-position="cityPosition"
            :format-city-info="formatCityInfoText"
            :image-path="imagePath"
            :disallow-click="true"
          />
        </div>
        <div class="nation_position"><SimpleNationList v-if="diplomacy" :nations="diplomacy.nations" /></div>
      </div>
    </div>
    <BottomBar></BottomBar>
  </BContainer>
</template>

<script lang="ts">
declare const staticValues: {
  serverNick: string;
  serverID: string;
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>
<script lang="ts" setup>
import { onMounted, provide, ref, watch } from "vue";
import { BContainer, useToast } from "bootstrap-vue-next";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { SammoAPI } from "./SammoAPI";
import SimpleNationList from "./components/SimpleNationList.vue";
import MapViewer, { type CityPositionMap, type MapCityParsedRaw, type MapCityParsed } from "./components/MapViewer.vue";
import { getGameConstStore, type GameConstStore } from "./GameConstStore";
import { unwrap } from "@/util/unwrap";
import type { SimpleNationObj, diplomacyState, MapResult } from "./defs";
import type { GetDiplomacyResponse } from "./defs/API/Global";
import { isString } from "lodash-es";
import { isBrightColor } from "@/util/isBrightColor";

const serverID = staticValues.serverID;
const serverNick = staticValues.serverNick;

const asyncReady = ref<boolean>(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

const infomativeStateCharMap: Record<diplomacyState, string> = {
  0: '<span style="color:red;">★</span>',
  1: '<span style="color:magenta;">▲</span>',
  2: "ㆍ",
  7: '<span style="color:green;">@</span>',
};

const neutralStateCharMap: Record<diplomacyState, string> = {
  0: '<span style="color:red;">★</span>',
  1: '<span style="color:magenta;">▲</span>',
  2: "",
  7: "에러",
};

const cityPosition = getCityPosition();
const formatCityInfoText = formatCityInfo;
const imagePath = window.pathConfig.gameImage;

const map = ref<MapResult>();
const diplomacy = ref<GetDiplomacyResponse>();

const nationMap = ref(new Map<number, SimpleNationObj>());

const toasts = unwrap(useToast());

onMounted(async () => {
  try {
    map.value = await SammoAPI.Global.GetMap({
      neutralView: 0,
      showMe: 1,
    });
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

watch(diplomacy, (diplomacy) => {
  if (diplomacy === undefined) {
    return;
  }
  const map = new Map<number, SimpleNationObj>();
  for (const nation of diplomacy.nations) {
    map.set(nation.nation, nation);
  }
  nationMap.value = map;
});

onMounted(async () => {
  try {
    diplomacy.value = await SammoAPI.Global.GetDiplomacy();
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
</script>

<style lang="scss" scoped>
* {
  font-size: 14px;
}
.diplomacy_area {
  width: 100%;
  overflow-x: auto;
}
.thead-nation {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  text-align: end;
  padding-bottom: 1ch;
  padding-top: 1ch;
  padding-left: 0;
  padding-right: 0;
  min-width: 1ch;
  max-width: 3ch;
  font-weight: normal;
}
.tbody-nation {
  text-align: end;
  padding-right: 1ch;
  padding-left: 1ch;
  min-width: 10ch;
  font-weight: normal;
}
.diplomacy {
  margin-top: 1.5em;
}

.conflict-area {
  margin-top: 1.5em;
}

.map_area {
  margin-top: 1.5em;
}

.tb-title {
  font-size: 1.2em;
}

.tbody-cell {
  border-left: solid 1px gray;
  border-top: solid 1px gray;
  padding: 0;
}

.conflictCityName {
  flex-basis: 16ch;
  text-align: right;
  padding-right: 1ch;
  align-self: center;
}
</style>
