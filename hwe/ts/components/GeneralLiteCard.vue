<template>
  <div class="general-card-basic bg2">
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
      】
    </div>

    <div class="bg1">통솔</div>
    <div>
      <div class="row gx-0">
        <div class="col">
          <span :style="{ color: injuryInfo.color }">{{ calcInjury("leadership", general) }}</span>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-if="general.lbonus > 0" style="color: cyan">+{{ general.lbonus }}</span>
        </div>
      </div>
    </div>
    <div class="bg1">무력</div>
    <div>
      <div class="row gx-0">
        <div class="col" :style="{ color: injuryInfo.color }">
          {{ calcInjury("strength", general) }}
        </div>
      </div>
    </div>
    <div class="bg1">지력</div>
    <div>
      <div class="row gx-0">
        <div class="col" :style="{ color: injuryInfo.color }">
          {{ calcInjury("intel", general) }}
        </div>
      </div>
    </div>

    <div class="bg1">자금</div>
    <div>{{ general.gold.toLocaleString() }}</div>
    <div class="bg1">군량</div>
    <div>{{ general.rice.toLocaleString() }}</div>
    <div class="bg1">성격</div>
    <div v-if="!personal.info">{{ personal.name }}</div>
    <div v-else v-b-tooltip.hover :title="personal.info">{{ personal.name }}</div>

    <div class="filler"></div>
    <div class="bg1">Lv</div>
    <div class="general-exp-level">
      {{ general.explevel }}
    </div>

    <div class="bg1">연령</div>
    <div :style="{ color: ageColor }">{{ general.age }}세</div>

    <div class="bg1">특기</div>
    <div>
      <span v-if="!specialDomestic.info"> {{ specialDomestic.name }}</span
      ><span v-else v-b-tooltip.hover :title="specialDomestic.info"> {{ specialDomestic.name }}</span>
      /
      <span v-if="!specialWar.info"> {{ specialWar.name }}</span
      ><span v-else v-b-tooltip.hover :title="specialWar.info"> {{ specialWar.name }}</span>
    </div>

    <div class="filler"></div>

    <div class="bg1">삭턴</div>
    <div>{{ general.killturn }} 턴</div>

    <div class="bg1">벌점</div>
    <div class="general-connect-score">
      {{ formatConnectScore(general.connect) }} {{ general.connect.toLocaleString() }}점
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { GeneralListItemP0 } from "@/defs/API/Nation";
import { computed, inject, ref, toRefs, watch, type Ref } from "vue";
import { getIconPath } from "@/util/getIconPath";
import { isBrightColor } from "@/util/isBrightColor";
import { formatInjury } from "@/utilGame/formatInjury";
import type { NationStaticItem } from "@/defs";
import { unwrap } from "@/util/unwrap";
import type { GameConstStore } from "@/GameConstStore";
import { formatGeneralTypeCall } from "@/utilGame/formatGeneralTypeCall";
import { formatConnectScore } from "@/utilGame/formatConnectScore";
import { calcInjury } from "@/utilGame/calcInjury";
import type { GameIActionInfo } from "@/defs/GameObj";
import { isValidObjKey } from "@/utilGame/isValidObjKey";

const gameConstStore = unwrap(inject<Ref<GameConstStore>>("gameConstStore"));
const props = defineProps<{
  general: GeneralListItemP0;
  nation: NationStaticItem;
}>();

const { general, nation } = toRefs(props);
const iconPath = ref("");

const injuryInfo = ref<{ text: string; color: string }>({ text: "-", color: "white" });
const generalTypeCall = ref<string>("-");

const ageColor = ref<string>("limegreen");

watch(
  general,
  (general) => {
    iconPath.value = getIconPath(general.imgsvr, general.picture);

    const [text, color] = formatInjury(general.injury);
    injuryInfo.value = { text, color };

    generalTypeCall.value = formatGeneralTypeCall(
      general.leadership,
      general.strength,
      general.intel,
      gameConstStore.value.gameConst
    );

    ageColor.value = (() => {
      const age = general.age;
      const retirementYear = gameConstStore.value.gameConst.retirementYear;
      if (age < retirementYear * 0.75) {
        return "limegreen";
      }
      if (age < retirementYear) {
        return "yellow";
      }
      return "red";
    })();
  },
  { immediate: true }
);

const dummyInfo: GameIActionInfo = { value: "None", name: "-", info: "" };

const personal = ref<GameIActionInfo>(dummyInfo);
const specialDomestic = ref<GameIActionInfo>(dummyInfo);
const specialWar = ref<GameIActionInfo>(dummyInfo);

watch(
  general,
  (general) => {
    personal.value = !isValidObjKey(general.personal)
      ? dummyInfo
      : gameConstStore.value.iActionInfo.personality[general.personal];

    specialDomestic.value = !isValidObjKey(general.specialDomestic)
      ? dummyInfo
      : gameConstStore.value.iActionInfo.specialDomestic[general.specialDomestic];
    specialWar.value = !isValidObjKey(general.specialWar)
      ? dummyInfo
      : gameConstStore.value.iActionInfo.specialWar[general.specialWar];
  },
  { immediate: true }
);
</script>

<style lang="scss" scoped>
.general-card-basic {
  display: grid;
  grid-template-columns: 64px repeat(3, 2fr 5fr);
  grid-template-rows: repeat(5, calc(64px / 3));
  text-align: center;
  font-size: 14px;

  border-bottom: 1px solid gray;
  border-right: 1px solid gray;

  > div.bg1,
  > .general-crew-type-icon,
  > .general-icon,
  > .filler {
    border-left: 1px solid gray;
  }

  > div {
    border-top: 1px solid gray;
  }
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

.general-connect-score {
  grid-column: 5 / 8;
}
</style>
