<template>
  <div style="position: relative">
    <DragSelect
      v-slot="{ selected }"
      :class="['subRows', 'chiefCommand']"
      :style="style"
      :disabled="!props.officer || !isEditMode"
      attribute="turnIdx"
      @dragStart="dragStart()"
      @dragDone="dragDone(...$event)"
    >
      <div class="bg1 center row gx-0" style="font-size: 1.2em">
        <div class="col-5 align-self-center text-end">
          {{ officer ? `${officer.officerLevelText} : ` : "" }}
        </div>
        <div
          class="col-7 align-self-center"
          :style="{
            color: getNPCColor(officer?.npcType ?? 0),
          }"
        >
          {{ officer?.name }}
        </div>
      </div>
      <div v-for="vidx in maxTurn" :key="vidx" :turnIdx="vidx" class="row c-bg2 gx-0">
        <div
          :class="[
            'col-2',
            'time_pad',
            'f_tnum',
            (isDragToggle || isCopyButtonShown) && selected.has(vidx.toString()) ? 'inverted' : undefined,
          ]"
        >
          {{ turnTimes[vidx - 1] }}
        </div>
        <div v-if="!officer || !officer.turn || !(vidx - 1 in officer.turn)" class="center" />
        <div
          v-else
          class="tableCell align-self-center col-10 center turn_pad"
          :style="{
            fontSize:
              mb_strwidth(officer.turn[vidx - 1].brief) > 28
                ? `${28 / mb_strwidth(officer.turn[vidx - 1].brief)}em`
                : undefined,
          }"
        >
          {{ officer.turn[vidx - 1].brief }}
        </div>
      </div>
    </DragSelect>
    <div
      class="hoverPanel d-grid"
      :style="{
        position: 'absolute',
        display: isCopyButtonShown ? 'grid !important' : 'none !important',
        top: `${btnPos * 30 + 25}px`,
        right: '10px',
      }"
    >
      <BButton ref="btnCopy" variant="primary" @blur="hideCopyButton()" @click="tryCopy()"> 복사하기 </BButton>
      <BButton ref="btnCopy" @blur="hideCopyButton()" @click="tryTextCopy()"> 텍스트 복사</BButton>
    </div>
  </div>
</template>
<script setup lang="ts">
import { getNPCColor } from "@/utilGame";
import { formatTime } from "@/util/formatTime";
import { mb_strwidth } from "@/util/mb_strwidth";
import { parseTime } from "@/util/parseTime";
import type { StoredActionsHelper } from "@/util/StoredActionsHelper";
import addMinutes from "date-fns/esm/addMinutes/index";
import { range } from "lodash-es";
import { inject, onMounted, ref, type PropType } from "vue";
import VueTypes from "vue-types";
import DragSelect from "@/components/DragSelect.vue";
import { BButton } from "bootstrap-vue-next";
import { QueryActionHelper } from "@/util/QueryActionHelper";
import type { ChiefResponse } from "@/defs/API/NationCommand";

const props = defineProps({
  style: VueTypes.object.isRequired,
  officer: {
    type: Object as PropType<ChiefResponse["chiefList"][0]>,
    default: undefined,
  },
  turnTerm: VueTypes.integer.isRequired,
  maxTurn: VueTypes.integer.isRequired,
});

const btnPos = ref(0);
const btnCopy = ref<InstanceType<typeof BButton> | null>(null);

const storedActionsHelper = inject<StoredActionsHelper>("storedNationActionsHelper");
const isEditMode = storedActionsHelper?.isEditMode ?? ref(false);
const isDragToggle = ref(false);

const isCopyButtonShown = ref(false);

const queryActionHelper = new QueryActionHelper(props.maxTurn);
const selectedTurnList = queryActionHelper.selectedTurnList;

onMounted(() => {
  if (props.officer === undefined) {
    return;
  }
  queryActionHelper.reservedCommandList.value = props.officer.turn.map((rawTurn) => {
    return {
      ...rawTurn,
      time: "",
    };
  });
  console.log(queryActionHelper.reservedCommandList.value);
});

function dragStart() {
  isDragToggle.value = true;
}

function hideCopyButton() {
  setTimeout(() => {
    isCopyButtonShown.value = false;
  }, 1);
}

function eqSet<T>(as: Set<T>, bs: Set<T>) {
  if (as.size !== bs.size) return false;
  for (var a of as) if (!bs.has(a)) return false;
  return true;
}

function dragDone(...rawSelectedTurn: string[]) {
  if (rawSelectedTurn.length === 0) {
    return;
  }
  const newSelectedTurnList = new Set<number>();

  let maxPos = -1;
  for (const rawIdx of rawSelectedTurn) {
    const idx = parseInt(rawIdx) - 1;
    newSelectedTurnList.add(idx);
    maxPos = Math.max(maxPos, idx);
  }

  btnPos.value = maxPos;

  isDragToggle.value = false;
  if (newSelectedTurnList.size == 1 && eqSet(selectedTurnList.value, newSelectedTurnList)) {
    isCopyButtonShown.value = false;
    return;
  }
  selectedTurnList.value = newSelectedTurnList;
  isCopyButtonShown.value = true;
  setTimeout(() => {
    (btnCopy.value?.$el as HTMLButtonElement).focus();
  }, 0);
}

function tryCopy() {
  const actions = queryActionHelper.extractQueryActions();
  isCopyButtonShown.value = false;

  if (!storedActionsHelper) {
    return;
  }
  storedActionsHelper.clipboard.value = [...actions];
}

const removeTagRegEx = /<[^>]*>?/g;

function tryTextCopy() {
  const rawActions = queryActionHelper.extractQueryActions();
  isCopyButtonShown.value = false;

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
}

const turnTimes = ref<string[]>([]);

if (!props.officer || !props.officer.turnTime) {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  for (const _ of range(props.maxTurn)) {
    turnTimes.value.push("\xa0");
  }
} else {
  const baseTurnTime = parseTime(props.officer.turnTime);
  for (const idx of range(props.officer.turn.length)) {
    turnTimes.value.push(
      formatTime(addMinutes(baseTurnTime, idx * props.turnTerm), props.turnTerm >= 5 ? "HH:mm" : "mm:ss")
    );
  }
}
</script>
