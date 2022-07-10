<template>
  <div class="commandBox">
    <div class="only1000px bg1 center row gx-0" style="height: 24px; font-size: 1.2em">
      <div class="col-5 align-self-center text-end">{{ officer.officerLevelText }} :</div>
      <div
        class="col-7 align-self-center"
        :style="{
          color: getNpcColor(officer.npcType ?? 0),
        }"
      >
        {{ officer.name }}
      </div>
    </div>
    <div :class="['row', 'controlPad', props.targetIsMe ? 'targetIsMe' : 'targetIsNotMe']">
      <div class="col-3 col-md-12 order-md-last">
        <div class="d-grid mb-1 py-1 only500px bg1 center">
          <div
            :style="{
              color: getNpcColor(officer.npcType ?? 0),
              fontSize: '1.2em',
            }"
          >
            {{ officer.name }}
          </div>
          <div>{{ officer.officerLevelText }}</div>
        </div>
        <div class="row gx-1 gy-1 py-1">
          <div class="col-md-4 mx-0 mb-0 mt-1 d-grid">
            <div class="alert alert-primary mb-0 center" style="padding: 0.5rem 0">
              <SimpleClock :serverTime="parseTime(props.date)" />
            </div>
          </div>

          <div class="col-md-4 d-grid">
            <BButton variant="secondary" @click="isEditMode = !isEditMode">
              {{ isEditMode ? "일반 모드" : "고급 모드" }}
            </BButton>
          </div>

          <BDropdown class="col-md-4" text="반복">
            <BDropdownItem v-for="turnIdx in maxPushTurn" :key="turnIdx" @click="repeatNationCommand(turnIdx)">
              {{ turnIdx }}턴
            </BDropdownItem>
          </BDropdown>

          <template v-if="isEditMode">
            <BDropdown class="col-md-4" left text="범위">
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

            <BDropdown class="col-md-4" left text="보관함">
              <BDropdownItem
                v-for="[actionKey, actions] of storedActions"
                :key="actionKey"
                @click.self="useStoredAction(actions)"
              >
                {{ actionKey }}
                <BButton size="sm" @click.prevent="deleteStoredActions(actionKey)"> 삭제 </BButton>
              </BDropdownItem>
            </BDropdown>

            <div class="col-md-4 d-grid">
              <BDropdown right text="최근">
                <BDropdownItem
                  v-for="(action, idx) in Array.from(recentActions.values()).reverse()"
                  :key="idx"
                  @click="void reserveCommandDirect([[queryActionHelper.getSelectedTurnList(), action]])"
                >
                  {{ action.brief }}
                </BDropdownItem>
              </BDropdown>
            </div>
          </template>

          <BDropdown class="col-md-6" split text="당기기" @click="pullNationCommandSingle">
            <BDropdownItem v-for="turnIdx in maxPushTurn" :key="turnIdx" @click="pushNationCommand(-turnIdx)">
              {{ turnIdx }}턴
            </BDropdownItem>
          </BDropdown>
          <BDropdown class="col-md-6" split text="미루기" @click="pushNationCommandSingle">
            <BDropdownItem v-for="turnIdx in maxPushTurn" :key="turnIdx" @click="pushNationCommand(turnIdx)">
              {{ turnIdx }}턴
            </BDropdownItem>
          </BDropdown>
        </div>
      </div>
      <div class="col">
        <div :style="{ position: 'relative' }">
          <div
            class="commandQuickReserveFormAnchor bg-dark"
            :style="{
              position: 'absolute',
              top: `${basicModeRowHeight * currentQuickReserveTarget + 26}px`,
              width: '100%',
              zIndex: 9,
            }"
          >
            <CommandSelectForm
              ref="commandQuickReserveForm"
              v-model:activatedCategory="activatedCategory"
              :commandList="commandList"
              :hideClose="false"
              class="bg-dark"
              style="position: absolute"
              @onClose="chooseQuickReserveCommand($event)"
            />
          </div>
        </div>
        <div class="commandPad chiefReservedCommand">
          <div :class="['commandTable', isEditMode ? 'editMode' : 'singleMode']">
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
                v-for="(turnObj, turnIdx) in reservedCommandList"
                :key="turnIdx"
                :turnIdx="turnIdx"
                class="time_pad center f_tnum"
                :style="{
                  backgroundColor: 'black',
                  whiteSpace: 'nowrap',
                  overflow: 'hidden',
                  color: isDragSingle && selected.has(`${turnIdx}`) ? 'cyan' : undefined,
                }"
              >
                {{ turnObj.time }}
              </div>
            </DragSelect>
            <DragSelect
              v-slot="{ selected }"
              :style="{ ...rowGridStyle, display: isEditMode ? 'grid' : 'none' }"
              attribute="turnIdx"
              :disabled="!isEditMode"
              @dragStart="isDragToggle = true"
              @dragDone="
                isDragToggle = false;
                toggleTurn(...$event);
              "
            >
              <div
                v-for="(turnObj, turnIdx) in reservedCommandList"
                :key="turnIdx"
                :turnIdx="turnIdx"
                class="idx_pad center d-grid"
              >
                <BButton
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
              </div>
            </DragSelect>
            <div :style="rowGridStyle">
              <div v-for="(turnObj, turnIdx) in reservedCommandList.map(postFilterTurnBrief)" :key="turnIdx" class="turn_pad center">
                <span v-b-tooltip.hover class="turn_text" :style="turnObj.style" :title="turnObj.tooltip">
                  <!-- eslint-disable-next-line vue/no-v-html -->
                  <span v-html="turnObj.brief" />
                </span>
              </div>
            </div>
            <div :style="{ ...rowGridStyle, display: isEditMode ? 'none' : 'grid' }">
              <div v-for="turnIdx in range(props.maxTurn)" :key="turnIdx" class="action_pad d-grid">
                <BButton
                  :variant="turnIdx % 2 == 0 ? 'secondary' : 'dark'"
                  size="sm"
                  class="simple_action_btn bi bi-pencil"
                  @click="toggleQuickReserveForm(turnIdx)"
                />
              </div>
            </div>
          </div>
          <div style="position: relative">
            <CommandSelectForm
              ref="commandSelectForm"
              v-model:activatedCategory="activatedCategory"
              :commandList="commandList"
              class="bg-dark"
              :style="{ position: 'absolute', bottom: '0' }"
              @onClose="chooseCommand($event)"
            />
          </div>

          <div v-if="isEditMode" class="row gx-0">
            <div class="col-5 col-md-6 d-grid">
              <BDropdown left variant="light" :style="{ color: 'black' }" text="선택한 턴을">
                <BDropdownItem @click="clipboardCut"> <i class="bi bi-scissors" />&nbsp;잘라내기 </BDropdownItem>
                <BDropdownItem @click="clipboardCopy"> <i class="bi bi-files" />&nbsp;복사하기 </BDropdownItem>
                <BDropdownItem @click="clipboardPaste">
                  <i class="bi bi-clipboard-fill" />&nbsp;붙여넣기
                </BDropdownItem>
                <BDropdownDivider />
                <BDropdownItem @click="setStoredActions">
                  <i class="bi bi-bookmark-plus-fill" />&nbsp;보관하기
                </BDropdownItem>
                <BDropdownItem @click="subRepeatCommand">
                  <i class="bi bi-arrow-repeat" />&nbsp;반복하기
                </BDropdownItem>
                <BDropdownDivider />
                <BDropdownItem @click="eraseSelectedTurnList"> <i class="bi bi-eraser" />&nbsp;비우기 </BDropdownItem>
                <BDropdownItem @click="eraseAndPullCommand">
                  <i class="bi bi-arrow-bar-up" />&nbsp;지우고 당기기
                </BDropdownItem>
                <BDropdownItem @click="pushEmptyCommand">
                  <i class="bi bi-arrow-bar-down" />&nbsp;뒤로 밀기
                </BDropdownItem>
                <!-- 최근에 실행한 10턴 -->
              </BDropdown>
            </div>

            <div class="col-7 col-md-6 d-grid">
              <BButton variant="info" @click="toggleForm($event)"> 명령 선택 ▾ </BButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import addMinutes from "date-fns/esm/addMinutes";
import { stringifyUrl } from "query-string";
import { onMounted, ref, watch, type PropType, inject, type Ref } from "vue";
import { formatTime } from "@util/formatTime";
import { joinYearMonth } from "@util/joinYearMonth";
import { mb_strwidth } from "@util/mb_strwidth";
import { parseTime } from "@util/parseTime";
import { parseYearMonth } from "@util/parseYearMonth";
import { convertSearch초성 } from "@util/convertSearch초성";
import VueTypes from "vue-types";
import DragSelect from "@/components/DragSelect.vue";
import { isString, range, trim } from "lodash";
import { SammoAPI } from "@/SammoAPI";
import type { CommandItem, TurnObj } from "@/defs";
import { QueryActionHelper } from "@/util/QueryActionHelper";
import type { Args } from "@/processing/args";
import type { StoredActionsHelper } from "@/util/StoredActionsHelper";
import { getNpcColor } from "@/common_legacy";
import { BButton, BDropdownItem, BDropdownText, BButtonGroup, BDropdownDivider, BDropdown } from "bootstrap-vue-3";
import CommandSelectForm from "@/components/CommandSelectForm.vue";
import SimpleClock from "@/components/SimpleClock.vue";
import type { ChiefResponse } from "@/defs/API/NationCommand";
import { unwrap } from "@/util/unwrap";
import { unwrap_err } from "@/util/unwrap_err";
import type { GameConstStore } from "@/GameConstStore";
import { pick as josaPick } from "@/util/JosaUtil";

type TurnObjWithTime = TurnObj & {
  time: string;
  year?: number;
  month?: number;
  tooltip?: string;
  style?: Record<string, unknown>;
};

const props = defineProps({
  maxTurn: VueTypes.integer.isRequired,
  maxPushTurn: VueTypes.integer.isRequired,
  date: VueTypes.string.isRequired,
  year: VueTypes.integer.isRequired,
  month: VueTypes.integer.isRequired,
  turnTerm: VueTypes.integer.isRequired,
  turnTime: VueTypes.string.isRequired,
  targetIsMe: VueTypes.bool.isRequired,

  selectedTurn: {
    type: Object as PropType<Set<number>>,
    required: false,
    default: () => new Set(),
  },
  turn: {
    type: Array as PropType<TurnObj[]>,
    required: true,
  },
  commandList: {
    type: Object as PropType<ChiefResponse["commandList"]>,
    required: true,
  },
  troopList: {
    type: Object as PropType<ChiefResponse["troopList"]>,
    required: true,
  },
  officer: {
    type: Object as PropType<ChiefResponse["chiefList"][0]>,
    required: true,
  },
});
const basicModeRowHeight = 30;

const listReqArgCommand = new Set<string>();
for (const commandCategories of props.commandList) {
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

const selectedCommand = ref(props.commandList[0].values[0]);
for (const subCategory of props.commandList) {
  for (const command of subCategory.values) {
    if (command.searchText) {
      continue;
    }
    command.searchText = convertSearch초성(command.simpleName).join("|");
  }
}

const invCommandMap: Record<string, CommandItem> = {};
for (const category of props.commandList) {
  for (const command of category.values) {
    invCommandMap[command.value] = command;
  }
}

const rowGridStyle = ref({
  display: "grid",
  gridTemplateRows: `repeat(${props.maxTurn}, 30px)`,
});

const updated = ref(false);
const isDragSingle = ref(false);
const isDragToggle = ref(false);
const autorun_limit = ref<number | null>(null);

const emit = defineEmits<{
  (event: "raise-reload"): void;
  (event: "update:selectedTurn", value: Set<number>): void;
}>();

function triggerUpdateCommandList(type?: string) {
  console.log("try update", type);
  updated.value = false;
  setTimeout(() => {
    updateCommandList();
  }, 1);
}

function toggleTurn(...reqTurnList: number[] | string[]) {
  for (let turnIdx of reqTurnList) {
    if (isString(turnIdx)) {
      turnIdx = parseInt(turnIdx);
    }
    if (selectedTurnList.value.has(turnIdx)) {
      selectedTurnList.value.delete(turnIdx);
    } else {
      selectedTurnList.value.add(turnIdx);
    }
  }
  emit("update:selectedTurn", selectedTurnList.value);
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

async function repeatNationCommand(amount: number) {
  try {
    await SammoAPI.NationCommand.RepeatCommand({ amount });
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }
  emit("raise-reload");
}

function pushNationCommandSingle(e: Event) {
  //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
  if (isDropdownChildren(e)) {
    return;
  }
  void pushNationCommand(1);
}

function pullNationCommandSingle(e: Event) {
  //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
  if (isDropdownChildren(e)) {
    return;
  }
  void pushNationCommand(-1);
}

async function pushNationCommand(amount: number) {
  try {
    await SammoAPI.NationCommand.PushCommand({ amount });
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }
  emit("raise-reload");
}

const gameConstStore = unwrap_err(
  inject<Ref<GameConstStore>>("gameConstStore"),
  Error,
  "gameConstStore가 주입되지 않았습니다."
);

function postFilterTurnBrief(turnObj: TurnObjWithTime): TurnObjWithTime{
  if(turnObj.action != 'che_발령'){
    return turnObj;
  }
  const destGeneralID = unwrap(turnObj.arg.destGeneralID);
  if(!(destGeneralID in props.troopList)){
    return turnObj;
  }

  const troopName = props.troopList[destGeneralID];
  const destCityID = unwrap(turnObj.arg.destCityID);
  const destCityName = gameConstStore.value.cityConst[destCityID].name;
  const josaRo = josaPick(destCityName, "로");
  const brief = `《${troopName}》【${destCityName}】${josaRo} 발령`;
  const tooltip = `《${troopName}》${turnObj.brief}`;

  return {
    ...turnObj,
    brief,
    tooltip,
  }
}

const queryActionHelper = new QueryActionHelper(props.maxTurn);
const reservedCommandList = queryActionHelper.reservedCommandList;
const prevSelectedTurnList = queryActionHelper.prevSelectedTurnList;
const selectedTurnList = queryActionHelper.selectedTurnList;

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
    await SammoAPI.NationCommand.ReserveBulkCommand(query);
    queryActionHelper.releaseSelectedTurnList();
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return false;
  }

  if (reload) {
    emit("raise-reload");
  }
  return true;
}

function updateCommandList() {
  if (updated.value) {
    return;
  }
  console.log("do update!");
  const _reservedCommandList: TurnObjWithTime[] = [];
  let yearMonth = joinYearMonth(props.year, props.month);

  const turnTime = parseTime(props.turnTime);
  let nextTurnTime = new Date(turnTime);

  const autorunLimitYearMonth = autorun_limit.value ?? yearMonth - 1;
  const [autorunLimitYear, autorunLimitMonth] = parseYearMonth(autorunLimitYearMonth);

  for (const obj of props.turn) {
    const [year, month] = parseYearMonth(yearMonth);
    let tooltip: string[] = [];
    let style: Record<string, unknown> = {};

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

    _reservedCommandList.push({
      ...obj,
      year,
      month,
      time: formatTime(nextTurnTime, props.turnTerm >= 5 ? "HH:mm" : "mm:ss"),
      tooltip: tooltip.length == 0 ? undefined : tooltip.join("\n"),
      style,
    });

    yearMonth += 1;
    nextTurnTime = addMinutes(nextTurnTime, props.turnTerm);
  }
  reservedCommandList.value = _reservedCommandList;
  updated.value = true;
}

async function reserveCommand() {
  const reqTurnList = queryActionHelper.getSelectedTurnList();
  const commandName = selectedCommand.value.value;

  if (listReqArgCommand.has(commandName)) {
    document.location.href = stringifyUrl({
      url: "v_processing.php",
      query: {
        command: commandName,
        turnList: reqTurnList.join("_"),
        is_chief: true,
      },
    });
    return;
  }

  try {
    const result = await SammoAPI.NationCommand.ReserveCommand({
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
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }
  emit("raise-reload");
}

function chooseCommand(val?: string) {
  if (val === undefined) {
    return;
  }
  selectedCommand.value = invCommandMap[val];
  void reserveCommand();
}

const emptyTurnObj: TurnObj = { action: "휴식", brief: "휴식", arg: {} };

const storedActionsHelper = inject("storedNationActionsHelper") as StoredActionsHelper;

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

async function subRepeatCommand(releaseSelect = true): Promise<boolean> {
  const reqTurnList = queryActionHelper.getSelectedTurnList();
  const selectedMinTurnIdx = reqTurnList[0];
  const selectedMaxTurnIdx = reqTurnList[reqTurnList.length - 1];
  const queryLength = selectedMaxTurnIdx - selectedMinTurnIdx + 1;

  const rawActions = queryActionHelper.extractQueryActions();
  const actions = queryActionHelper.amplifyQueryActions(
    rawActions,
    range(selectedMinTurnIdx, props.maxTurn, queryLength)
  );

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
    await pushNationCommand(-queryLength);
    return true;
  }

  if (selectedMinTurnIdx + queryLength == props.maxTurn) {
    return eraseSelectedTurnList(releaseSelect);
  }

  const actions: [number[], TurnObj][] = [];

  const emptyTurnList: number[] = [];

  for (const srcTurnIdx of range(selectedMinTurnIdx + queryLength, props.maxTurn)) {
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

  emptyTurnList.push(...range(props.maxTurn - queryLength, props.maxTurn));
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
    await pushNationCommand(queryLength);
    return true;
  }

  if (selectedMaxTurnIdx == props.maxTurn) {
    return eraseSelectedTurnList(releaseSelect);
  }

  const actions: [number[], TurnObj][] = [];

  const emptyTurnList: number[] = [];

  for (const srcTurnIdx of range(selectedMinTurnIdx, props.maxTurn - queryLength)) {
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

function getQueryActionHelper(): QueryActionHelper {
  return queryActionHelper;
}

function getStoredActionHeler(): StoredActionsHelper {
  return storedActionsHelper;
}

defineExpose({
  useStoredAction,
  deleteStoredActions,
  clipboardCut,
  clipboardCopy,
  clipboardPaste,
  getQueryActionHelper,
  getStoredActionHeler,
});

watch(
  () => props.date,
  () => {
    triggerUpdateCommandList("date");
  }
);
watch(
  () => props.year,
  () => {
    triggerUpdateCommandList("year");
  }
);
watch(
  () => props.month,
  () => {
    triggerUpdateCommandList("month");
  }
);
watch(
  () => props.turnTime,
  () => {
    triggerUpdateCommandList("turnTime");
  }
);
watch(
  () => props.commandList,
  () => {
    triggerUpdateCommandList("commandList");
  }
);
watch(
  () => props.selectedTurn,
  (val: Set<number>) => {
    console.log(val);
    if (val === selectedTurnList.value) {
      console.log("pass!");
      return;
    }
    selectedTurnList.value.clear();
    for (const t of val.values()) {
      selectedTurnList.value.add(t);
    }
  }
);

watch(selectedTurnList, () => {
  console.log(selectedTurnList.value);
  emit("update:selectedTurn", selectedTurnList.value);
});

const commandQuickReserveForm = ref<InstanceType<typeof CommandSelectForm> | null>(null);
const commandSelectForm = ref<InstanceType<typeof CommandSelectForm> | null>(null);

const currentQuickReserveTarget = ref(-1);
function chooseQuickReserveCommand(val?: string) {
  if (!val) {
    return;
  }
  selectedCommand.value = invCommandMap[val];
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

const isEditMode = storedActionsHelper.isEditMode;
watch(isEditMode, (newEditMode) => {
  if (newEditMode) {
    commandQuickReserveForm.value?.close();
    currentQuickReserveTarget.value = -1;
  } else {
    commandSelectForm.value?.close();
  }
});
function toggleForm($event: Event): void {
  $event.preventDefault();

  const form = commandSelectForm.value;
  if (!form) {
    return;
  }
  form.toggle();
}

onMounted(() => {
  updateCommandList();
});
</script>
<style lang="scss">
@import "@scss/common/break_500px.scss";
@import "@scss/common/variables.scss";
@import "@scss/common/bootswatch_custom_variables.scss";

.chiefReservedCommand {
  background-color: $gray-900;

  .commandTable.editMode {
    width: 100%;
    display: grid;
    grid-template-columns: minmax(39.67px, 1fr) minmax(28px, 1fr) 5fr;
    //30, 70, 37.65, 160
  }

  .commandTable.singleMode {
    width: 100%;
    display: grid;
    grid-template-columns: minmax(39.67px, 1fr) 5fr minmax(28px, 1fr);
    //30, 70, 160, 37.65
  }

  @include media-1000px {
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

  @include media-500px {
    .dropdown-item {
      padding: 8px;
    }

    .multiselect__content-wrapper {
      margin-left: calc(-100% / 7 * 2);
      width: calc(100% / 7 * 12);
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

  .month_pad:hover {
    text-decoration: underline;
    cursor: pointer;
  }

  .month_pad,
  .time_pad,
  .turn_pad {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .turn_pad {
    white-space: nowrap;
  }

  .turn_pad .turn_text {
    display: inline-block;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
  }
}
</style>
