<template>
  <div class="commandPad">
    <div class="col alert alert-dark m-0 p-1 center">
      <h4 class="m-0">명령 목록</h4>
    </div>
    <div :style="{ position: 'relative' }">
      <div class="bg-dark" :style="{
        position: 'absolute',
        top: `${basicModeRowHeight * currentQuickReserveTarget + 30}px`,
        width: '100%',
        zIndex: 9,
      }">
        <CommandSelectForm ref="commandQuickReserveForm" :activatedCategory="'개인 전략'" :commandList="commandList"
          :hideClose="false" @onClose="chooseQuickReserveCommand($event)" />
      </div>
    </div>
    <div :class="{
      commandTable: true,
    }">
      <DragSelect :style="rowGridStyle" :disabled="true" attribute="turnIdx">
        <div v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)" :key="turnIdx" :turnIdx="turnIdx"
          class="idx_pad center d-grid">
          <div class="plain-center">
            {{ turnIdx + 1 }}
          </div>
        </div>
      </DragSelect>
      <DragSelect :style="rowGridStyle" attribute="turnIdx" :disabled="true">
        <div v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)" :key="turnIdx" height="24"
          class="month_pad center" :turnIdx="turnIdx" :style="{
            'white-space': 'nowrap',
            'font-size': `${Math.min(14, (75 / (`${turnObj.year ?? 1}`.length + 8)) * 1.8)}px`,
            overflow: 'hidden',
          }">
          {{ turnObj.year ? `${turnObj.year}年` : "" }}
          {{ turnObj.month ? `${turnObj.month}月` : "" }}
        </div>
      </DragSelect>
      <div :style="rowGridStyle">
        <div v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)" :key="turnIdx"
          class="time_pad center" :style="{
            backgroundColor: 'black',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
          }">
          {{ turnObj.time }}
        </div>
      </div>
      <div :style="rowGridStyle">
        <div v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)" :key="turnIdx"
          class="turn_pad center">
          <span v-b-tooltip.hover="turnObj.tooltip" class="turn_text" :style="turnObj.style">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <span v-html="turnObj.brief" />
          </span>
        </div>
      </div>
      <div :style="rowGridStyle">
        <div v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)" :key="turnIdx"
          class="action_pad d-grid">
          <BButton :variant="turnIdx % 2 == 0 ? 'secondary' : 'dark'" size="sm" class="simple_action_btn bi bi-pencil"
            :disabled="!commandList.length" @click="toggleQuickReserveForm(turnIdx)" />
        </div>
      </div>
    </div>
    <div class="row gx-1">
      <div class="col d-grid">
      </div>
      <div class="col alert alert-primary m-0 p-0"
        style="text-align: center; display: flex; justify-content: center; align-items: center">
        <SimpleClock :serverTime="serverNow" />
      </div>
      <div class="col d-grid">
        <BButton @click="toggleViewMaxTurn">
          {{ flippedMaxTurn == viewMaxTurn ? "펼치기" : "접기" }}
        </BButton>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
declare const staticValues: {
  maxTurn: number;
  maxPushTurn: number;
  serverNow: string;
  serverNick: string;
  mapName: string;
  unitSet: string;
};
</script>

<script lang="ts" setup>
import addMinutes from "date-fns/esm/addMinutes";
import { isString, range, trim } from "lodash-es";
import queryString from "query-string";
import { onMounted, ref, watch } from "vue";
import { formatTime } from "@util/formatTime";
import { joinYearMonth } from "@util/joinYearMonth";
import { mb_strwidth } from "@util/mb_strwidth";
import { parseTime } from "@util/parseTime";
import { parseYearMonth } from "@util/parseYearMonth";
import DragSelect from "@/components/DragSelect.vue";
import { SammoAPI } from "./SammoAPI";
import type { CommandItem, CommandTableResponse } from "@/defs";
import CommandSelectForm from "@/components/CommandSelectForm.vue";
import { BButton, BButtonGroup, BDropdownItem, BDropdown, BDropdownText, BDropdownDivider, useToast } from "bootstrap-vue-next";
import { StoredActionsHelper } from "./util/StoredActionsHelper";
import type { TurnObj } from "@/defs";
import type { Args } from "./processing/args";
import { getEmptyTurn } from "./util/QueryActionHelper";
import SimpleClock from "./components/SimpleClock.vue";
import type { ReservedCommandResponse } from "./defs/API/Command";
import { unwrap } from "./util/unwrap";

defineExpose({
  updateCommandTable,
  reloadCommandList,
})

const toasts = unwrap(useToast());

const { maxTurn } = staticValues;

const commandList = ref<CommandTableResponse['commandTable']>([]);

const nullCommand: CommandItem = {
  'value': '휴식',
  'compensation': 0,
  'simpleName': '휴식',
  'possible': true,
  'info': '휴식',
  'title': '휴식',
  'reqArg': false,
}

const selectedCommand = ref<CommandItem>(nullCommand);
const commandSelectForm = ref<InstanceType<typeof CommandSelectForm> | null>(null);
const invCommandMap = new Map<string, CommandItem>();

async function updateCommandTable() {
  try {
    const response = await SammoAPI.General.GetUserActionCommandTable();
    console.log(response);
    commandList.value = response.commandTable;
  }
  catch (e) {
    console.error(e);
  }
}

watch(commandList, (commandList) => {
  if (!commandList) {
    return;
  }

  for (const commandCategories of commandList) {
    if (!commandCategories.values) {
      continue;
    }
    for (const commandObj of commandCategories.values) {
      if (!commandObj.reqArg) {
        continue;
      }
      console.warn(`명령 ${commandObj.value}에는 인자가 필요하나, 현재는 지원하지 않습니다.`);
    }
  }

  invCommandMap.clear();
  for (const category of commandList) {
    for (const command of category.values) {
      invCommandMap.set(command.value, command);
    }
  }

  if (selectedCommand.value === nullCommand || !invCommandMap.has(selectedCommand.value.value)) {
    selectedCommand.value = commandList[0].values[0];
  }
});

onMounted(() => {
  void updateCommandTable();
});

const reservedCommandList = ref(getEmptyTurn(maxTurn));

const flippedMaxTurn = 14;

const basicModeRowHeight = 34.4;
const viewMaxTurn = ref(flippedMaxTurn);
const rowGridStyle = ref({
  display: "grid",
  gridTemplateRows: `repeat(${viewMaxTurn.value}, ${basicModeRowHeight}px)`,
});

watch([viewMaxTurn], ([maxTurn]) => {
  rowGridStyle.value.gridTemplateRows = `repeat(${maxTurn}, ${basicModeRowHeight}px)`;
});

function toggleViewMaxTurn() {
  if (viewMaxTurn.value == flippedMaxTurn) {
    viewMaxTurn.value = maxTurn;
  } else {
    viewMaxTurn.value = flippedMaxTurn;
  }
}

const serverNow = ref(new Date());

async function reloadCommandList() {
  let result: ReservedCommandResponse;
  try {
    result = await SammoAPI.Command.GetReservedCommand();
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    console.error(e);
    return;
  }

  let yearMonth = joinYearMonth(result.year, result.month);

  const turnTime = parseTime(result.turnTime);
  let nextTurnTime = new Date(turnTime);

  const autorunLimitYearMonth = result.autorun_limit ?? yearMonth - 1;
  const [autorunLimitYear, autorunLimitMonth] = parseYearMonth(autorunLimitYearMonth);

  reservedCommandList.value = [];
  for (const obj of result.turn) {
    const [year, month] = parseYearMonth(yearMonth);
    let tooltip: string[] = [];
    let style: Record<string, string> = {};

    const brief = obj.brief;

    if (yearMonth <= autorunLimitYearMonth) {
      if (obj.brief == "휴식") {
        obj.brief = "휴식<small>(자율 행동)</small>";
      }
      style.color = "#aaffff";

      tooltip.push(`자율 행동 기간: ${autorunLimitYear}년 ${autorunLimitMonth}월까지`);
    }

    if (mb_strwidth(brief) > 22) {
      tooltip.push(brief);
    }

    reservedCommandList.value.push({
      ...obj,
      year,
      month,
      time: formatTime(nextTurnTime, "HH:mm"),
      tooltip: tooltip.length == 0 ? undefined : tooltip.join("\n"),
      style,
    });

    yearMonth += 1;
    nextTurnTime = addMinutes(nextTurnTime, result.turnTerm);
  }

  serverNow.value = parseTime(result.date);
}

async function reserveCommand() {
  const turnIdx = currentQuickReserveTarget.value;

  const commandName = selectedCommand.value.value;
  if(turnIdx < 0) {
    return;
  }

  try {
    const result = await SammoAPI.Command.ReserveUserAction({
      turnIdx,
      action: commandName,
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    console.error(e);
    return;
  }
  await reloadCommandList();
}

const commandQuickReserveForm = ref<InstanceType<typeof CommandSelectForm> | null>(null);

const currentQuickReserveTarget = ref(-1);
function chooseQuickReserveCommand(val?: string) {
  if (!val) {
    return;
  }
  selectedCommand.value = unwrap(invCommandMap.get(val));
  void reserveCommand();
}
function toggleQuickReserveForm(turnIdx: number) {
  if (turnIdx == currentQuickReserveTarget.value) {
    commandQuickReserveForm.value?.toggle();
    return;
  }
  currentQuickReserveTarget.value = turnIdx;
  commandQuickReserveForm.value?.show();
}

onMounted(() => {
  void reloadCommandList();
});
</script>
<style lang="scss">
@use "sass:color";

@import "@scss/common/break_500px.scss";
@import "@scss/common/variables.scss";
@import "@scss/common/bootswatch_custom_variables.scss";
@import "@scss/game_bg.scss";

.commandPad {
  background-color: $gray-900;
  position: relative;
}

.commandTable {
  width: 100%;
  display: grid;
  grid-template-columns:
    minmax(20px, 0.8fr) minmax(75px, 2.4fr) minmax(40px, 0.9fr) 4.8fr minmax(28px, 0.8fr);
  //30, 70, 37.65, 160
}

.commandTable.isEditMode {
  width: 100%;
  display: grid;
  grid-template-columns: minmax(30px, 1fr) minmax(75px, 2.5fr) minmax(40px, 1fr) 5fr;
  //30, 70, 37.65, 160
}

@include media-1000px {
  .commandPad {
    margin-left: 10px;

    .turn_pad {
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .multiselect__content-wrapper {
      margin-left: calc(-100% / 7 * 2);
      width: calc(100% / 7 * 12);
    }

    .multiselect__single {
      display: inline-block;
      text-overflow: ellipsis;
      white-space: nowrap;
      overflow: hidden;
    }
  }
}

@include media-500px {
  .dropdown-item {
    padding: 8px;
  }

  .commandPad {
    margin-top: 10px;
    margin-bottom: 10px;

    .btn {
      transition: none !important;
    }
  }

  .month_pad,
  .time_pad,
  .turn_pad {
    padding: 6px;
  }
}

.isEditMode .month_pad:hover {
  text-decoration: underline;
  cursor: pointer;
}

.plain-center {
  background-color: black;
}

.plain-center,
.month_pad,
.time_pad,
.turn_pad {
  display: flex;
  justify-content: center;
  align-items: center;
}

.turn_pad {
  white-space: nowrap;
  background-color: $nbase2color;
}

.turn_pad:nth-child(2n) {
  background-color: color.adjust($nbase2color, $lightness: -5%);
}

.turn_pad .turn_text {
  display: inline-block;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
</style>
