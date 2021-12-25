<template>
  <div :class="['subRows', 'chiefCommand']" :style="style">
    <div class="bg1 center row gx-0" style="font-size: 1.2em">
      <div class="col-5 align-self-center text-end">
        {{ officer ? `${officer.officerLevelText} : ` : "" }}
      </div>
      <div
        class="col-7 align-self-center"
        :style="{
          color: getNpcColor(officer?.npcType ?? 0),
        }"
      >
        {{ officer?.name }}
      </div>
    </div>
    <div class="row c-bg2 gx-0" v-for="vidx in maxTurn" :key="vidx">
      <div class="col-2 time_pad f_tnum">
        {{ turnTimes[vidx - 1] }}
      </div>
      <div
        class="center"
        v-if="!officer || (!officer.turn) || !(vidx - 1 in officer.turn)"
      ></div>
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
  </div>
</template>
<script lang="ts">
import { getNpcColor } from "@/common_legacy";
import { ChiefResponse } from "@/defs";
import { formatTime } from "@/util/formatTime";
import { mb_strwidth } from "@/util/mb_strwidth";
import { parseTime } from "@/util/parseTime";
import addMinutes from "date-fns/esm/addMinutes/index";
import { range } from "lodash";
import { defineComponent, PropType } from "vue";
import VueTypes from "vue-types";

export default defineComponent({
  props: {
    style: VueTypes.object.isRequired,
    officer: {
      type: Object as PropType<ChiefResponse["chiefList"][0]>,
    },
    turnTerm: VueTypes.integer.isRequired,
    maxTurn: VueTypes.integer.isRequired,
  },
  methods: {
    getNpcColor,
    mb_strwidth,
  },

  data() {
    const turnTimes: string[] = [];
    if (!this.officer || !this.officer.turnTime) {
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      for (const _ of range(this.maxTurn)) {
        turnTimes.push("\xa0");
      }
    } else {
      const baseTurnTime = parseTime(this.officer.turnTime);
      for (const idx of range(this.officer.turn.length)) {
        turnTimes.push(
          formatTime(
            addMinutes(baseTurnTime, idx * this.turnTerm),
            this.turnTerm >= 5 ? "HH:mm" : "mm:ss"
          )
        );
      }
    }

    return {
      turnTimes,
    };
  },
});
</script>
