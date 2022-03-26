<template>
  <div style="position:relative">
    <DragSelect
      :class="['subRows', 'chiefCommand']"
      :style="style"
      :disabled="!props.officer || !isEditMode"
      attribute="turnIdx"
      @dragStart="dragStart()"
      @dragDone="dragDone(...$event)"
      v-slot="{ selected }"
    >
      <div class="bg1 center row gx-0" style="font-size: 1.2em">
        <div
          class="col-5 align-self-center text-end"
        >{{ officer ? `${officer.officerLevelText} : ` : "" }}</div>
        <div
          class="col-7 align-self-center"
          :style="{
            color: getNpcColor(officer?.npcType ?? 0),
          }"
        >{{ officer?.name }}</div>
      </div>
      <div :turnIdx="vidx" class="row c-bg2 gx-0" v-for="vidx in maxTurn" :key="vidx">
        <div
          :class="['col-2', 'time_pad', 'f_tnum', ((isDragToggle || isCopyButtonShown) && selected.has(vidx.toString())) ? 'inverted' : undefined]"
        >{{ turnTimes[vidx - 1] }}</div>
        <div class="center" v-if="!officer || (!officer.turn) || !(vidx - 1 in officer.turn)"></div>
        <div
          v-else
          class="tableCell align-self-center col-10 center turn_pad"
          :style="{
            fontSize:
              mb_strwidth(officer.turn[vidx - 1].brief) > 28
                ? `${28 / mb_strwidth(officer.turn[vidx - 1].brief)}em`
                : undefined,
          }"
        >{{ officer.turn[vidx - 1].brief }}</div>
      </div>
    </DragSelect>
    <BButton
      ref="btnCopy"
      :style="{
        position: 'absolute',
        display: isCopyButtonShown ? 'block' : 'none',
        top: `${btnPos * 30 + 25}px`,
        right: '10px',
      }"
      @blur="isCopyButtonShown = false"
      @click="tryCopy()"
    >복사하기</BButton>
  </div>
</template>
<script setup lang="ts">
import { getNpcColor } from "@/common_legacy";
import type { ChiefResponse } from "@/defs";
import { formatTime } from "@/util/formatTime";
import { mb_strwidth } from "@/util/mb_strwidth";
import { parseTime } from "@/util/parseTime";
import type { StoredActionsHelper } from "@/util/StoredActionsHelper";
import addMinutes from "date-fns/esm/addMinutes/index";
import { range } from "lodash";
import { defineProps, inject, onMounted, ref, type PropType } from "vue";
import VueTypes from "vue-types";
import DragSelect from "@/components/DragSelect.vue";
import { BButton } from "bootstrap-vue-3";
import { QueryActionHelper } from "@/util/QueryActionHelper";


const props = defineProps({
  style: VueTypes.object.isRequired,
  officer: {
    type: Object as PropType<ChiefResponse["chiefList"][0]>,
  },
  turnTerm: VueTypes.integer.isRequired,
  maxTurn: VueTypes.integer.isRequired,
})

const btnPos = ref(0);
const btnCopy = ref<InstanceType<typeof BButton> | null>(null);

const storedActionsHelper = inject<StoredActionsHelper>('storedNationActionsHelper');
const isEditMode = (storedActionsHelper?.isEditMode) ?? ref(false);
const isDragToggle = ref(false);

const isCopyButtonShown = ref(false);

const queryActionHelper = new QueryActionHelper(props.maxTurn);
const selectedTurnList = queryActionHelper.selectedTurnList;

onMounted(() => {
  if (props.officer === undefined) {
    return;
  }
  queryActionHelper.reservedCommandList.value = props.officer.turn.map(rawTurn => {
    return {
      ...rawTurn,
      time: '',
    }
  });
  console.log(queryActionHelper.reservedCommandList.value);
});


function dragStart() {
  isDragToggle.value = true;
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
      formatTime(
        addMinutes(baseTurnTime, idx * props.turnTerm),
        props.turnTerm >= 5 ? "HH:mm" : "mm:ss"
      )
    );
  }
}
</script>
