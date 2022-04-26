<template>
  <div class="general-card-basic">
    <div
      class="general-icon"
      :style="{
        backgroundImage: `url(${iconPath})`,
      }"
    ></div>

    <div
      class="general-name"
      :style="{
        color: isBrightColor(nation.color) ? '#000' : '#fff',
        backgroundColor: nation.color,
      }"
    >
      {{ general.name }} 【{{ general.officerLevelText }} | {{ generalTypeCall }} |
      <span :style="{ color: injuryInfo.color }">{{ injuryInfo.text }}</span>
      】 {{ general.turntime.substring(11, 19) }}
    </div>

    <div>통솔</div>
    <div>
      <div class="row gx-0">
        <div class="col">
          <span :style="{ color: injuryInfo.color }">{{ general.leadership }}</span>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-if="general.lbonus > 0" style="color: cyan">+{{ general.lbonus }}</span>
        </div>
        <div class="col">
          <SammoBar :height="10" :percent="general.leadership_exp / 20" />
        </div>
      </div>
    </div>
    <div>무력</div>
    <div>
      <div class="row gx-0">
        <div class="col" :style="{ color: injuryInfo.color }">
          {{ general.strength }}
        </div>
        <div class="col">
          <SammoBar :height="10" :percent="general.strength_exp / 20" />
        </div>
      </div>
    </div>
    <div>지력</div>
    <div>
      <div class="row gx-0">
        <div class="col" :style="{ color: injuryInfo.color }">
          {{ general.intel }}
        </div>
        <div class="col">
          <SammoBar :height="10" :percent="general.intel_exp / 20" />
        </div>
      </div>
    </div>

    <div>명마</div>
    <div v-b-tooltip.hover :title="horse.info ?? undefined">{{ horse.name }}</div>
    <div>무기</div>
    <div v-b-tooltip.hover :title="weapon.info ?? undefined">{{ weapon.name }}</div>
    <div>서적</div>
    <div v-b-tooltip.hover :title="book.info ?? undefined">{{ book.name }}</div>

    <div>자금</div>
    <div>{{ general.gold.toLocaleString() }}</div>
    <div>군량</div>
    <div>{{ general.rice.toLocaleString() }}</div>
    <div>도구</div>
    <div v-b-tooltip.hover :title="item.info ?? undefined">{{ item.name }}</div>

    <!-- TODO: show_img_level을 고려 -->
    <div
      class="general-crew-type-icon"
      :style="{
        backgroundImage: `url(${imagePath}/crewtype${general.crewtype}.png)`,
      }"
    ></div>

    <div>병종</div>
    <div v-b-tooltip.hover :title="crewtype.info ?? undefined">{{ crewtype.name }}</div>
    <div>병사</div>
    <div>{{ general.crew.toLocaleString() }}</div>
    <div>성격</div>
    <div v-b-tooltip.hover :title="personal.info ?? undefined">{{ personal.name }}</div>

    <!-- TODO: bonusTrain 같은 개념이 필요 -->
    <div>훈련</div>
    <div>{{ general.train }}</div>
    <div>사기</div>
    <div>{{ general.atmos }}</div>
    <div>특기</div>
    <div>
      <span v-b-tooltip.hover :title="specialDomestic.info ?? undefined"> {{ specialDomestic.name }}</span> /
      <span v-b-tooltip.hover :title="specialWar.info ?? undefined"> {{ specialWar.name }}</span>
    </div>

    <div>Lv</div>
    <!-- TODO: 경험치 막대가 필요 -->
    <div class="general-exp-level">
      {{ general.explevel }}
    </div>
    <div class="general-exp-level-bar">{{ nextExpLevelRemain(general.experience, general.explevel) }}</div>
    <div>연령</div>
    <div :style="{ color: ageColor }">{{ general.age }}세</div>

    <div>수비</div>
    <div class="general-defence-train">
      <span v-if="general.defence_train === 999" style="color: red">수비 안함</span>
      <span v-else style="color: limegreen">수비 함(훈사{{ general.defence_train }})</span>
    </div>
    <div>삭턴</div>
    <div>{{ general.killturn }} 턴</div>
    <div>실행</div>
    <div>{{ nextExecuteMinute }}분 남음</div>

    <div>부대</div>
    <div v-if="!troopInfo" class="general-troop">-</div>
    <div v-else class="general-troop">
      <s v-if="troopInfo.leader.reservedCommand[0]?.action != 'che_집합'" style="color: gray">
        {{ troopInfo.name }}
      </s>
      <span v-else style="color: orange">
        {{ troopInfo.name }}({{ gameConstStore.cityConst[troopInfo.leader.city].name }})
      </span>
    </div>
    <div>벌점</div>
    <div class="general-v">
      {{ formatConnectScore(general.connect) }} {{ general.connect.toLocaleString() }}점({{ general.con }})
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { GeneralListItemP1 } from "@/defs/API/Nation";
import { computed, inject, onMounted, ref, toRefs, type Ref } from "vue";
import { getIconPath } from "@/util/getIconPath";
import { isBrightColor } from "@/util/isBrightColor";
import { formatInjury } from "@/utilGame/formatInjury";
import type { NationStaticItem } from "@/defs";
import { unwrap } from "@/util/unwrap";
import type { GameConstStore } from "@/GameConstStore";
import { formatGeneralTypeCall } from "@/utilGame/formatGeneralTypeCall";
import { nextExpLevelRemain } from "@/utilGame/nextExpLevelRemain";
import { formatConnectScore } from "@/utilGame/formatConnectScore";
import SammoBar from "@/components/SammoBar.vue";
import { parseTime } from "@/util/parseTime";
import { clamp } from "lodash";
const imagePath = window.pathConfig.gameImage;
const gameConstStore = unwrap(inject<Ref<GameConstStore>>("gameConstStore"));
const props = defineProps<{
  general: GeneralListItemP1;
  troopInfo?: {
    leader: GeneralListItemP1;
    name: string;
  };
  nation: NationStaticItem;
}>();

const { general, troopInfo, nation } = toRefs(props);
const iconPath = computed(() => getIconPath(general.value.imgsvr, general.value.picture));
const injuryInfo = computed(() => {
  const [text, color] = formatInjury(general.value.injury);
  return {
    text,
    color,
  };
});
const generalTypeCall = computed(() =>
  formatGeneralTypeCall(
    general.value.leadership,
    general.value.strength,
    general.value.intel,
    gameConstStore.value.gameConst
  )
);

const horse = computed(
  () => gameConstStore.value.iActionInfo.item[general.value.horse] ?? { value: "None", name: "-" }
);
const weapon = computed(
  () => gameConstStore.value.iActionInfo.item[general.value.weapon] ?? { value: "None", name: "-" }
);
const book = computed(() => gameConstStore.value.iActionInfo.item[general.value.book] ?? { value: "None", name: "-" });
const item = computed(() => gameConstStore.value.iActionInfo.item[general.value.item] ?? { value: "None", name: "-" });

const crewtype = computed(
  () => gameConstStore.value.iActionInfo.crewtype[general.value.crewtype] ?? { value: "None", name: "-" }
);

const personal = computed(
  () => gameConstStore.value.iActionInfo.personality[general.value.personal] ?? { value: "None", name: "-" }
);
const specialDomestic = computed(
  () =>
    gameConstStore.value.iActionInfo.specialDomestic[general.value.specialDomestic] ?? {
      value: "None",
      name: `${general.value.specage}세`,
    }
);
const specialWar = computed(
  () =>
    gameConstStore.value.iActionInfo.specialWar[general.value.specialWar] ?? {
      value: "None",
      name: `${general.value.specage2}세`,
    }
);

const ageColor = computed(() => {
  const age = general.value.age;
  const retirementYear = gameConstStore.value.gameConst.retirementYear;
  if (age < retirementYear * 0.75) {
    return "limegreen";
  }
  if (age < retirementYear) {
    return "yellow";
  }
  return "red";
});

const nextExecuteMinute = ref(999);
onMounted(() => {
  const now = new Date();
  const turnTime = parseTime(general.value.turntime);
  nextExecuteMinute.value = Math.floor(clamp(turnTime.getSeconds() - now.getSeconds() / 60, 0));
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
</style>
