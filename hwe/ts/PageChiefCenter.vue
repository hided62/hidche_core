<template>
  <BContainer id="container" class="pageChiefCenter" :toast="{ root: true }">
    <TopBackBar title="사령부" reloadable @reload="reloadTable" />

    <div
      v-if="asyncReady && chiefList !== undefined"
      id="mainTable"
      :class="`${targetIsMe ? 'targetIsMe' : 'targetIsNotMe'}`"
    >
      <template v-for="(chiefLevel, vidx) in [12, 10, 8, 6, 11, 9, 7, 5]" :key="chiefLevel">
        <div v-if="vidx % 4 == 0" :class="['turnIdx', vidx == 0 && !targetIsMe ? undefined : 'only1000px']">
          <div :class="['subRows', 'bg0']" :style="mainTableGridRows">
            <div class="bg1">&nbsp;</div>
            <div v-for="idx in maxChiefTurn" :key="idx" :class="[`turnIdxLeft`, 'align-self-center', 'center']">
              {{ idx }}
            </div>
          </div>
        </div>
        <div
          v-for="(officer, idx) in [chiefList[chiefLevel]]"
          :key="idx"
          :class="[`${viewTarget == chiefLevel ? '' : 'only1000px'}`]"
        >
          <TopItem
            v-if="officerLevel != chiefLevel"
            :style="mainTableGridRows"
            :officer="postFilterOfficer(chiefList[chiefLevel])"
            :maxTurn="maxChiefTurn"
            :turnTerm="turnTerm"
          />
          <ChiefReservedCommand
            v-else
            :key="idx"
            :targetIsMe="targetIsMe"
            :year="year"
            :month="month"
            :turn="officer.turn"
            :turnTerm="turnTerm"
            :commandList="unwrap(commandList)"
            :troopList="unwrap(troopList)"
            :turnTime="officer.turnTime"
            :maxTurn="maxChiefTurn"
            :maxPushTurn="Math.floor(maxChiefTurn / 2)"
            :date="date"
            :officer="officer"
            @raiseReload="reloadTable()"
          />
        </div>
        <div v-if="vidx % 4 == 3" :class="['turnIdx', vidx == 7 && !targetIsMe ? undefined : 'only1000px']">
          <div :class="['subRows', 'bg0']" :style="mainTableGridRows">
            <div class="bg1">&nbsp;</div>
            <div v-for="idx in maxChiefTurn" :key="idx" :class="[`turnIdxRight`, 'align-self-center', 'center']">
              {{ idx }}
            </div>
          </div>
        </div>
      </template>
    </div>
  </BContainer>
  <div v-if="chiefList" id="bottomChiefBox">
    <div id="bottomChiefList" class="c-bg2">
      <template v-for="(chiefLevel, vidx) in [12, 10, 8, 6, 11, 9, 7, 5]" :key="chiefLevel">
        <div v-if="vidx % 4 == 0" class="turnIdx subRows bg0" :style="subTableGridRows">
          <div class="bg1" style="grid-row: 1/3" />
          <div v-for="idx in maxChiefTurn" :key="idx" :class="[`turnIdxLeft`]">
            {{ idx }}
          </div>
        </div>
        <BottomItem
          :chiefLevel="chiefLevel"
          :officer="postFilterOfficer(chiefList[chiefLevel])"
          :style="subTableGridRows"
          :isMe="chiefLevel == officerLevel"
          @click="viewTarget = chiefLevel"
        />
        <div v-if="vidx % 4 == 3" class="turnIdx subRows bg0" :style="subTableGridRows">
          <div class="bg1" style="grid-row: 1/3" />
          <div v-for="idx in maxChiefTurn" :key="idx" :class="`turnIdxRight`">
            {{ idx }}
          </div>
        </div>
      </template>
    </div>
  </div>
  <div id="bottomBar">
    <BottomBar />
  </div>
</template>
<script lang="ts">
declare const staticValues: {
  serverNick: string;
  mapName: string;
  unitSet: string;
};
</script>
<script lang="ts" setup>
import "@scss/game_bg.scss";
import "../../css/config.css";

import { computed, provide, reactive, ref, toRefs, watch } from "vue";
import ChiefReservedCommand from "@/components/ChiefReservedCommand.vue";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import VueTypes from "vue-types";
import { isString } from "lodash-es";
import { entriesWithType } from "./util/entriesWithType";
import TopItem from "@/ChiefCenter/TopItem.vue";
import BottomItem from "@/ChiefCenter/BottomItem.vue";
import type { OptionalFull, TurnObj } from "./defs";
import { SammoAPI } from "./SammoAPI";
import { unwrap } from "@/util/unwrap";
import { StoredActionsHelper } from "./util/StoredActionsHelper";
import type { ChiefResponse } from "./defs/API/NationCommand";
import { getGameConstStore, type GameConstStore } from "./GameConstStore";
import { postFilterNationCommandGen } from "./utilGame/postFilterNationCommandGen";
import { useToast } from "bootstrap-vue-3";

const props = defineProps({
  maxChiefTurn: VueTypes.number.isRequired,
});


const toasts = unwrap(useToast());

const asyncReady = ref<boolean>(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

const tableObj = reactive<Omit<OptionalFull<ChiefResponse>, "result">>({
  lastExecute: undefined,
  year: undefined,
  month: undefined,
  turnTerm: undefined,
  date: undefined,
  troopList: undefined,
  chiefList: undefined,
  isChief: undefined,
  autorun_limit: undefined,
  officerLevel: undefined,
  commandList: undefined,
  mapName: undefined,
  unitSet: undefined,
});

const { year, month, turnTerm, date, chiefList, troopList, officerLevel, commandList } = toRefs(tableObj);

let postFilterNationCommand = function (turnObj: TurnObj): TurnObj {
  return turnObj;
};

watch([tableObj, gameConstStore], ([tableObj, gameConstStore]) => {
  if (tableObj.troopList === undefined) {
    return;
  }
  if (gameConstStore === undefined) {
    return;
  }

  postFilterNationCommand = postFilterNationCommandGen(tableObj.troopList, gameConstStore);
});

type OfficerObj = ChiefResponse["chiefList"][0];
function postFilterOfficer(officer: OfficerObj|undefined): OfficerObj|undefined {
  if(officer === undefined) {
    return undefined;
  }
  return {
    ...officer,
    turn: officer.turn.map(postFilterNationCommand),
  };
}

const viewTarget = ref<number | undefined>();

const targetIsMe = ref<boolean>(false);
watch(viewTarget, (val) => {
  console.log("targetChange!", val, targetIsMe);
  if (val === undefined) {
    targetIsMe.value = false;
    return;
  }
  if (tableObj.officerLevel === undefined) {
    targetIsMe.value = false;
  }
  targetIsMe.value = val === tableObj.officerLevel;
});

async function reloadTable(): Promise<void> {
  try {
    const response = await SammoAPI.NationCommand.GetReservedCommand();
    console.log(response);
    for (const [key, value] of entriesWithType(response)) {
      if (key === "result") {
        continue;
      }

      if (key === "officerLevel") {
        if (value < 5) {
          tableObj.officerLevel = undefined;
        } else {
          tableObj.officerLevel = value as number;
        }
        continue;
      }

      //HACK:
      tableObj[key as unknown as "year"] = value as unknown as undefined;
    }

    if (viewTarget.value === undefined) {
      if (!tableObj.officerLevel) {
        viewTarget.value = 12;
      } else {
        viewTarget.value = tableObj.officerLevel;
      }
    }
  } catch (e) {
    if (isString(e)) {
      alert(e);
    }
    console.error(e);
    return;
  }
}

const mainTableGridRows = computed(() => {
  return {
    gridTemplateRows: `24px repeat(${props.maxChiefTurn}, 30px)`,
  };
});

const subTableGridRows = computed(() => {
  return {
    gridTemplateRows: `36px repeat(${props.maxChiefTurn + 1}, 1fr)`,
  };
});

void reloadTable();

const storedActionsHelper = new StoredActionsHelper(
  staticValues.serverNick,
  "nation",
  staticValues.mapName,
  staticValues.unitSet
);
provide("storedNationActionsHelper", storedActionsHelper);
</script>
<style lang="scss">
@import "@scss/chiefCenter.scss";
</style>
