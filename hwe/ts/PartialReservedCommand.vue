<template>
  <div class="commandPad">
    <div class="col alert alert-dark m-0 p-1 center">
      <h4 class="m-0">명령 목록</h4>
    </div>
    <div class="row gx-1">
      <div class="col d-grid">
        <BButton variant="secondary" @click="isEditMode = !isEditMode">
          {{ isEditMode ? "일반 모드로" : "고급 모드로" }}
        </BButton>
      </div>
      <div
        class="col alert alert-primary m-0 p-0"
        style="text-align: center; display: flex; justify-content: center; align-items: center"
      >
        <SimpleClock :serverTime="serverNow" />
      </div>
      <div class="col d-grid">
        <BDropdown right text="반복">
          <BDropdownItem v-for="turnIdx in maxPushTurn" :key="turnIdx" @click="repeatGeneralCommand(turnIdx)">
            {{ turnIdx }}턴
          </BDropdownItem>
        </BDropdown>
      </div>
    </div>

    <div v-if="isEditMode" class="row gx-1">
      <div class="col-4 d-grid">
        <BDropdown left text="범위">
          <BDropdownItem @click="queryActionHelper.selectTurn()"> 해제 </BDropdownItem>
          <BDropdownItem @click="queryActionHelper.selectAll()"> 모든턴 </BDropdownItem>
          <BDropdownItem @click="queryActionHelper.selectStep(0, 2)"> 홀수턴 </BDropdownItem>
          <BDropdownItem @click="queryActionHelper.selectStep(1, 2)"> 짝수턴 </BDropdownItem>
          <BDropdownDivider />

          <BDropdownText v-for="spanIdx in [3, 4, 5, 6, 7]" :key="spanIdx">
            {{ spanIdx }}턴 간격
            <br />
            <BButtonGroup>
              <BButton
                v-for="beginIdx in spanIdx"
                :key="beginIdx"
                class="ignoreMe"
                @click="queryActionHelper.selectStep(beginIdx - 1, spanIdx)"
              >
                {{ beginIdx }}
              </BButton>
            </BButtonGroup>
          </BDropdownText>
        </BDropdown>
      </div>

      <div class="col-4 d-grid">
        <BDropdown left text="보관함">
          <BDropdownItem
            v-for="[actionKey, actions] of storedActions"
            :key="actionKey"
            @click.self="useStoredAction(actions)"
          >
            {{ actionKey }}
            <BButton size="sm" @click.prevent="deleteStoredActions(actionKey)"> 삭제 </BButton>
          </BDropdownItem>
        </BDropdown>
      </div>

      <div class="col-4 d-grid">
        <BDropdown right text="최근 실행">
          <BDropdownItem
            v-for="(action, idx) of Array.from(recentActions.values()).reverse()"
            :key="idx"
            @click="void reserveCommandDirect([[queryActionHelper.getSelectedTurnList(), action]])"
          >
            {{ action.brief }}
          </BDropdownItem>
        </BDropdown>
      </div>

      <div class="col-5 d-grid">
        <BDropdown left variant="light" :style="{ color: 'black' }" text="선택한 턴을">
          <BDropdownItem @click="clipboardCut"> <i class="bi bi-scissors" />&nbsp;잘라내기 </BDropdownItem>
          <BDropdownItem @click="clipboardCopy"> <i class="bi bi-files" />&nbsp;복사하기 </BDropdownItem>
          <BDropdownItem @click="clipboardPaste"> <i class="bi bi-clipboard-fill" />&nbsp;붙여넣기 </BDropdownItem>
          <BDropdownDivider />
          <BDropdownItem @click="clipboardTextCopy"> <i class="bi bi-files" />&nbsp;텍스트 복사 </BDropdownItem>
          <BDropdownDivider />
          <BDropdownItem @click="setStoredActions">
            <i class="bi bi-bookmark-plus-fill" />&nbsp;보관하기
          </BDropdownItem>
          <BDropdownItem @click="subRepeatCommand"> <i class="bi bi-arrow-repeat" />&nbsp;반복하기 </BDropdownItem>
          <BDropdownDivider />
          <BDropdownItem @click="eraseSelectedTurnList"> <i class="bi bi-eraser" />&nbsp;비우기 </BDropdownItem>
          <BDropdownItem @click="eraseAndPullCommand">
            <i class="bi bi-arrow-bar-up" />&nbsp;지우고 당기기
          </BDropdownItem>
          <BDropdownItem @click="pushEmptyCommand"> <i class="bi bi-arrow-bar-down" />&nbsp;뒤로 밀기 </BDropdownItem>
          <!-- 최근에 실행한 10턴 -->
        </BDropdown>
      </div>

      <div class="col-7 d-grid">
        <BButton variant="info" :disabled="!commandList.length" @click="toggleForm($event)"> 명령 선택 ▾ </BButton>
      </div>
    </div>
    <CommandSelectForm
      ref="commandSelectForm"
      v-model:activatedCategory="activatedCategory"
      :commandList="commandList"
      @onClose="chooseCommand($event)"
    />

    <div :style="{ position: 'relative' }">
      <div
        class="bg-dark"
        :style="{
          position: 'absolute',
          top: `${basicModeRowHeight * currentQuickReserveTarget + 30}px`,
          width: '100%',
          zIndex: 9,
        }"
      >
        <CommandSelectForm
          ref="commandQuickReserveForm"
          v-model:activatedCategory="activatedCategory"
          :commandList="commandList"
          :hideClose="false"
          @onClose="chooseQuickReserveCommand($event)"
        />
      </div>
    </div>
    <div
      :class="{
        commandTable: true,
        isEditMode,
      }"
    >
      <DragSelect
        v-slot="{ selected }"
        :style="rowGridStyle"
        :disabled="!isEditMode"
        attribute="turnIdx"
        @dragStart="isDragToggle = true"
        @dragDone="
          isDragToggle = false;
          queryActionHelper.toggleTurn(...$event);
        "
      >
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)"
          :key="turnIdx"
          :turnIdx="turnIdx"
          class="idx_pad center d-grid"
        >
          <BButton
            v-if="isEditMode"
            size="sm"
            :variant="
              isDragToggle && selected.has(`${turnIdx}`)
                ? 'light'
                : selectedTurnList.has(turnIdx)
                ? 'info'
                : selectedTurnList.size == 0 && prevSelectedTurnList.has(turnIdx)
                ? 'success'
                : 'primary'
            "
          >
            {{ turnIdx + 1 }}
          </BButton>
          <div v-else class="plain-center">
            {{ turnIdx + 1 }}
          </div>
        </div>
      </DragSelect>
      <DragSelect
        v-slot="{ selected }"
        :style="rowGridStyle"
        attribute="turnIdx"
        :disabled="!isEditMode"
        @dragStart="isDragSingle = true"
        @dragDone="
          isDragSingle = false;
          queryActionHelper.selectTurn(...$event);
        "
      >
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)"
          :key="turnIdx"
          height="24"
          class="month_pad center"
          :turnIdx="turnIdx"
          :style="{
            'white-space': 'nowrap',
            'font-size': `${Math.min(14, (75 / (`${turnObj.year ?? 1}`.length + 8)) * 1.8)}px`,
            overflow: 'hidden',
            color: isDragSingle && selected.has(`${turnIdx}`) ? 'cyan' : undefined,
          }"
        >
          {{ turnObj.year ? `${turnObj.year}年` : "" }}
          {{ turnObj.month ? `${turnObj.month}月` : "" }}
        </div>
      </DragSelect>
      <div :style="rowGridStyle">
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)"
          :key="turnIdx"
          class="time_pad center"
          :style="{
            backgroundColor: 'black',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
          }"
        >
          {{ turnObj.time }}
        </div>
      </div>
      <div :style="rowGridStyle">
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)"
          :key="turnIdx"
          class="turn_pad center"
        >
          <span v-b-tooltip.hover class="turn_text" :style="turnObj.style" :title="turnObj.tooltip">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <span v-html="turnObj.brief" />
          </span>
        </div>
      </div>
      <div v-if="!isEditMode" :style="rowGridStyle">
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(0, viewMaxTurn)"
          :key="turnIdx"
          class="action_pad d-grid"
        >
          <BButton
            :variant="turnIdx % 2 == 0 ? 'secondary' : 'dark'"
            size="sm"
            class="simple_action_btn bi bi-pencil"
            :disabled="!commandList.length"
            @click="toggleQuickReserveForm(turnIdx)"
          />
        </div>
      </div>
    </div>
    <div class="row gx-1">
      <div class="col d-grid">
        <BDropdown :split="isEditMode" text="당기기" @click="pullGeneralCommandSingle">
          <BDropdownItem v-for="turnIdx in maxPushTurn" :key="turnIdx" @click="pushGeneralCommand(-turnIdx)">
            {{ turnIdx }}턴
          </BDropdownItem>
        </BDropdown>
      </div>
      <div class="col d-grid">
        <BDropdown :split="isEditMode" text="미루기" @click="pushGeneralCommandSingle">
          <BDropdownItem v-for="turnIdx in maxPushTurn" :key="turnIdx" @click="pushGeneralCommand(turnIdx)">
            {{ turnIdx }}턴
          </BDropdownItem>
        </BDropdown>
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
import { stringifyUrl } from "query-string";
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
import { BButton, BButtonGroup, BDropdownItem, BDropdown, BDropdownText, BDropdownDivider, useToast } from "bootstrap-vue-3";
import { StoredActionsHelper } from "./util/StoredActionsHelper";
import type { TurnObj } from "@/defs";
import type { Args } from "./processing/args";
import { QueryActionHelper } from "./util/QueryActionHelper";
import SimpleClock from "./components/SimpleClock.vue";
import type { ReservedCommandResponse } from "./defs/API/Command";
import { unwrap } from "./util/unwrap";

defineExpose({
  updateCommandTable,
  reloadCommandList,
})

const toasts = unwrap(useToast());

const { maxTurn, maxPushTurn } = staticValues;

const commandList = ref<CommandTableResponse['commandTable']>([]);
const listReqArgCommand = new Set<string>();

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

async function updateCommandTable(){
  try{
    const response = await SammoAPI.General.GetCommandTable();
    console.log(response);
    commandList.value = response.commandTable;
  }
  catch(e){
    console.error(e);
  }
}

watch(commandList, (commandList)=>{
  if(!commandList){
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
      listReqArgCommand.add(commandObj.value);
    }
  }

  invCommandMap.clear();
  for (const category of commandList) {
    for (const command of category.values) {
      invCommandMap.set(command.value, command);
    }
  }

  if(selectedCommand.value === nullCommand || !invCommandMap.has(selectedCommand.value.value)){
    selectedCommand.value = commandList[0].values[0];
  }
});

onMounted(() => {
  void updateCommandTable();
});

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

const queryActionHelper = new QueryActionHelper(staticValues.maxTurn);
const storedActionsHelper = new StoredActionsHelper(
  staticValues.serverNick,
  "general",
  staticValues.mapName,
  staticValues.unitSet
);

const reservedCommandList = queryActionHelper.reservedCommandList;
const prevSelectedTurnList = queryActionHelper.prevSelectedTurnList;
const selectedTurnList = queryActionHelper.selectedTurnList;

const isEditMode = storedActionsHelper.isEditMode;

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
    if(isString(e)){
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

async function pushGeneralCommand(amount: number) {
  try {
    await SammoAPI.Command.PushCommand({ amount });
  } catch (e) {
    if(isString(e)){
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

const serverNow = ref(new Date());

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
    if(isString(e)){
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

async function reserveCommandDirect(args: [number[], TurnObj][], reload = true): Promise<boolean> {
  const query: {
    turnList: number[];
    action: string;
    arg: Args;
  }[] = [];
  for (const [turnList, { action, arg }] of args) {
    query.push({
      turnList,
      action,
      arg,
    });
  }

  try {
    await SammoAPI.Command.ReserveBulkCommand(query);
    queryActionHelper.releaseSelectedTurnList();
  } catch (e) {
    if(isString(e)){
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    console.error(e);
    return false;
  }

  if (reload) {
    await reloadCommandList();
  }
  return true;
}

async function reserveCommand() {
  let reqTurnList: number[] = queryActionHelper.getSelectedTurnList();

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
    const result = await SammoAPI.Command.ReserveCommand({
      turnList: reqTurnList,
      action: commandName,
    });

    storedActionsHelper.pushRecentActions({
      action: commandName,
      brief: result.brief,
      arg: {},
    });

    queryActionHelper.releaseSelectedTurnList();
  } catch (e) {
    if(isString(e)){
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

function chooseCommand(val?: string) {
  if (!val) {
    return;
  }
  selectedCommand.value = unwrap(invCommandMap.get(val));
  void reserveCommand();
}

const commandQuickReserveForm = ref<InstanceType<typeof CommandSelectForm> | null>(null);

const currentQuickReserveTarget = ref(-1);
function chooseQuickReserveCommand(val?: string) {
  if (!val) {
    return;
  }
  selectedCommand.value = unwrap(invCommandMap.get(val));
  selectedTurnList.value.clear();
  selectedTurnList.value.add(currentQuickReserveTarget.value);
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

watch(isEditMode, (newEditMode) => {
  if (newEditMode) {
    commandQuickReserveForm.value?.close();
    currentQuickReserveTarget.value = -1;
  } else {
    commandSelectForm.value?.close();
  }
});

const emptyTurnObj: TurnObj = { action: "휴식", brief: "휴식", arg: {} };

const recentActions = storedActionsHelper.recentActions;
const storedActions = storedActionsHelper.storedActions;
const activatedCategory = storedActionsHelper.activatedCategory;

async function eraseSelectedTurnList(releaseSelect = true): Promise<boolean> {
  const result = await reserveCommandDirect([[queryActionHelper.getSelectedTurnList(), emptyTurnObj]]);
  if (releaseSelect) {
    queryActionHelper.releaseSelectedTurnList();
  }
  return result;
}

const clipboard = storedActionsHelper.clipboard;

async function clipboardCut(releaseSelect = true) {
  clipboardCopy(false);
  return eraseSelectedTurnList(releaseSelect);
}

function clipboardCopy(releaseSelect = true) {
  clipboard.value = queryActionHelper.extractQueryActions();
  if (releaseSelect) {
    queryActionHelper.releaseSelectedTurnList();
  }
}

async function clipboardPaste(releaseSelect = true) {
  const rawActions = clipboard.value;
  if (rawActions === undefined) {
    return;
  }

  const actions = queryActionHelper.amplifyQueryActions(rawActions, queryActionHelper.getSelectedTurnList());
  if (actions.length === 0) {
    return;
  }

  const result = await reserveCommandDirect(actions);
  if (releaseSelect) {
    queryActionHelper.releaseSelectedTurnList();
  }
  return result;
}

const removeTagRegEx = /<[^>]*>?/g;

function clipboardTextCopy(releaseSelect = true) {
  const rawActions = queryActionHelper.extractQueryActions();
  if (rawActions.length === 0) {
    return;
  }

  const actions = queryActionHelper.amplifyQueryActions(rawActions, queryActionHelper.getSelectedTurnList());
  if (actions.length === 0) {
    return;
  }

  const turnBriefs: [number, string][] = [];
  for (const action of actions) {
    const [turnIdxList, turnObj] = action;
    for (const turnIdx of turnIdxList) {
      turnBriefs.push([turnIdx, `${turnIdx + 1}턴 ${turnObj.brief.replace(removeTagRegEx, "")}`]);
    }
  }
  turnBriefs.sort((a, b) => a[0] - b[0]);

  const text = turnBriefs.map(([, brief]) => brief).join("\n");
  void navigator.clipboard.writeText(text);

  if (releaseSelect) {
    queryActionHelper.releaseSelectedTurnList();
  }
}

async function subRepeatCommand(releaseSelect = true): Promise<boolean> {
  const reqTurnList = queryActionHelper.getSelectedTurnList();
  const selectedMinTurnIdx = reqTurnList[0];
  const selectedMaxTurnIdx = reqTurnList[reqTurnList.length - 1];
  const queryLength = selectedMaxTurnIdx - selectedMinTurnIdx + 1;

  const rawActions = queryActionHelper.extractQueryActions();
  const actions = queryActionHelper.amplifyQueryActions(rawActions, range(selectedMinTurnIdx, maxTurn, queryLength));

  const result = await reserveCommandDirect(actions);
  if (releaseSelect) {
    queryActionHelper.releaseSelectedTurnList();
  }
  return result;
}

async function eraseAndPullCommand(releaseSelect = true): Promise<boolean> {
  const reqTurnList = queryActionHelper.getSelectedTurnList();
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
    actions.push([
      [srcTurnIdx - queryLength],
      {
        action: rawAction.action,
        arg: rawAction.arg,
        brief: rawAction.brief,
      },
    ]);
  }

  emptyTurnList.push(...range(maxTurn - queryLength, maxTurn));
  actions.push([emptyTurnList, emptyTurnObj]);

  const result = await reserveCommandDirect(actions);
  if (releaseSelect) {
    queryActionHelper.releaseSelectedTurnList();
  }
  return result;
}

async function pushEmptyCommand(releaseSelect = true): Promise<boolean> {
  const reqTurnList = queryActionHelper.getSelectedTurnList();
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
    actions.push([
      [srcTurnIdx + queryLength],
      {
        action: rawAction.action,
        arg: rawAction.arg,
        brief: rawAction.brief,
      },
    ]);
  }

  emptyTurnList.push(...range(selectedMinTurnIdx, selectedMinTurnIdx + queryLength));
  actions.push([emptyTurnList, emptyTurnObj]);

  const result = await reserveCommandDirect(actions);
  if (releaseSelect) {
    queryActionHelper.releaseSelectedTurnList();
  }
  return result;
}

function setStoredActions() {
  const actions = queryActionHelper.extractQueryActions();
  const turnBrief = new Map<number, string>();
  for (const [subTurnList, action] of actions) {
    const actionName = action.action.split("_");
    const actionShortName = actionName.length == 1 ? actionName[0] : actionName[1];
    for (const turnIdx of subTurnList) {
      turnBrief.set(turnIdx, actionShortName[0]);
    }
  }

  const turnBriefStr = Array.from(turnBrief.entries())
    .sort(([turnA], [turnB]) => turnA - turnB)
    .map(([, action]) => action)
    .join("");

  const nickName = trim(prompt("선택한 턴들의 별명을 지어주세요", turnBriefStr) ?? "");
  if (nickName == "") {
    return;
  }

  storedActionsHelper.setStoredActions(nickName, actions);
  queryActionHelper.releaseSelectedTurnList();
}

function deleteStoredActions(actionKey: string) {
  storedActionsHelper.deleteStoredActions(actionKey);
}

async function useStoredAction(rawActions: [number[], TurnObj][]) {
  const reqTurnList = queryActionHelper.getSelectedTurnList();
  const actions = queryActionHelper.amplifyQueryActions(rawActions, reqTurnList);
  const result = await reserveCommandDirect(actions);
  queryActionHelper.releaseSelectedTurnList();
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
