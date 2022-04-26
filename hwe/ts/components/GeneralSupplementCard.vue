<template>
  <div class="general-card-supplement row gx-0">
    <div class="col-12 general-card-info">
      <div class="part-title">추가 정보</div>
      <div>명성</div>
      <div>{{ formatHonor(general.experience) }} ({{ general.experience.toLocaleString() }})</div>
      <div>계급</div>
      <div>{{ general.dedLevelText }} ({{ general.dedication.toLocaleString() }}</div>
      <div>봉급</div>
      <div>{{ general.bill.toLocaleString() }}</div>

      <div>전투</div>
      <div>{{ general.warnum.toLocaleString() }}</div>
      <div>계략</div>
      <div>{{ general.firenum.toLocaleString() }}</div>
      <div>사관</div>
      <div>{{ general.belong }}년차</div>

      <div>승률</div>
      <div>{{ ((general.killnum / Math.max(general.warnum, 1)) * 100).toFixed(2) }} %</div>
      <div>승리</div>
      <div>{{ general.killnum.toLocaleString() }}</div>
      <div>패배</div>
      <div>{{ general.deathnum.toPrecision() }}</div>

      <div>살상률</div>
      <div>
        {{
          ((general.killcrew / Math.max(general.deathcrew, 1)) * 100).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          })
        }}
        %
      </div>
      <div>사살</div>
      <div>{{ general.killcrew.toLocaleString() }}</div>
      <div>피살</div>
      <div>{{ general.deathcrew.toLocaleString() }}</div>
    </div>
    <div class="col-7 general-card-dex">
      <div class="part-title">숙련도</div>
      <template v-for="[dexType, dex, dexInfo] of dexList" :key="dexType">
        <div>{{ dexType }}</div>
        <div :style="{ color: dexInfo.color }">{{ dexInfo.name }}</div>
        <div>{{ (dex / 1000).toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1 }) }}K</div>
        <div>
          <SammoBar :height="10" :percent="dex / 1_000_000" />
        </div>
      </template>
    </div>
    <div class="col-5 general-card-turn">
      <div class="part-title">예약턴</div>
      <template v-if="general.reservedCommand">
        <div v-for="(turn, idx) in general.reservedCommand.slice(0, 5)" :key="idx">
          {{ turn.brief }}
        </div>
      </template>
      <div v-else style="grid-row: 2 / 7">NPC</div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { GeneralListItemP1 } from "@/defs/API/Nation";
import { computed } from "vue";
import SammoBar from "@/components/SammoBar.vue";
import { formatDexLevel, type DexInfo } from "@/utilGame/formatDexLevel";
import { formatHonor } from "@/utilGame/formatHonor";
const props = defineProps<{
  general: GeneralListItemP1;
}>();

const dexList = computed((): [string, number, DexInfo][] => {
  return [
    ["보병", props.general.dex1, formatDexLevel(props.general.dex1)],
    ["궁병", props.general.dex2, formatDexLevel(props.general.dex2)],
    ["기병", props.general.dex3, formatDexLevel(props.general.dex3)],
    ["귀병", props.general.dex4, formatDexLevel(props.general.dex4)],
    ["차병", props.general.dex5, formatDexLevel(props.general.dex5)],
  ];
});
</script>

<style lang="scss" scoped>
.general-card-basic {
  display: grid;
  grid-template-columns: 64px repeat(3, 2fr 5fr);
  grid-template-rows: repeat(9, calc(64px / 3));
  text-align: center;
  font-size: 14px;
}
.general-icon {
  width: 64px;
  height: 64px;
  background-size: contain;
  background-repeat: no-repeat;
  grid-row: 1 / 4;
}

.general-name {
  grid-row: 1 / 2;
  grid-column: 2 / 8;
  font-weight: bold;
}

.general-crew-type-icon {
  width: 64px;
  height: 64px;
  background-size: contain;
  background-repeat: no-repeat;
  grid-row: 4 / 7;
}

.general-exp-level-bar {
  grid-column: 3 / 6;
}

.general-defence-train {
  grid-column: 2 / 4;
}

.general-card-info {
  display: grid;
  grid-template-columns: repeat(3, 64px 1fr);
  grid-template-rows: repeat(5, calc(64px / 3));

  .part-title {
    grid-column: 1 / 4;
  }
}

.general-card-dex {
  display: grid;
  grid-template-columns: 64px 40px 60px 1fr;
  grid-template-rows: repeat(6, calc(64px / 3));

  .part-title {
    grid-column: 1 / 5;
  }
}

.general-card-turn {
  display: grid;
  grid-template-columns: 1fr;
  grid-template-rows: repeat(6, calc(64px / 3));
}
</style>
