<template>
  <div id="outBlock" :class="`sam-color-${nationStaticInfo?.color.substring(1, 7) ?? '000000'}`">
    <div id="outBlock2">
      <!-- eslint-disable-next-line vue/max-attributes-per-line -->
      <BContainer v-if="asyncReady" id="container" :position="'position-relative'" :toast="{ root: true }" class="bg0">
        <main class="gx-0">
          <div class="commonToolbar">
            <GlobalMenu
              v-if="globalMenu && globalInfo"
              :globalInfo="globalInfo"
              :modelValue="globalMenu"
              variant="sammo-base2"
            />
          </div>
          <GameInfo
            v-if="frontInfo"
            :frontInfo="frontInfo"
            :serverLocked="serverLocked"
            :serverName="serverName"
            :lastExecuted="lastExecuted"
          />
          <div class="s-border-t px-2 py-2 onlineNations">접속중인 국가: {{ globalInfo.onlineNations }}</div>
          <div class="s-border-t px-2 py-2 onlineUsers">【 접속자 】 {{ nationInfo?.onlineGen }}</div>
          <div class="s-border-t py-2 nationNotice">
            <div class="px-2">【 국가방침 】</div>
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div v-if="nationInfo" class="nationNoticeBody" v-html="nationInfo.notice?.msg ?? ''" />
          </div>
          <div id="ingameBoard">
            <!-- TODO: 운영자 툴바는 어디에?-->
            <div class="mapView">
              <MapViewer
                v-if="map"
                :serverNick="serverNick"
                :serverID="serverID"
                :mapName="unwrap(gameConstStore?.gameConst.mapName)"
                :isDetailMap="true"
                :cityPosition="cityPosition"
                :imagePath="imagePath"
                :formatCityInfo="formatCityInfoText"
                :mapData="map"
                :disallowClick="false"
                :genHref="genMapCityHref"
                @city-click="onCityClick"
              />
            </div>
            <div class="reservedCommandZone">
              <PartialReservedCommand id="reservedCommandPanel" ref="reservedCommandPanel" />
            </div>
            <div id="actionMiniPlate" class="gx-0 row">
              <div class="col">
                <div class="gx-1 row">
                  <div class="col-8 d-grid">
                    <button type="button" class="btn btn-sammo-base2" @click="tryRefresh">갱 신</button>
                  </div>
                  <div class="col-4 d-grid">
                    <button type="button" class="btn btn-sammo-base2" @click="moveLobby">로비로</button>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="frontInfo" class="cityInfo">
              <CityBasicCard :city="frontInfo.city" />
            </div>
            <div v-if="frontInfo" class="nationInfo">
              <NationBasicCard :nation="frontInfo.nation" :global="globalInfo" />
            </div>
            <div v-if="frontInfo && generalInfo && nationStaticInfo" class="generalInfo">
              <GeneralBasicCard
                :general="generalInfo"
                :nation="nationStaticInfo"
                :troopInfo="frontInfo.general.troopInfo"
                :turnTerm="globalInfo.turnterm"
                :lastExecuted="lastExecuted"
              />
            </div>

            <div class="generalCommandToolbar">
              <MainControlBar
                v-if="generalInfo"
                :permission="generalInfo.permission"
                :showSecret="showSecret"
                :myLevel="generalInfo.officerLevel"
                :nationLevel="nationStaticInfo?.level ?? 0"
                :nationColor="nationStaticInfo?.color.substring(1, 7) ?? '000000'"
                :isTournamentApplicationOpen="globalInfo.isTournamentApplicationOpen"
                :isBettingActive="globalInfo.isBettingActive"
              />
            </div>
            <div id="actionMiniPlateSub" class="gx-0 row">
              <div class="col">
                <div class="gx-1 row">
                  <div class="col-3 d-grid">
                    <button type="button" class="btn btn-dark" @click="scrollToSelector('#reservedCommandPanel')">
                      명령으로
                    </button>
                  </div>
                  <div class="col-5 d-grid">
                    <button type="button" class="btn btn-sammo-base2" @click="tryRefresh">갱 신</button>
                  </div>
                  <div class="col-4 d-grid">
                    <button type="button" class="btn btn-sammo-base2" @click="moveLobby">로비로</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="RecordZone row gx-0">
            <div class="PublicRecord col col-12 col-md-6">
              <div class="bg1 center s-border-tb title">장수 동향</div>
              <template v-for="[idx, rawText] of globalRecords.toArray()" :key="idx">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <div :v-data-idx="idx" v-html="formatLog(rawText)" />
              </template>
            </div>
            <div class="GeneralLog col col-12 col-md-6">
              <div class="bg1 center s-border-tb title">개인 기록</div>
              <template v-for="[idx, rawText] of generalRecords.toArray()" :key="idx">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <div :v-data-idx="idx" v-html="formatLog(rawText)" />
              </template>
            </div>
            <div class="WorldHistory col col-12">
              <div class="bg1 center s-border-tb title">중원 정세</div>
              <template v-for="[idx, rawText] of worldHistory.toArray()" :key="idx">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <div :v-data-idx="idx" v-html="formatLog(rawText)" />
              </template>
            </div>
          </div>

          <div class="commonToolbar">
            <GlobalMenu
              v-if="globalMenu && globalInfo"
              :globalInfo="globalInfo"
              :modelValue="globalMenu"
              variant="sammo-base2"
            />
          </div>
          <MessagePanel
            v-if="generalInfo"
            ref="msgPanel"
            :generalID="generalInfo.no"
            :generalName="generalInfo.name"
            :nationID="generalInfo.nation"
            :permissionLevel="generalInfo.permission"
          />
          <div class="commonToolbar">
            <GlobalMenu
              v-if="globalMenu && globalInfo"
              :globalInfo="globalInfo"
              :modelValue="globalMenu"
              variant="sammo-base2"
            />
          </div>
        </main>
      </BContainer>
      <div v-else>서버 갱신 중입니다.</div>
    </div>
    <GameBottomBar
      v-if="frontInfo && globalMenu"
      id="mobileBottomBar"
      :frontInfo="frontInfo"
      :globalMenu="globalMenu"
      @refresh="tryRefresh"
    />
  </div>
</template>
<script lang="ts">
declare const staticValues: {
  serverName: string;
  serverNick: string;
  serverID: string;
  mapName: string;
  unitSet: string;
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>
<script lang="ts" setup>
import { BContainer, BButton, useToast } from "bootstrap-vue-3";
import { isString } from "lodash-es";
import { computed, onMounted, provide, ref, watch } from "vue";
import { GameConstStore, getGameConstStore } from "./GameConstStore";
import { SammoAPI } from "./SammoAPI";
import { parseTime } from "./util/parseTime";
import { unwrap } from "./util/unwrap";
import Denque from "denque";
import { formatLog } from "@/utilGame/formatLog";
import type { GetFrontInfoResponse, GetMenuResponse } from "./defs/API/Global";
import { delay } from "./util/delay";
import CityBasicCard from "./components/CityBasicCard.vue";
import NationBasicCard from "./components/NationBasicCard.vue";
import GeneralBasicCard from "./components/GeneralBasicCard.vue";
import MessagePanel from "./components/MessagePanel.vue";
import type { MapResult, NationStaticItem } from "./defs";
import MapViewer, { type CityPositionMap, type MapCityParsed, type MapCityParsedRaw } from "./components/MapViewer.vue";
import PartialReservedCommand from "./PartialReservedCommand.vue";
import { scrollToSelector } from "./util/scrollToSelector";
import MainControlBar from "./components/MainControlBar.vue";
import GlobalMenu from "./components/GlobalMenu.vue";
import GameInfo from "./components/GameInfo.vue";
import GameBottomBar from "./components/GameBottomBar.vue";

const { serverName, serverNick, serverID } = staticValues;

const asyncReady = ref(false);
const gameConstStore = ref<GameConstStore>();

const toasts = unwrap(useToast());

provide("gameConstStore", gameConstStore);

const lastExecuted = ref<Date>(parseTime("2022-08-15 00:00:00"));
const serverLocked = ref(true);
const refreshCounter = ref(0);

const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

const showSecret = computed(() => {
  if (!frontInfo.value) {
    return false;
  }
  if (frontInfo.value.general.permission >= 1) {
    return true;
  }
  if (frontInfo.value.general.officerLevel >= 2) {
    return true;
  }
  return false;
});

let responseLock = false;

const msgPanel = ref<InstanceType<typeof MessagePanel>>();

function moveLobby() {
  location.replace("../");
}

const lastVoteState = (() => {
  const key = `state.${serverID}.lastVote`;
  const value = parseInt(localStorage.getItem(key) ?? "0");
  const obj = ref<number>(value);
  watch(obj, (newValue, oldValue) => {
    if (newValue == oldValue) {
      return;
    }
    localStorage.setItem(key, newValue.toString());
  });
  return obj;
})();

async function tryRefresh() {
  msgPanel.value?.tryRefresh();

  if (responseLock) {
    return;
  }
  try {
    responseLock = true;
    const responseP = SammoAPI.Global.ExecuteEngine({ serverID }, true).then((response) => {
      if (response.result) {
        lastExecuted.value = parseTime(response.lastExecuted);
        serverLocked.value = response.locked;
      }
      return response;
    });

    //TODO: 갱신 알림 띄우기
    const response = await Promise.race([delay(3000), responseP]);
    responseLock = false;

    if (response === undefined) {
      //timeout이지만 일단 갱신한다.
      refreshCounter.value += 1;
      return;
    }

    if (!response.result) {
      if (response.reqRefresh) {
        alert(`갱신이 필요합니다: ${response.reason}`);
        window.location.reload();
        return;
      }

      console.error(response.reason);
      if (!asyncReady.value) {
        throw response.reason;
      }
      toasts.danger({
        title: "갱신 실패",
        body: response.reason,
      });
      return;
    }

    refreshCounter.value += 1;

    //TODO: 서버와 클라이언트 버전이 다르다면 갱신 필요
  } catch (e) {
    responseLock = false;
    if(isString(e)){
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    console.error(e);
    throw e;
  }
}

void Promise.all([storeP, tryRefresh()]).then(() => {
  asyncReady.value = true;
});

const globalMenu = ref<GetMenuResponse["menu"]>();
onMounted(async () => {
  try {
    const response = await SammoAPI.Global.GetGlobalMenu();
    globalMenu.value = response.menu;
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "메뉴 갱신 실패",
        body: `${e}`,
      });
    }
    console.error(e);
  }
});

const frontInfo = ref<GetFrontInfoResponse>();
const globalInfo = ref<GetFrontInfoResponse["global"]>({} as GetFrontInfoResponse["global"]);
const generalInfo = ref<GetFrontInfoResponse["general"]>();
const nationStaticInfo = ref<NationStaticItem>();
const nationInfo = ref<GetFrontInfoResponse["nation"]>();

const lastGeneralRecordID = ref(0);
const lastWorldHistoryID = ref(0);

const generalRecords = ref(new Denque<[number, string]>());
const globalRecords = ref(new Denque<[number, string]>());
const worldHistory = ref(new Denque<[number, string]>());

let generalInfoLock = false;

watch(refreshCounter, async () => {
  if (generalInfoLock) {
    return;
  }
  try {
    generalInfoLock = true;
    const response = await SammoAPI.General.GetFrontInfo({
      lastGeneralRecordID: lastGeneralRecordID.value,
      lastWorldHistoryID: lastWorldHistoryID.value,
    });
    generalInfoLock = false;

    frontInfo.value = response;
    globalInfo.value = response.global;
    generalInfo.value = response.general;

    const newLastExecuted = parseTime(response.global.lastExecuted);
    if (newLastExecuted.getTime() > lastExecuted.value.getTime()) {
      lastExecuted.value = newLastExecuted;
    }

    const rawNation = response.nation;
    nationInfo.value = rawNation;
    nationStaticInfo.value = {
      nation: rawNation.id,
      name: rawNation.name,
      color: rawNation.color,
      type: rawNation.type.raw,
      level: rawNation.level,
      capital: rawNation.capital,
      gennum: rawNation.gennum,
      power: rawNation.power,
    };

    const recentRecord = response.recentRecord;
    const recordIter = [
      ["flushGeneral", generalRecords, "general", lastGeneralRecordID],
      ["flushGlobal", globalRecords, "global", lastGeneralRecordID],
      ["flushHistory", worldHistory, "history", lastWorldHistoryID],
    ] as const;

    let haveNewRecord = false;

    for (const [flushKey, recordRef, recordKey, lastRecordID] of recordIter) {
      if (recentRecord[flushKey]) {
        recordRef.value = new Denque<[number, string]>();
        haveNewRecord = true;
      }
      const subRecord = recentRecord[recordKey];
      if (subRecord.length) {
        lastRecordID.value = Math.max(lastRecordID.value, subRecord[0][0]);
        haveNewRecord = true;
      }
      while (subRecord.length) {
        const [id, record] = unwrap(subRecord.pop());
        if (!recordRef.value.isEmpty() && id <= unwrap(recordRef.value.get(0))[0]) {
          continue;
        }
        if (recordRef.value.length >= 15) {
          recordRef.value.pop();
        }
        recordRef.value.unshift([id, formatLog(record)]);
      }
    }
    if (refreshCounter.value <= 1){
      console.log('초기화 완료');
    }
    else if (haveNewRecord) {
      toasts.info(
        {
          title: "갱신 완료",
          body: `동향 변경이 있습니다.`,
        },
        { delay: 1000 * 3 }
      );
    } else {
      toasts.success(
        {
          title: "갱신 완료",
        },
        { delay: 1000 * 3 }
      );
    }

    const lastVoteID = response.global.lastVoteID;
    if (lastVoteID > lastVoteState.value) {
      lastVoteState.value = lastVoteID;
      toasts.warning(
        {
          title: "설문조사 안내",
          body: `새로운 설문조사가 있습니다.`,
        },
        {
          delay: 1000 * 60,
        }
      );
    }
  } catch (e) {
    generalInfoLock = false;
    console.error(e);
    toasts.danger({
      title: "최근 정보 갱신 실패",
      body: `${e}`,
    });
  }
});

const map = ref<MapResult>();

const cityPosition = getCityPosition();
const formatCityInfoText = formatCityInfo;
const imagePath = window.pathConfig.gameImage;

function genMapCityHref(cityID: number) {
  return `b_currentCity.php?citylist=${cityID}`;
}

function onCityClick(city: MapCityParsed, $event: MouseEvent | TouchEvent): void {
  if (city.id === 0) {
    return;
  }
  if ($event.ctrlKey) {
    window.open(genMapCityHref(city.id), "_blank");
    return;
  }
  window.location.href = genMapCityHref(city.id);
}

watch(refreshCounter, async () => {
  try {
    map.value = await SammoAPI.Global.GetMap({
      neutralView: 0,
      showMe: 1,
    });
  } catch (e) {
    console.error(e);
    toasts.danger({
      title: "지도 갱신 실패",
      body: `${e}`,
    });
  }
});

const reservedCommandPanel = ref<InstanceType<typeof PartialReservedCommand> | null>(null);
watch(refreshCounter, async () => {
  reservedCommandPanel.value?.reloadCommandList();
});
</script>
<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";
:deep() {
  @import "@scss/gameEvent.scss";
  @import "@scss/battleLog.scss";
}

#mobileBottomBar {
  display: none;
}

#outBlock {
  display: flex;
  flex-flow: column;
  justify-content: space-between;
}

.generalInfo {
  width: 500px;
}

#ingameBoard {
  display: grid;
}

.nationNoticeBody {
  word-break: break-all;

  :deep(p) {
    min-height: 1em;
  }
}

@include media-500px {
  #outBlock {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    #mobileBottomBar {
      display: block;
    }
  }

  #outBlock2 {
    flex-grow: 1;
    overflow-y: scroll;
  }

  #container {
    width: 500px;
  }

  #ingameBoard {
    grid-template-columns: 1fr;
  }

  .reservedCommandZone {
    grid-row: 1;
  }

  .generalCommandToolbar {
    grid-row: 2;
  }

  .nationInfo {
    grid-row: 3;
  }

  .generalInfo {
    grid-row: 4;
  }

  .cityInfo {
    grid-row: 5;
  }

  #actionMiniPlate {
    display: none;
  }
}

@include media-1000px {
  #container {
    width: 1000px;
  }

  #ingameBoard {
    grid-template-columns: 500px 200px 300px;
  }

  .mapView {
    grid-column: 1 / 3;
    grid-row: 1;
  }

  .reservedCommandZone {
    grid-column: 3;
    grid-row: 1 / 3;
  }

  .cityInfo {
    grid-column: 1 / 3;
    grid-row: 2 / 4;
  }

  #actionMiniPlate {
    grid-column: 3;
    grid-row: 3;
    padding-left: 10px;
    padding-top: 10px;
    padding-bottom: 10px;
  }

  .generalCommandToolbar {
    grid-column: 1 / 4;
  }

  .nationInfo {
    grid-column: 1;
  }

  .generalInfo {
    grid-column: 2 / 4;
  }

  #actionMiniPlateSub {
    display: none;
  }
}
</style>
