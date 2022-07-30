<template>
  <BContainer v-if="asyncReady" id="container" :toast="{ root: true }">
    <TopBackBar title="감찰부" :reloadable="true" type="close" @reload="reload" />

    <div class="row gx-0">
      <div class="col-2 col-md-1 d-grid">
        <BButton @click="changeTargetByOffset(-1)">◀ 이전</BButton>
      </div>
      <div class="col-3 col-md-4">
        <BFormSelect v-model="orderBy">
          <BFormSelectOption
            v-for="[orderKey, [orderName]] of Object.entries(textMap)"
            :key="orderKey"
            :value="orderKey"
          >
            {{ orderName }}
          </BFormSelectOption>
        </BFormSelect>
      </div>
      <div class="col-5 col-md-6">
        <BFormSelect v-model="targetGeneralID">
          <BFormSelectOption v-for="general of orderedGeneralList" :key="general.no" :value="general.no">
            <span
              :style="{
                color: getNpcColor(general.npc),
              }"
              >{{ general.officerLevel > 4 ? `*${general.name}*` : general.name }}({{ general.turntime.slice(-5) }}){{
                textMap[orderBy][3](general)
              }}</span
            >
          </BFormSelectOption>
        </BFormSelect>
      </div>
      <div class="col-2 col-md-1 d-grid">
        <BButton @click="changeTargetByOffset(1)">다음 ▶</BButton>
      </div>
    </div>
    <div v-if="targetGeneral && nationInfo && targetGeneralLogs" class="row gx-0 bg0">
      <div class="col-12 col-md-6">
        <div class="bg1 header-cell" style="color: skyblue">장수 정보</div>
        <GeneralBasicCard :general="targetGeneral" :nation="nationInfo" />
        <GeneralSupplementCard :general="targetGeneral" />
      </div>
      <div class="col-12 col-md-6">
        <div class="bg1 header-cell">장수 열전</div>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-for="[id, log] of targetGeneralLogs.generalHistory" :key="id" v-html="log" />
      </div>
      <div class="col-12 col-md-6">
        <div class="bg1 header-cell">전투 기록</div>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-for="[id, log] of targetGeneralLogs.battleDetail" :key="id" v-html="log" />
      </div>
      <div class="col-12 col-md-6">
        <div class="bg1 header-cell">전투 결과</div>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-for="[id, log] of targetGeneralLogs.battleResult" :key="id" v-html="log" />
      </div>
      <div v-if="targetGeneralLogs.generalAction.size > 0" class="col-12 col-md-6">
        <div class="bg1 header-cell">개인 기록</div>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-for="[id, log] of targetGeneralLogs.generalAction" :key="id" v-html="log" />
      </div>
    </div>

    <BottomBar type="close"></BottomBar>
  </BContainer>
</template>

<script lang="ts">
declare const queryValues: {
  generalID: number | null;
};
</script>
<script lang="ts" setup>
import { BContainer, useToast, BFormSelect, BFormSelectOption, BButton } from "bootstrap-vue-3";
import { onMounted, provide, ref, watch } from "vue";
import { getGameConstStore, type GameConstStore } from "./GameConstStore";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import type { GeneralListItemP1 } from "./defs/API/Nation";
import { SammoAPI } from "./SammoAPI";
import { unwrap } from "@/util/unwrap";
import { merge2DArrToObjectArr } from "@/util/merge2DArrToObjectArr";
import { getNpcColor } from "@/common_legacy";
import GeneralBasicCard from "./components/GeneralBasicCard.vue";
import GeneralSupplementCard from "@/components/GeneralSupplementCard.vue";
import type { NationStaticItem } from "./defs";
import type { GeneralLogType } from "./defs/API/General";
import { isString } from "lodash";
import { formatLog } from "./utilGame/formatLog";

const toasts = unwrap(useToast());

const generalList = ref(new Map<number, GeneralListItemP1>());
const textMap = {
  recent_war: [
    "최근 전투",
    (gen: GeneralListItemP1) => gen.recent_war,
    false,
    (gen: GeneralListItemP1) => `[${gen.recent_war.slice(-5)}]`,
  ],
  warnum: ["전투 횟수", (gen: GeneralListItemP1) => gen.warnum, false, (gen: GeneralListItemP1) => `[${gen.warnum}회]`],
  turntime: ["최근 턴", (gen: GeneralListItemP1) => gen.turntime, false, (gen: GeneralListItemP1) => ""],
  name: ["이름", (gen: GeneralListItemP1) => `${gen.npc} ${gen.name}`, true, (gen: GeneralListItemP1) => ""],
} as const;

const orderedGeneralList = ref<GeneralListItemP1[]>([]);
const orderedInvGeneralKeyIndex = ref(new Map<number, number>());

const orderBy = ref<keyof typeof textMap>("turntime");
const targetGeneral = ref<GeneralListItemP1>();
const targetGeneralID = ref(queryValues.generalID ?? undefined);

type GeneralLogs = {
  [key in GeneralLogType]: Map<number, string>;
};

const targetGeneralLogs = ref<GeneralLogs>();

const nationInfo = ref<NationStaticItem>();

watch([generalList, targetGeneralID], async ([generalList, targetGeneralID]) => {
  if (targetGeneralID === undefined) {
    targetGeneral.value = undefined;
    return;
  }
  targetGeneral.value = generalList.get(targetGeneralID);

  const logs: GeneralLogs = {
    generalHistory: new Map(),
    battleResult: new Map(),
    battleDetail: new Map(),
    generalAction: new Map(),
  };

  const waiter: Promise<unknown>[] = [];

  for (const reqType of ["generalHistory", "battleResult", "battleDetail", "generalAction"] as const) {
    waiter.push(
      SammoAPI.Nation.GetGeneralLog({ generalID: targetGeneralID, reqType }).then(
        (res) => {
          const rawLogs: [number, string][] = Object.entries(res.log).map(([key, value]) => [
            Number(key),
            formatLog(value),
          ]);
          rawLogs.sort(([keyLhs], [keyRhs]) => keyRhs - keyLhs);
          for (const [idx, log] of rawLogs) {
            logs[reqType].set(Number(idx), formatLog(log));
          }
        },
        (err) => {
          if (!isString(err)) {
            toasts.danger({
              title: "에러",
              body: `${err}`,
            });
          }
        }
      )
    );
  }
  await Promise.all(waiter);
  console.log(logs);
  targetGeneralLogs.value = logs;
});

watch([generalList, orderBy], ([generalList, orderBy], [, oldOrderBy]) => {
  console.log(generalList);
  const list = Array.from(generalList.values());
  if (orderBy != oldOrderBy) {
    targetGeneralID.value = undefined;
  }

  const idSet = new Set<number>(list.map((gen) => gen.no));
  list.sort((a, b) => {
    const [, getter, isAsc] = textMap[orderBy];
    const aVal = getter(a);
    const bVal = getter(b);
    if (aVal === bVal) return 0;
    return isAsc ? (aVal > bVal ? 1 : -1) : aVal < bVal ? 1 : -1;
  });

  const invIndex = new Map<number, number>();
  for (const [idx, gen] of list.entries()) {
    invIndex.set(gen.no, idx);
  }

  orderedGeneralList.value = list;
  orderedInvGeneralKeyIndex.value = invIndex;

  const oldTargetGeneralID = targetGeneralID.value;
  if (!oldTargetGeneralID) {
    targetGeneralID.value = list[0].no;
  } else if (!idSet.has(oldTargetGeneralID)) {
    targetGeneralID.value = list[0].no;
  }
});

function changeTargetByOffset(idx: number) {
  const targetID = targetGeneralID.value;
  if (targetID === undefined) {
    return;
  }

  const currIdx = orderedInvGeneralKeyIndex.value.get(targetID);
  if (currIdx === undefined) {
    return;
  }

  let newIdx = currIdx + idx;
  const listLen = orderedGeneralList.value.length;
  while (newIdx < 0) {
    newIdx += listLen;
  }
  newIdx = newIdx % listLen;

  targetGeneralID.value = orderedGeneralList.value[newIdx].no;
}

const asyncReady = ref(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

async function reload(): Promise<void> {
  console.log("갱신 시도");

  try {
    const nationP = SammoAPI.Nation.GetNationInfo({});
    const { column, list, permission, troops, env } = await SammoAPI.Nation.GeneralList();

    if (permission === 0) {
      throw "권한이 부족합니다.";
    }

    console.log(list);
    const rawGeneralList = merge2DArrToObjectArr(column, list);

    const newList = new Map<number, GeneralListItemP1>();
    for (const general of rawGeneralList) {
      newList.set(general.no, general);
    }
    generalList.value = newList;

    const { nation } = await nationP;
    nationInfo.value = nation;
  } catch (e) {
    toasts.danger({
      title: "오류",
      body: `${e}`,
    });
    console.error(e);
  }
}

onMounted(async () => {
  await reload();
});
</script>
<style lang="scss" scoped>
.header-cell {
  color: orange;
  font-size: 1.3em;
  text-align: center;
  border: 1px gray solid;
}
</style>
