<template>
  <div class="nation-card-basic bg2">
    <div
      class="name tb-title"
      :style="{
        backgroundColor: nation.color,
        color: isBrightColor(nation.color) ? 'black' : 'white',
        fontWeight: 'bold',
      }"
    >
      {{ nation.name }}
    </div>
    <div class="type-head tb-head bg1">성향</div>
    <div class="type-body tb-body">
      {{ nation.type.name }} (<span style="color: cyan">{{ nation.type.pros }}</span>
      <span style="color: magenta">{{ nation.type.cons }}</span
      >)
    </div>
    <div class="c12-head tb-head bg1">{{ formatOfficerLevelText(12, nation.level) }}</div>
    <div class="c12-body tb-body" :style="{ color: getNPCColor(nation.topChiefs[12]?.npc ?? 1) }">
      {{ nation.topChiefs[12]?.name ?? "-" }}
    </div>
    <div class="c11-head tb-head bg1">{{ formatOfficerLevelText(11, nation.level) }}</div>
    <div class="c11-body tb-body" :style="{ color: getNPCColor(nation.topChiefs[11]?.npc ?? 1) }">
      {{ nation.topChiefs[11]?.name ?? "-" }}
    </div>
    <div class="pop-head tb-head bg1">총 주민</div>
    <div v-if="!nation.id" class="pop-body tb-body">해당 없음</div>
    <div v-else class="pop-body tb-body">
      {{ nation.population.now.toLocaleString() }} / {{ nation.population.max.toLocaleString() }}
    </div>
    <div class="crew-head tb-head bg1">총 병사</div>
    <div v-if="!nation.id" class="crew-body tb-body">해당 없음</div>
    <div v-else class="crew-body tb-body">
      {{ nation.crew.now.toLocaleString() }} / {{ nation.crew.max.toLocaleString() }}
    </div>
    <div class="gold-head tb-head bg1">국고</div>
    <div v-if="!nation.id" class="gold-body tb-body">해당 없음</div>
    <div v-else class="gold-body tb-body">{{ nation.gold.toLocaleString() }}</div>
    <div class="rice-head tb-head bg1">병량</div>
    <div v-if="!nation.id" class="rice-body tb-body">해당 없음</div>
    <div v-else class="rice-body tb-body">{{ nation.rice.toLocaleString() }}</div>
    <div class="bill-head tb-head bg1">지급률</div>
    <div v-if="!nation.id" class="bill-body tb-body">해당 없음</div>
    <div v-else class="bill-body tb-body">{{ nation.bill }}%</div>
    <div class="taxRate-head tb-head bg1">세율</div>
    <div v-if="!nation.id" class="taxRate-body tb-body">해당 없음</div>
    <div v-else class="taxRate-body tb-body">{{ nation.taxRate }}%</div>
    <div class="cityCnt-head tb-head bg1">속령</div>
    <div v-if="!nation.id" class="cityCnt-body tb-body">해당 없음</div>
    <div v-else class="cityCnt-body tb-body">{{ nation.population.cityCnt.toLocaleString() }}</div>
    <div class="genCnt-head tb-head bg1">장수</div>
    <div v-if="!nation.id" class="genCnt-body tb-body">해당 없음</div>
    <div v-else class="genCnt-body tb-body">{{ nation.crew.generalCnt.toLocaleString() }}</div>
    <div class="power-head tb-head bg1">국력</div>
    <div v-if="!nation.id" class="power-body tb-body">해당 없음</div>
    <div v-else class="power-body tb-body">{{ nation.power.toLocaleString() }}</div>
    <div class="tech-head tb-head bg1">기술력</div>
    <div v-if="!nation.id" class="tech-body tb-body">해당 없음</div>
    <div v-else class="tech-body tb-body">
      {{ currentTechLevel }}등급 /
      <span :style="{ color: onTechLimit ? 'magenta' : 'limegreen' }">{{
        Math.floor(nation.tech).toLocaleString()
      }}</span>
    </div>
    <div class="strategicCmd-head tb-head bg1">전략</div>
    <div v-if="!nation.id" class="strategicCmd-body tb-body">해당 없음</div>
    <div
      v-else-if="impossibleStrategicCommandText"
      v-b-tooltip.hover="impossibleStrategicCommandText"
      class="strategicCmd-body tb-body"
      style="text-decoration: underline dashed red"
    >
      <span v-if="nation.strategicCmdLimit" style="color: red">{{ nation.strategicCmdLimit.toLocaleString() }}턴</span>
      <span v-else style="color: yellow">가능</span>
    </div>
    <div v-else class="strategicCmd-body tb-body">
      <span v-if="nation.strategicCmdLimit" style="color: red">{{ nation.strategicCmdLimit.toLocaleString() }}턴</span>
      <span v-else style="color: limegreen">가능</span>
    </div>
    <div class="diplomaticCmd-head tb-head bg1">외교</div>
    <div v-if="!nation.id" class="diplomaticCmd-body tb-body">해당 없음</div>
    <div v-else class="diplomaticCmd-body tb-body">
      <span v-if="nation.diplomaticLimit" style="color: red">{{ nation.diplomaticLimit.toLocaleString() }}턴</span>
      <span v-else style="color: limegreen">가능</span>
    </div>
    <div class="prohibitScout-head tb-head bg1">임관</div>
    <div v-if="!nation.id" class="prohibitScout-body tb-body">해당 없음</div>
    <div v-else class="prohibitScout-body tb-body">
      <span v-if="nation.prohibitScout" style="color: red">금지</span>
      <span v-else style="color: limegreen">허가</span>
    </div>
    <div class="prohibitWar-head tb-head bg1">전쟁</div>
    <div v-if="!nation.id" class="prohibitWar-body tb-body">해당 없음</div>
    <div v-else class="prohibitWar-body tb-body">
      <span v-if="nation.prohibitWar" style="color: red">금지</span>
      <span v-else style="color: limegreen">허가</span>
    </div>
  </div>
</template>
<script lang="ts" setup>
import type { GetFrontInfoResponse } from "@/defs/API/Global";
import type { GameConstStore } from "@/GameConstStore";
import { joinYearMonth } from "@/util/joinYearMonth";
import { parseYearMonth } from "@/util/parseYearMonth";
import { unwrap } from "@/util/unwrap";
import { isBrightColor } from "@/util/isBrightColor";
import { formatOfficerLevelText, getNPCColor, isTechLimited, convTechLevel, getMaxRelativeTechLevel } from "@/utilGame";
import { inject, ref, toRef, watch, type Ref } from "vue";
const props = defineProps<{
  nation: GetFrontInfoResponse["nation"];
  global: GetFrontInfoResponse["global"];
}>();

const gameConstStore = unwrap(inject<Ref<GameConstStore>>("gameConstStore"));
const nation = toRef(props, "nation");
const global = toRef(props, "global");

const currentTechLevel = ref(0);
const maxTechLevel = ref(0);
const onTechLimit = ref(false);
watch(
  nation,
  (nation) => {
    const { startyear, year } = global.value;
    console.log(gameConstStore);
    maxTechLevel.value = getMaxRelativeTechLevel(startyear, year, gameConstStore.value.gameConst.maxTechLevel);
    currentTechLevel.value = convTechLevel(nation.tech, maxTechLevel.value);
    onTechLimit.value = isTechLimited(startyear, year, nation.tech, gameConstStore.value.gameConst.maxTechLevel);
  },
  { immediate: true }
);

const impossibleStrategicCommandText = ref<string>("");
watch(
  nation,
  (nation) => {
    if (nation.impossibleStrategicCommand.length == 0) {
      impossibleStrategicCommandText.value = "";
      return;
    }

    const yearMonth = joinYearMonth(global.value.year, global.value.month);
    const texts = [];
    for (const [cmdName, turnCnt] of nation.impossibleStrategicCommand) {
      const [year, month] = parseYearMonth(yearMonth + turnCnt);
      texts.push(`${cmdName}: ${turnCnt.toLocaleString()}턴 뒤(${year}년 ${month}월부터)`);
    }
    impossibleStrategicCommandText.value = texts.join("<br>\n");
  },
  { immediate: true }
);
</script>
<style lang="scss" scoped>
.nation-card-basic {
  width: 500px;
  height: 193px;
  display: grid;
  grid-template-columns: 7fr 18fr 7fr 18fr;
  grid-template-rows: repeat(10, calc(192px / 10));

  border-bottom: solid 1px gray;
  border-right: solid 1px gray;

  .name {
    grid-column: 1 / span 4;
  }

  .type-body {
    grid-column: 2 / span 3;
  }
}

.tb-title {
  text-align: center;
  padding: 0px;
  line-height: calc(193px / 10);

  border-left: solid 1px gray;
  border-top: solid 1px gray;
}
.tb-head {
  border-left: solid 1px gray;
  border-top: solid 1px gray;

  text-align: center;
  padding: 0px;
  line-height: calc(193px / 10);
}

.tb-body {
  border-top: solid 1px gray;
  padding: 0px;
  line-height: calc(193px / 10);
  text-align: center;
}
</style>
