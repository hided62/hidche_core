<template>
  <div :class="[`chiefBox${chiefLevel}`, 'subRows']" :style="style" @click="onClick">
    <div
      class="bg1 nameHeader"
      :style="{
        color: getNpcColor(officer?.npcType ?? 0),
        textDecoration: isMe ? 'underline' : undefined,
      }"
    >
      {{ officer ? officer?.name ?? "-" : "" }}
    </div>
    <div class="bg1 center row gx-0">
      <div class="col">
        {{ officer?.officerLevelText }}
      </div>
      <div class="col">
        {{ officer ? (officer?.turnTime ?? "  -  ").slice(-5) : "" }}
      </div>
    </div>
    <div
      v-for="(turn, idx) in officer?.turn ?? []"
      :key="idx"
      class="tableCell align-self-center turn_pad"
      :style="{
        fontSize: mb_strwidth(turn.brief) > 28 ? `${28 / mb_strwidth(turn.brief)}em` : undefined,
      }"
    >
      {{ turn.brief }}
    </div>
  </div>
</template>
<script setup lang="ts">
import { getNpcColor } from "@/common_legacy";
import type { ChiefResponse } from "@/defs/API/NationCommand";
import { mb_strwidth } from "@/util/mb_strwidth";
import type { PropType } from "vue";
import VueTypes from "vue-types";

defineProps({
    chiefLevel: VueTypes.integer.isRequired,
    style: VueTypes.object.isRequired,
    officer: {
      type: Object as PropType<ChiefResponse['chiefList'][0]>,
      default: undefined,
    },
    isMe: VueTypes.bool.isRequired,
  });

const emit = defineEmits<{
  (event: "click", value: HTMLElement): void,
}>();

function onClick(event: MouseEvent){
  if(!event.target){
    return;
  }
  const elem = event.target as HTMLElement;
  emit("click", elem);
}
</script>
