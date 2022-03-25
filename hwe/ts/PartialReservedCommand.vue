<template>
  <div class="commandPad">
    <div class="col alert alert-dark m-0 p-1 center">
      <h4 class="m-0">명령 목록</h4>
    </div>
    <div class="row gx-1">
      <div class="col d-grid">
        <BButton
          variant="secondary"
          @click="isEditMode = !isEditMode"
        >{{ isEditMode ? '일반 모드로' : '고급 모드로' }}</BButton>
      </div>
      <div
        class="col alert alert-primary m-0 p-0"
        style="
          text-align: center;
          display: flex;
          justify-content: center;
          align-items: center;
        "
      >{{ formatTime(serverNow, "HH:mm:ss") }}</div>
      <div class="col d-grid">
        <BDropdown right text="반복">
          <BDropdownItem
            v-for="turnIdx in maxPushTurn"
            :key="turnIdx"
            @click="repeatGeneralCommand(turnIdx)"
          >{{ turnIdx }}턴</BDropdownItem>
        </BDropdown>
      </div>
    </div>

    <div class="commandSelectFormAnchor">
      <div v-if="isEditMode" class="row gx-1">
        <div class="col-4 d-grid">
          <BDropdown left text="범위">
            <BDropdownItem @click="selectTurn()">해제</BDropdownItem>
            <BDropdownItem @click="selectAll(true)">모든턴</BDropdownItem>
            <BDropdownItem @click="selectStep(0, 2)">홀수턴</BDropdownItem>
            <BDropdownItem @click="selectStep(1, 2)">짝수턴</BDropdownItem>
            <BDropdownDivider></BDropdownDivider>

            <BDropdownText v-for="spanIdx in [3, 4, 5, 6, 7]" :key="spanIdx">
              {{ spanIdx }}턴 간격
              <br />
              <BButtonGroup>
                <BButton
                  class="ignoreMe"
                  v-for="beginIdx in spanIdx"
                  :key="beginIdx"
                  @click="selectStep(beginIdx - 1, spanIdx)"
                >{{ beginIdx }}</BButton>
              </BButtonGroup>
            </BDropdownText>
          </BDropdown>
        </div>

        <div class="col-4 d-grid">
          <BDropdown left text="보관함">
            <BDropdownItem
              v-for="[actionKey, actions] of storedActions"
              :key="actionKey"
              @click="useStoredAction(actions)"
            >
              {{ actionKey }}
              <BButton @click.prevent="deleteStoredActions(actionKey)" size="sm">삭제</BButton>
            </BDropdownItem>
          </BDropdown>
        </div>

        <div class="col-4 d-grid">
          <BDropdown right text="최근 실행">
            <BDropdownItem
              v-for="(action, idx) in recentActions"
              :key="idx"
              @click="void reserveCommandDirect([[Array.from(turnList.values()), action]])"
            >{{ action.brief }}</BDropdownItem>
          </BDropdown>
        </div>

        <div class="col-5 d-grid">
          <BDropdown left variant="info" text="선택한 턴을">
            <BDropdownItem @click="clipboardCut">
              <i class="bi bi-scissors"></i>&nbsp;잘라내기
            </BDropdownItem>
            <BDropdownItem @click="clipboardCopy">
              <i class="bi bi-files"></i>&nbsp;복사하기
            </BDropdownItem>
            <BDropdownItem @click="clipboardPaste">
              <i class="bi bi-clipboard-fill"></i>&nbsp;붙여넣기
            </BDropdownItem>
            <BDropdownDivider />
            <BDropdownItem @click="setStoredActions">
              <i class="bi bi-bookmark-plus-fill"></i>&nbsp;보관하기
            </BDropdownItem>
            <BDropdownItem @click="subRepeatCommand">
              <i class="bi bi-arrow-repeat"></i>&nbsp;반복하기
            </BDropdownItem>
            <BDropdownDivider />
            <BDropdownItem @click="eraseSelectedTurnList">
              <i class="bi bi-eraser"></i>&nbsp;비우기
            </BDropdownItem>
            <BDropdownItem @click="eraseAndPullCommand">
              <i class="bi bi-arrow-bar-up"></i>&nbsp;지우고 당기기
            </BDropdownItem>
            <BDropdownItem @click="pushEmptyCommand">
              <i class="bi bi-arrow-bar-down"></i>&nbsp;뒤로 밀기
            </BDropdownItem>
            <!-- 최근에 실행한 10턴 -->
          </BDropdown>
        </div>

        <div class="col-7 d-grid">
          <BButton variant="light" @click="toggleForm($event)" :style="{ color: 'black' }">명령 선택 ▾</BButton>
        </div>
      </div>
    </div>

    <div :style="{ position: 'relative' }">
      <div
        class="commandQuickReserveFormAnchor bg-dark"
        :style="{
          position: 'absolute',
          top: `${basicModeRowHeight * currentQuickReserveTarget + 30}px`,
          width: '100%',
          zIndex: 9,
        }"
      ></div>
    </div>
    <div :class="{
      commandTable: true,
      isEditMode,
    }">
      <DragSelect
        :style="rowGridStyle"
        :disabled="!isEditMode"
        attribute="turnIdx"
        @dragStart="isDragToggle = true"
        @dragDone="
  isDragToggle = false;
toggleTurn(...$event);
        "
        v-slot="{ selected }"
      >
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(
            0,
            viewMaxTurn
          )"
          :turnIdx="turnIdx"
          :key="turnIdx"
          class="idx_pad center d-grid"
        >
          <BButton
            v-if="isEditMode"
            size="sm"
            :variant="
              (isDragToggle && selected.has(`${turnIdx}`)) ? 'light' :
                turnList.has(turnIdx)
                  ? 'info'
                  : turnList.size == 0 && prevTurnList.has(turnIdx)
                    ? 'success'
                    : 'primary'
            "
          >{{ turnIdx + 1 }}</BButton>
          <div v-else class="plain-center">{{ turnIdx + 1 }}</div>
        </div>
      </DragSelect>
      <DragSelect
        :style="rowGridStyle"
        attribute="turnIdx"
        :disabled="!isEditMode"
        @dragStart="isDragSingle = true"
        @dragDone="
  isDragSingle = false;
selectTurn(...$event);
        "
        v-slot="{ selected }"
      >
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(
            0,
            viewMaxTurn
          )"
          :key="turnIdx"
          height="24"
          class="month_pad center"
          :turnIdx="turnIdx"
          :style="{
            'white-space': 'nowrap',
            'font-size': `${Math.min(
              14,
              (75 / (`${turnObj.year ?? 1}`.length + 8)) * 1.8
            )}px`,
            overflow: 'hidden',
            color:
              isDragSingle && selected.has(`${turnIdx}`) ? 'cyan' : undefined,
          }"
        >
          {{ turnObj.year ? `${turnObj.year}年` : "" }}
          {{ turnObj.month ? `${turnObj.month}月` : "" }}
        </div>
      </DragSelect>
      <div :style="rowGridStyle">
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(
            0,
            viewMaxTurn
          )"
          :key="turnIdx"
          class="time_pad center"
          :style="{
            backgroundColor: 'black',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
          }"
        >{{ turnObj.time }}</div>
      </div>
      <div :style="rowGridStyle">
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(
            0,
            viewMaxTurn
          )"
          :key="turnIdx"
          class="turn_pad center"
        >
          <span
            class="turn_text"
            :style="turnObj.style"
            v-b-tooltip.hover
            :title="turnObj.tooltip"
            v-html="turnObj.brief"
          ></span>
        </div>
      </div>
      <div v-if="!isEditMode" :style="rowGridStyle">
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(
            0,
            viewMaxTurn
          )"
          :key="turnIdx"
          class="action_pad d-grid"
        >
          <BButton
            :variant="(turnIdx % 2 == 0) ? 'secondary' : 'dark'"
            size="sm"
            class="simple_action_btn bi bi-pencil"
            @click="toggleQuickReserveForm(turnIdx)"
          ></BButton>
        </div>
      </div>
    </div>
    <div class="row gx-1">
      <div class="col d-grid">
        <BDropdown :split="isEditMode" text="당기기" @click="pullGeneralCommandSingle">
          <BDropdownItem
            v-for="turnIdx in maxPushTurn"
            :key="turnIdx"
            @click="pushGeneralCommand(-turnIdx)"
          >{{ turnIdx }}턴</BDropdownItem>
        </BDropdown>
      </div>
      <div class="col d-grid">
        <BDropdown :split="isEditMode" text="미루기" @click="pushGeneralCommandSingle">
          <BDropdownItem
            v-for="turnIdx in maxPushTurn"
            :key="turnIdx"
            @click="pushGeneralCommand(turnIdx)"
          >{{ turnIdx }}턴</BDropdownItem>
        </BDropdown>
      </div>
      <div class="col d-grid">
        <BButton @click="toggleViewMaxTurn">{{ flippedMaxTurn == viewMaxTurn ? "펼치기" : "접기" }}</BButton>
      </div>
    </div>
  </div>
  <CommandSelectForm
    :commandList="commandList"
    anchor=".commandSelectFormAnchor"
    ref="commandSelectForm"
    @on-close="chooseCommand($event)"
  />
  <CommandSelectForm
    :commandList="commandList"
    anchor=".commandQuickReserveFormAnchor"
    ref="commandQuickReserveForm"
    @on-close="chooseQuickReserveCommand($event)"
    :hideClose="false"
  />
</template>

<script lang="ts">
declare const staticValues: {
  maxTurn: number,
  maxPushTurn: number,
  commandList: {
    category: string;
    values: CommandItem[];
  }[],
  serverNow: string,
  serverNick: string,
  mapName: string,
  unitSet: string,
}
</script>

<script lang="ts" setup>
import addMilliseconds from "date-fns/esm/addMilliseconds";
import addMinutes from "date-fns/esm/addMinutes";
import { clone, isString, min, range, repeat, trim } from "lodash";
import { stringifyUrl } from "query-string";
import { onMounted, ref, watch } from "vue";
import { formatTime } from "@util/formatTime";
import { joinYearMonth } from "@util/joinYearMonth";
import { mb_strwidth } from "@util/mb_strwidth";
import { parseTime } from "@util/parseTime";
import { parseYearMonth } from "@util/parseYearMonth";
import DragSelect from "@/components/DragSelect.vue";
import { SammoAPI, type InvalidResponse } from "./SammoAPI";
import type { CommandItem, ReserveBulkCommandResponse, ReserveCommandResponse } from "@/defs";
import CommandSelectForm from "@/components/CommandSelectForm.vue";
import { BButton, BButtonGroup, BDropdownItem, BDropdown, BDropdownText, BDropdownDivider } from "bootstrap-vue-3";
import { StoredActionsHelper } from "./util/StoredActionsHelper";
import type { TurnObj } from '@/defs';
import { unwrap } from "./util/unwrap";
import type { Args } from "./processing/args";

type TurnObjWithTime = TurnObj & {
  time: string;
  year?: number;
  month?: number;
  tooltip?: string;
  style?: Record<string, unknown>;
};

type ReservedCommandResponse = {
  result: true;
  turnTime: string;
  turnTerm: number;
  year: number;
  month: number;
  date: string;
  turn: TurnObj[];
  autorun_limit: null | number;
};


const {
  maxTurn,
  maxPushTurn,
  commandList,
} = staticValues;


const listReqArgCommand = new Set<string>();
const serverNow = ref(parseTime(staticValues.serverNow));
const clientNow = ref(new Date());
const timeDiff = ref(serverNow.value.getTime() - clientNow.value.getTime());
const selectedCommand = ref(staticValues.commandList[0].values[0]);
const commandSelectForm = ref<InstanceType<typeof CommandSelectForm> | null>(null);

for (const commandCategories of commandList) {
  if (!commandCategories.values) {
    continue;
  }
  for (const commandObj of commandCategories.values) {
    if (!commandObj.reqArg) {
      continue;
    }
    listReqArgCommand.add(commandObj.value);
  }
}

function toggleForm($event: Event): void {
  $event.preventDefault();

  const form = commandSelectForm.value;
  if (!form) {
    return;
  }
  form.toggle();
}

function isDropdownChildren(e?: Event): boolean {
  if (!e) {
    return false;
  }
  if (!e.target) {
    return false;
  }
  if (
    (e.target as HTMLElement).classList.contains("dropdown-item") ||
    (e.target as HTMLElement).classList.contains("dropdown-toggle-split") ||
    (e.target as HTMLElement).classList.contains("ignoreMe")
  ) {
    return true;
  }
  return false;
}


setTimeout(() => {
  updateNow();
}, 1000 - serverNow.value.getMilliseconds());


const emptyTurn: TurnObjWithTime[] = Array.from<TurnObjWithTime>({
  length: staticValues.maxTurn,
}).fill({
  arg: {},
  brief: "",
  action: "",
  year: undefined,
  month: undefined,
  time: "",
});

const editModeKey = `sammo_edit_mode_key`;

const prevTurnList = ref(new Set([0]));
const turnList = ref(new Set<number>());
const reservedCommandList = ref(emptyTurn);
const isEditMode = ref(localStorage.getItem(editModeKey) === '1');

const flippedMaxTurn = 14;

const editModeRowHeight = 29.35;
const basicModeRowHeight = 34.4;
const viewMaxTurn = ref(flippedMaxTurn);
const rowGridStyle = ref({
  display: "grid",
  gridTemplateRows: `repeat(${viewMaxTurn.value}, ${isEditMode.value ? editModeRowHeight : basicModeRowHeight}px)`,
});

watch([isEditMode, viewMaxTurn], ([isEditMode, maxTurn]) => {
  rowGridStyle.value.gridTemplateRows = `repeat(${maxTurn}, ${isEditMode ? editModeRowHeight : basicModeRowHeight}px)`;
});

const isDragSingle = ref(false);
const isDragToggle = ref(false);

const invCommandMap: Record<string, CommandItem> = {};
for (const category of commandList) {
  for (const command of category.values) {
    invCommandMap[command.value] = command;
  }
}


function updateNow() {
  serverNow.value = addMilliseconds(new Date(), timeDiff.value);
  setTimeout(() => {
    updateNow();
  }, 1000 - serverNow.value.getMilliseconds());
}

function toggleTurn(...reqTurnList: number[] | string[]) {
  for (let turnIdx of reqTurnList) {
    if (isString(turnIdx)) {
      turnIdx = parseInt(turnIdx);
    }
    if (turnList.value.has(turnIdx)) {
      turnList.value.delete(turnIdx);
    } else {
      turnList.value.add(turnIdx);
    }
  }
}

function selectTurn(...reqTurnList: number[] | string[]) {
  turnList.value.clear();
  for (const turnIdx of reqTurnList) {
    if (isString(turnIdx)) {
      turnList.value.add(parseInt(turnIdx));
    } else {
      turnList.value.add(turnIdx);
    }
  }
}

function selectAll(e: Event | true) {
  //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
  if (e !== true && isDropdownChildren(e)) {
    return;
  }

  if (turnList.value.size * 3 > maxTurn) {
    turnList.value.clear();
  } else {
    for (let i = 0; i < maxTurn; i++) {
      turnList.value.add(i);
    }
  }
}

function selectStep(begin: number, step: number) {
  turnList.value.clear();
  for (const idx of range(0, maxTurn)) {
    if ((idx - begin) % step == 0) {
      turnList.value.add(idx);
    }
  }
}

function toggleViewMaxTurn() {
  if (viewMaxTurn.value == flippedMaxTurn) {
    viewMaxTurn.value = maxTurn;
  } else {
    viewMaxTurn.value = flippedMaxTurn;
  }
}

async function repeatGeneralCommand(amount: number) {
  try {
    await SammoAPI.Command.RepeatCommand({ amount });
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }
  await reloadCommandList();
}

async function pushGeneralCommand(amount: number) {
  try {
    await SammoAPI.Command.PushCommand({ amount });
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }
  await reloadCommandList();
}


function pushGeneralCommandSingle(e: Event) {
  //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
  if (isDropdownChildren(e)) {
    return;
  }
  if (!isEditMode.value) {
    return;
  }
  void pushGeneralCommand(1);
}

function pullGeneralCommandSingle(e: Event) {
  //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
  if (isDropdownChildren(e)) {
    return;
  }
  if (!isEditMode.value) {
    return;
  }
  void pushGeneralCommand(-1);
}


async function reloadCommandList() {
  let result: ReservedCommandResponse;
  try {
    result = await SammoAPI.Command.GetReservedCommand();
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }

  let yearMonth = joinYearMonth(result.year, result.month);

  const turnTime = parseTime(result.turnTime);
  let nextTurnTime = new Date(turnTime);

  const autorunLimitYearMonth = result.autorun_limit ?? yearMonth - 1;
  const [autorunLimitYear, autorunLimitMonth] = parseYearMonth(
    autorunLimitYearMonth
  );

  reservedCommandList.value = [];
  for (const obj of result.turn) {
    const [year, month] = parseYearMonth(yearMonth);
    let tooltip: string[] = [];
    let style: Record<string, unknown> = {};

    const brief = obj.brief;

    if (yearMonth <= autorunLimitYearMonth) {
      if (obj.brief == "휴식") {
        obj.brief = "휴식<small>(자율 행동)</small>";
      }
      style.color = "#aaffff";

      tooltip.push(
        `자율 행동 기간: ${autorunLimitYear}년 ${autorunLimitMonth}월까지`
      );
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
  clientNow.value = new Date();
  timeDiff.value = serverNow.value.getTime() - clientNow.value.getTime();
}

async function reserveCommandDirect(args: [number[], TurnObj][], reload = true): Promise<boolean> {
  const waiterList: Promise<ReserveCommandResponse | InvalidResponse>[] = [];

  const query: {
    turnList: number[],
    action: string,
    arg: Args
  }[] = [];
  for (const [turnList, { action, arg }] of args) {
    query.push({
      turnList,
      action,
      arg
    });
  }

  try {
    await SammoAPI.Command.ReserveBulkCommand(query);
    releaseSelectedTurnList();
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return false;
  }

  if (reload) {
    await reloadCommandList();
  }
  return true;
}

function getSelectedTurnList(): number[] {
  if (turnList.value.size) {
    return Array.from(turnList.value);
  }
  if (prevTurnList.value.size) {
    return Array.from(prevTurnList.value);
  }
  return [0];
}

function releaseSelectedTurnList() {
  if (turnList.value.size > 0) {
    prevTurnList.value.clear();
    for (const v of turnList.value) {
      prevTurnList.value.add(v);
    }
    turnList.value.clear();
  }
}

async function reserveCommand() {
  let reqTurnList: number[] = getSelectedTurnList();

  const commandName = selectedCommand.value.value;

  if (listReqArgCommand.has(commandName)) {
    document.location.href = stringifyUrl({
      url: "v_processing.php",
      query: {
        command: commandName,
        turnList: reqTurnList.join("_"),
      },
    });
    return;
  }

  try {
    const result = await SammoAPI.Command.ReserveCommand<ReserveCommandResponse>({
      turnList: reqTurnList,
      action: commandName,
    });

    storedActionsHelper.pushRecentActions({
      action: commandName,
      brief: result.brief,
      arg: {}
    });

    releaseSelectedTurnList();


  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }
  await reloadCommandList();
}

function chooseCommand(val?: string) {
  if (!val) {
    return;
  }
  selectedCommand.value = invCommandMap[val];
  void reserveCommand();
}

const commandQuickReserveForm = ref<InstanceType<typeof CommandSelectForm> | null>(null);

const currentQuickReserveTarget = ref(-1);
function chooseQuickReserveCommand(val?: string) {
  if (!val) {
    return;
  }
  selectedCommand.value = invCommandMap[val];
  turnList.value.clear();
  turnList.value.add(currentQuickReserveTarget.value);
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


watch(isEditMode, newEditMode => {
  localStorage.setItem(editModeKey, newEditMode ? '1' : '0');
  if (newEditMode) {
    commandQuickReserveForm.value?.close();
    currentQuickReserveTarget.value = -1;
  }
  else {
    commandSelectForm.value?.close();
  }
});

const emptyTurnObj: TurnObj = { action: '휴식', brief: '휴식', arg: {} };

const storedActionsHelper = new StoredActionsHelper(staticValues.serverNick, 'general', staticValues.mapName, staticValues.unitSet);

const recentActions = storedActionsHelper.recentActions;
const storedActions = storedActionsHelper.storedActions;

async function eraseSelectedTurnList(releaseSelect = true): Promise<boolean> {
  const result = await reserveCommandDirect([[
    getSelectedTurnList(),
    emptyTurnObj
  ]]);
  if (releaseSelect) {
    releaseSelectedTurnList();
  }
  return result;
}


function refineQueryActions(): [number[], TurnObj][] {
  const reqTurnList = getSelectedTurnList();
  const selectedMinTurnIdx = unwrap(min<number>(reqTurnList));
  const buffer: [number[], TurnObj][] = [];
  for (const rawTurnIdx of reqTurnList) {
    const turnIdx = rawTurnIdx - selectedMinTurnIdx;
    const rawAction = reservedCommandList.value[rawTurnIdx]
    buffer.push([[turnIdx], {
      action: rawAction.action,
      arg: clone(rawAction.arg),
      brief: rawAction.brief
    }]);
  }
  return buffer;
}

function amplifyQueryActions(rawActions: [number[], TurnObj][], reqTurnList: number[]): [number[], TurnObj][] {
  if (reqTurnList.length < 1) {
    return [];
  }

  let minQueryIdx = maxTurn;
  let maxQueryIdx = 0;
  for (const [turnList] of rawActions) {
    for (const turnIdx of turnList) {
      minQueryIdx = Math.min(minQueryIdx, turnIdx);
      maxQueryIdx = Math.max(maxQueryIdx, turnIdx);
    }
  }
  const queryLength = maxQueryIdx - minQueryIdx + 1;

  const queryTurnList: number[] = [reqTurnList[0]];
  for (const reqTurnIdx of reqTurnList) {
    const last = queryTurnList[queryTurnList.length - 1];
    if (reqTurnIdx < last + queryLength) {
      continue;
    }
    queryTurnList.push(reqTurnIdx);
  }

  const actions: [number[], TurnObj][] = [];
  for (const [baseTurnList, action] of rawActions) {
    const subTurnList: number[] = [];
    for (const baseTurnIdx of baseTurnList) {
      for (const queryTurnIdx of queryTurnList) {
        const targetTurn = baseTurnIdx + queryTurnIdx;
        if (targetTurn >= maxTurn) {
          continue;
        }
        subTurnList.push(baseTurnIdx + queryTurnIdx);
      }
    }
    if (subTurnList.length == 0) {
      continue;
    }
    actions.push([subTurnList, action]);
  }

  return actions;
}

const clipboard = ref<[number[], TurnObj][] | undefined>(undefined);

async function clipboardCut(releaseSelect = true) {
  clipboardCopy(false);
  return eraseSelectedTurnList(releaseSelect);
}

function clipboardCopy(releaseSelect = true) {
  clipboard.value = refineQueryActions();
  if (releaseSelect) {
    releaseSelectedTurnList();
  }
}

async function clipboardPaste(releaseSelect = true) {
  const rawActions = clipboard.value;
  if (rawActions === undefined) {
    return;
  }

  const actions = amplifyQueryActions(rawActions, getSelectedTurnList());
  if (actions.length === 0) {
    return;
  }

  const result = await reserveCommandDirect(actions);
  if (releaseSelect) {
    releaseSelectedTurnList();
  }
  return result;
}

async function subRepeatCommand(releaseSelect = true): Promise<boolean> {
  const reqTurnList = getSelectedTurnList().sort((a, b) => (a - b));
  const selectedMinTurnIdx = reqTurnList[0];
  const selectedMaxTurnIdx = reqTurnList[reqTurnList.length - 1];
  const queryLength = selectedMaxTurnIdx - selectedMinTurnIdx + 1;

  const rawActions = refineQueryActions();
  const actions = amplifyQueryActions(rawActions, range(selectedMinTurnIdx, maxTurn, queryLength));

  const result = await reserveCommandDirect(actions);
  if (releaseSelect) {
    releaseSelectedTurnList();
  }
  return result;
}


async function eraseAndPullCommand(releaseSelect = true): Promise<boolean> {
  const reqTurnList = getSelectedTurnList().sort((a, b) => (a - b));
  const selectedMinTurnIdx = reqTurnList[0];
  const selectedMaxTurnIdx = reqTurnList[reqTurnList.length - 1];
  const queryLength = selectedMaxTurnIdx - selectedMinTurnIdx + 1;

  if (selectedMinTurnIdx === 0) {
    await pushGeneralCommand(-queryLength);
    return true;
  }

  if (selectedMinTurnIdx + queryLength == maxTurn) {
    return eraseSelectedTurnList(releaseSelect);
  }

  const actions: [number[], TurnObj][] = [];


  const emptyTurnList: number[] = [];

  for (const srcTurnIdx of range(selectedMinTurnIdx + queryLength, maxTurn)) {
    const rawAction = reservedCommandList.value[srcTurnIdx];
    if (rawAction.action == emptyTurnObj.action) {
      emptyTurnList.push(srcTurnIdx - queryLength);
      continue;
    }
    actions.push([[srcTurnIdx - queryLength], {
      action: rawAction.action,
      arg: rawAction.arg,
      brief: rawAction.brief
    }]);
  }

  emptyTurnList.push(...range(maxTurn - queryLength, maxTurn));
  actions.push([emptyTurnList, emptyTurnObj]);

  const result = await reserveCommandDirect(actions);
  if (releaseSelect) {
    releaseSelectedTurnList();
  }
  return result;
}

async function pushEmptyCommand(releaseSelect = true): Promise<boolean> {
  const reqTurnList = getSelectedTurnList().sort((a, b) => (a - b));
  const selectedMinTurnIdx = reqTurnList[0];
  const selectedMaxTurnIdx = reqTurnList[reqTurnList.length - 1];
  const queryLength = selectedMaxTurnIdx - selectedMinTurnIdx + 1;

  if (selectedMinTurnIdx === 0) {
    await pushGeneralCommand(queryLength);
    return true;
  }

  if (selectedMaxTurnIdx == maxTurn) {
    return eraseSelectedTurnList(releaseSelect);
  }

  const actions: [number[], TurnObj][] = [];


  const emptyTurnList: number[] = [];

  for (const srcTurnIdx of range(selectedMinTurnIdx, maxTurn - queryLength)) {
    const rawAction = reservedCommandList.value[srcTurnIdx];
    if (rawAction.action == emptyTurnObj.action) {
      emptyTurnList.push(srcTurnIdx + queryLength);
      continue;
    }
    actions.push([[srcTurnIdx + queryLength], {
      action: rawAction.action,
      arg: rawAction.arg,
      brief: rawAction.brief
    }]);
  }

  emptyTurnList.push(...range(selectedMinTurnIdx, selectedMinTurnIdx + queryLength));
  actions.push([emptyTurnList, emptyTurnObj]);

  const result = await reserveCommandDirect(actions);
  if (releaseSelect) {
    releaseSelectedTurnList();
  }
  return result;
}

function setStoredActions() {
  const actions = refineQueryActions();
  const turnBrief: string[] = [];
  for (const [subTurnList, action] of actions) {
    const actionName = action.action.split('_');
    const actionShortName = actionName.length == 1 ? actionName[0] : actionName[1];
    turnBrief.push(repeat(actionShortName[0], subTurnList.length));
  }
  const nickName = trim(prompt('선택한 턴들의 별명을 지어주세요', turnBrief.join()) ?? '');
  if (nickName == '') {
    return;
  }

  storedActionsHelper.setStoredActions(nickName, actions);
  releaseSelectedTurnList();
}

function deleteStoredActions(actionKey: string) {
  storedActionsHelper.deleteStoredActions(actionKey)
}

async function useStoredAction(rawActions: [number[], TurnObj][]) {
  const reqTurnList = getSelectedTurnList().sort((a, b) => (a - b));
  const actions = amplifyQueryActions(rawActions, reqTurnList)
  const result = await reserveCommandDirect(actions);
  releaseSelectedTurnList();
  return result;
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
    minmax(20px, 0.8fr) minmax(75px, 2.4fr) minmax(40px, 0.9fr)
    4.8fr minmax(28px, 0.8fr);
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