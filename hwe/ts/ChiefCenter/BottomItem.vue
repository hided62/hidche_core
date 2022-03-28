<template>
  <div
    v-for="(officer, _idx) in [chiefList[chiefLevel]]"
    :key="_idx"
    :class="[`chiefBox${chiefLevel}`, 'subRows']"
    :style="style"
    @click="$emit('click', this)"
  >
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
<script lang="ts">
import { getNpcColor } from "@/common_legacy";
import { mb_strwidth } from "@/util/mb_strwidth";
import { defineComponent } from "vue";
import VueTypes from "vue-types";

export default defineComponent({
  props: {
    chiefLevel: VueTypes.integer.isRequired,
    style: VueTypes.object.isRequired,
    chiefList: VueTypes.object.isRequired,
    isMe: VueTypes.bool.isRequired,
  },
  emits: ["click"],
  methods: {
    mb_strwidth,
    getNpcColor,
  },
});
</script>
