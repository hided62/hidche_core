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
      {{ general.name }} 【
      <template v-if="2 <= general.officerLevel && general.officerLevel <= 4 && general.officer_city">
        {{ formatCityName(general.officer_city, gameConstStore) }}
      </template>
      {{ general.officerLevelText }} | {{ generalTypeCall }} |
      <span :style="{ color: injuryInfo.color }">{{ injuryInfo.text }}</span>
      】 {{ general.turntime.substring(11, 19) }}
    </div>

    <div class="bg1">통솔</div>
    <div>
      <div class="row gx-0">
        <div class="col">
          <span :style="{ color: injuryInfo.color }">{{ calcInjury("leadership", general) }}</span>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-if="general.lbonus > 0" style="color: cyan">+{{ general.lbonus }}</span>
        </div>
        <div class="col align-self-center">
          <SammoBar :height="10" :percent="(general.leadership_exp / statUpThreshold) * 100" />
        </div>
      </div>
    </div>
    <div class="bg1">무력</div>
    <div>
      <div class="row gx-0">
        <div class="col" :style="{ color: injuryInfo.color }">
          {{ calcInjury("strength", general) }}
        </div>
        <div class="col align-self-center">
          <SammoBar :height="10" :percent="(general.strength_exp / statUpThreshold) * 100" />
        </div>
      </div>
    </div>
    <div class="bg1">지력</div>
    <div>
      <div class="row gx-0">
        <div class="col" :style="{ color: injuryInfo.color }">
          {{ calcInjury("intel", general) }}
        </div>
        <div class="col align-self-center">
          <SammoBar :height="10" :percent="(general.intel_exp / statUpThreshold) * 100" />
        </div>
      </div>
    </div>

    <div class="bg1">명마</div>
    <div v-if="!horse.info">{{ horse.name }}</div>
    <div v-else v-b-tooltip.hover="horse.info">{{ horse.name }}</div>

    <div class="bg1">무기</div>
    <div v-if="!weapon.info">{{ weapon.name }}</div>
    <div v-else v-b-tooltip.hover="weapon.info">{{ weapon.name }}</div>

    <div class="bg1">서적</div>
    <div v-if="!book.info">{{ book.name }}</div>
    <div v-else v-b-tooltip.hover="book.info">{{ book.name }}</div>

    <div class="bg1">자금</div>
    <div>{{ general.gold.toLocaleString() }}</div>
    <div class="bg1">군량</div>
    <div>{{ general.rice.toLocaleString() }}</div>
    <div class="bg1">도구</div>
    <div v-if="!item.info">{{ item.name }}</div>
    <div v-else v-b-tooltip.hover="item.info">{{ item.name }}</div>

    <!-- TODO: show_img_level을 고려 -->
    <div
      class="general-crew-type-icon"
      :style="{
        backgroundImage: `url(${imagePath}/crewtype${general.crewtype}.png)`,
      }"
    ></div>

    <div class="bg1">병종</div>
    <div v-if="!crewtype.info">{{ crewtype.name }}</div>
    <div v-else v-b-tooltip.hover="crewtype.info">{{ crewtype.name }}</div>
    <div class="bg1">병사</div>
    <div>{{ general.crew.toLocaleString() }}</div>
    <div class="bg1">성격</div>
    <div v-if="!personal.info">{{ personal.name }}</div>
    <div v-else v-b-tooltip.hover="personal.info">{{ personal.name }}</div>

    <!-- TODO: bonusTrain 같은 개념이 필요 -->
    <div class="bg1">훈련</div>
    <div>{{ general.train }}</div>
    <div class="bg1">사기</div>
    <div>{{ general.atmos }}</div>
    <div class="bg1">특기</div>
    <div>
      <span v-if="!specialDomestic.info"> {{ specialDomestic.name }}</span
      ><span v-else v-b-tooltip.hover="specialDomestic.info"> {{ specialDomestic.name }}</span>
      /
      <span v-if="!specialWar.info"> {{ specialWar.name }}</span
      ><span v-else v-b-tooltip.hover="specialWar.info"> {{ specialWar.name }}</span>
    </div>

    <div class="bg1">Lv</div>
    <!-- TODO: 경험치 막대가 필요 -->
    <div class="general-exp-level">
      {{ general.explevel }}
    </div>
    <div class="general-exp-level-bar d-grid">
      <div class="align-self-center">
        <SammoBar
          :height="10"
          :percent="(([a, b]) => (a / b) * 100)(nextExpLevelRemain(general.experience, general.explevel))"
        />
      </div>
    </div>
    <div class="bg1">연령</div>
    <div :style="{ color: ageColor }">{{ general.age }}세</div>

    <div class="bg1">수비</div>
    <div class="general-defence-train">
      <span v-if="general.defence_train === 999" style="color: red">수비 안함</span>
      <span v-else style="color: limegreen">수비 함(훈사{{ general.defence_train }})</span>
    </div>
    <div class="bg1">삭턴</div>
    <div>{{ general.killturn }} 턴</div>
    <div class="bg1">실행</div>
    <div>{{ nextExecuteMinute }}분 남음</div>

    <div class="bg1">부대</div>
    <div v-if="!troopInfo" class="general-troop">-</div>
    <div v-else class="general-troop">
      <s
        v-if="troopInfo.leader.reservedCommand && troopInfo.leader.reservedCommand[0].action != 'che_집합'"
        style="color: gray"
      >
        {{ troopInfo.name }}
      </s>
      <span v-else-if="troopInfo.leader.city == general.city">{{ troopInfo.name }}</span>
      <span v-else style="color: orange">
        {{ troopInfo.name }}({{ formatCityName(troopInfo.leader, gameConstStore) }})
      </span>
    </div>
    <div class="bg1">벌점</div>
    <div class="general-connect-score">
      {{ formatConnectScore(general.connect) }} {{ general.connect.toLocaleString() }}점({{ general.con }})
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { GeneralListItemP1 } from "@/defs/API/Nation";
import { inject, ref, toRefs, watch, type Ref } from "vue";
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
import { clamp } from "lodash-es";
import { formatCityName } from "@/utilGame/formatCityName";
import { isValidObjKey } from "@/utilGame/isValidObjKey";
import { calcInjury } from "@/utilGame/calcInjury";
import { addMinutes } from "date-fns/esm";
import type { GameIActionInfo } from "@/defs/GameObj";
const imagePath = window.pathConfig.gameImage;
const gameConstStore = unwrap(inject<Ref<GameConstStore>>("gameConstStore"));
const props = defineProps<{
  general: GeneralListItemP1;
  troopInfo?: {
    leader: {
      city: number;
      reservedCommand?: GeneralListItemP1["reservedCommand"];
    };
    name: string;
  };
  nation: NationStaticItem;
  turnTerm: number;
  lastExecuted: Date;
}>();

const { general, troopInfo, nation } = toRefs(props);
const iconPath = ref("");

const injuryInfo = ref<{ text: string; color: string }>({ text: "-", color: "white" });
const generalTypeCall = ref<string>("-");

const ageColor = ref<string>("limegreen");

const statUpThreshold = gameConstStore.value.gameConst.upgradeLimit;

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

const horse = ref<GameIActionInfo>(dummyInfo);
const weapon = ref<GameIActionInfo>(dummyInfo);
const book = ref<GameIActionInfo>(dummyInfo);
const item = ref<GameIActionInfo>(dummyInfo);

const crewtype = ref<GameIActionInfo>(dummyInfo);

const personal = ref<GameIActionInfo>(dummyInfo);
const specialDomestic = ref<GameIActionInfo>(dummyInfo);
const specialWar = ref<GameIActionInfo>(dummyInfo);

watch(
  general,
  (general) => {
    horse.value = !isValidObjKey(general.horse) ? dummyInfo : gameConstStore.value.iActionInfo.item[general.horse];
    weapon.value = !isValidObjKey(general.weapon) ? dummyInfo : gameConstStore.value.iActionInfo.item[general.weapon];
    book.value = !isValidObjKey(general.book) ? dummyInfo : gameConstStore.value.iActionInfo.item[general.book];
    item.value = !isValidObjKey(general.item) ? dummyInfo : gameConstStore.value.iActionInfo.item[general.item];

    crewtype.value = !isValidObjKey(general.crewtype)
      ? dummyInfo
      : gameConstStore.value.iActionInfo.crewtype[general.crewtype];

    personal.value = !isValidObjKey(general.personal)
      ? dummyInfo
      : gameConstStore.value.iActionInfo.personality[general.personal];

    specialDomestic.value = !isValidObjKey(general.specialDomestic)
      ? { value: "None", name: `${Math.max(general.age + 1, general.specage)}세`, info: "-" }
      : gameConstStore.value.iActionInfo.specialDomestic[general.specialDomestic];
    specialWar.value = !isValidObjKey(general.specialWar)
      ? { value: "None", name: `${Math.max(general.age + 1, general.specage2)}세`, info: "-" }
      : gameConstStore.value.iActionInfo.specialWar[general.specialWar];
  },
  { immediate: true }
);

const nextExecuteMinute = ref(999);
watch(
  general,
  () => {
    let turnTime = parseTime(general.value.turntime);
    if (turnTime.getTime() < props.lastExecuted.getTime()) {
      turnTime = addMinutes(turnTime, props.turnTerm);
    }
    nextExecuteMinute.value = Math.floor(clamp((turnTime.getTime() - props.lastExecuted.getTime()) / 60000, 0, 999));
  },
  { immediate: true }
);
</script>

<style lang="scss" scoped>
.general-card-basic {
  display: grid;
  grid-template-columns: 64px repeat(3, 2fr 5fr);
  grid-template-rows: repeat(9, calc(64px / 3));
  text-align: center;
  font-size: 14px;

  border-bottom: 1px solid gray;
  border-right: 1px solid gray;

  > div.bg1,
  > .general-crew-type-icon,
  > .general-icon {
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

.general-troop {
  grid-column: 2 / 4;
}

.general-connect-score {
  grid-column: 5 / 8;
}
</style>
