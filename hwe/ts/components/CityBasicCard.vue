<template>
  <div class="city-card-basic bg2">
    <div
      class="cityNamePanel"
      :style="{
        color: isBrightColor(city.nationInfo.color) ? 'black' : 'white',
        backgroundColor: city.nationInfo.color,
      }"
    >
      <div>【{{ cityRegionText }} | {{ cityLevelText }}】 {{ city.name }}</div>
    </div>
    <div
      class="nationNamePanel"
      :style="{
        color: isBrightColor(city.nationInfo.color) ? 'black' : 'white',
        backgroundColor: city.nationInfo.color,
      }"
    >
      {{ city.nationInfo.id ? `지배 국가 【 ${city.nationInfo.name} 】` : "공 백 지" }}
    </div>
    <div class="gPanel popPanel">
      <div class="gHead bg1">주민</div>
      <div class="gBody">
        <SammoBar :height="7" :percent="(city.pop[0] / city.pop[1]) * 100" />
        <div class="cellText">{{ city.pop[0].toLocaleString() }} / {{ city.pop[1].toLocaleString() }}</div>
      </div>
    </div>
    <div class="gPanel trustPanel">
      <div class="gHead bg1">민심</div>
      <div class="gBody">
        <SammoBar :height="7" :percent="city.trust" />
        <div class="cellText">{{ city.trust.toLocaleString(undefined, {
          maximumFractionDigits: 1,
        }) }}</div>
      </div>
    </div>
    <div class="gPanel agriPanel">
      <div class="gHead bg1">농업</div>
      <div class="gBody">
        <SammoBar :height="7" :percent="(city.agri[0] / city.agri[1]) * 100" />
        <div class="cellText">
          {{ city.agri[0].toLocaleString() }}
          /
          {{ city.agri[1].toLocaleString() }}
        </div>
      </div>
    </div>
    <div class="gPanel commPanel">
      <div class="gHead bg1">상업</div>
      <div class="gBody">
        <SammoBar :height="7" :percent="(city.comm[0] / city.comm[1]) * 100" />
        <div class="cellText">
          {{ city.comm[0].toLocaleString() }}
          /
          {{ city.comm[1].toLocaleString() }}
        </div>
      </div>
    </div>
    <div class="gPanel secuPanel">
      <div class="gHead bg1">치안</div>
      <div class="gBody">
        <SammoBar :height="7" :percent="(city.secu[0] / city.secu[1]) * 100" />
        <div class="cellText">
          {{ city.secu[0].toLocaleString() }}
          /
          {{ city.secu[1].toLocaleString() }}
        </div>
      </div>
    </div>
    <div class="gPanel defPanel">
      <div class="gHead bg1">수비</div>
      <div class="gBody">
        <SammoBar :height="7" :percent="(city.def[0] / city.def[1]) * 100" />
        <div class="cellText">
          {{ city.def[0].toLocaleString() }}
          /
          {{ city.def[1].toLocaleString() }}
        </div>
      </div>
    </div>
    <div class="gPanel wallPanel">
      <div class="gHead bg1">성벽</div>
      <div class="gBody">
        <SammoBar :height="7" :percent="(city.wall[0] / city.wall[1]) * 100" />
        <div class="cellText">
          {{ city.wall[0].toLocaleString() }}
          /
          {{ city.wall[1].toLocaleString() }}
        </div>
      </div>
    </div>
    <div class="gPanel tradePanel">
      <div class="gHead bg1">시세</div>
      <div class="gBody">
        <SammoBar :height="7" :percent="city.trade ?? 100" />
        <div class="cellText">{{ city.trade ? `${city.trade}%` : "상인 없음" }}</div>
      </div>
    </div>
    <div class="gPanel officer4Panel">
      <div class="gHead bg1">태수</div>
      <div class="gBody cellTextOnly" :style="{ color: getNPCColor(city.officerList[4]?.npc ?? 0) }">
        {{ city.officerList[4]?.name ?? "-" }}
      </div>
    </div>
    <div class="gPanel officer3Panel">
      <div class="gHead bg1">군사</div>
      <div class="gBody cellTextOnly" :style="{ color: getNPCColor(city.officerList[3]?.npc ?? 0) }">
        {{ city.officerList[3]?.name ?? "-" }}
      </div>
    </div>
    <div class="gPanel officer2Panel">
      <div class="gHead bg1">종사</div>
      <div class="gBody cellTextOnly" :style="{ color: getNPCColor(city.officerList[2]?.npc ?? 0) }">
        {{ city.officerList[2]?.name ?? "-" }}
      </div>
    </div>
  </div>
</template>
<script lang="ts" setup>
import { unwrap } from "@/util/unwrap";
import { isBrightColor } from "@/util/isBrightColor";
import { getNPCColor } from "@/utilGame";
import type { GameConstStore } from "@/GameConstStore";
import { inject, ref, toRef, watch, type Ref } from "vue";
import type { GetFrontInfoResponse } from "@/defs/API/Global";
import SammoBar from "@/components/SammoBar.vue";

const props = defineProps<{
  city: GetFrontInfoResponse["city"];
}>();

const gameConstStore = unwrap(inject<Ref<GameConstStore>>("gameConstStore"));

const city = toRef(props, "city");

const cityRegionText = ref("");
const cityLevelText = ref("");
watch(
  city,
  (city) => {
    const cityInfo = gameConstStore.value.cityConst[city.id];
    cityRegionText.value = gameConstStore.value.cityConstMap.region[cityInfo.region] as string;
    cityLevelText.value = gameConstStore.value.cityConstMap.level[city.level] as string;
  },
  { immediate: true }
);
</script>
<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

.city-card-basic {
  display: grid;
  border-right: solid 1px gray;
  border-bottom: solid 1px gray;

  .cellText {
    text-align: center;
    line-height: 1.2em;
  }

  .cellTextOnly {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .gPanel {
    display: grid;
    grid-template-columns: 1fr 2fr;
    border-top: solid 1px gray;
    border-left: solid 1px gray;

    .gHead {
      display: flex;
      justify-content: center;
      align-items: center;
    }
  }

  .cityNamePanel,
  .nationNamePanel {
    font-weight: bold;
    text-align: center;
    border-top: solid 1px gray;
    border-left: solid 1px gray;
  }

  .popPanel {
    grid-column: 1 / 3;
    grid-template-columns: 1fr 5fr;
  }
}

@include media-1000px {
  .city-card-basic {
    grid-template-columns: 1fr 1fr 1fr 1fr;

    .cityNamePanel,
    .nationNamePanel {
      grid-column: 1 / 5;
    }

    .officer4Panel {
      grid-column: 4 / 5;
      grid-row: 3 / 4;
    }

    .officer3Panel {
      grid-column: 4 / 5;
      grid-row: 4 / 5;
    }

    .officer2Panel {
      grid-column: 4 / 5;
      grid-row: 5 / 6;
    }
  }
}

@include media-500px {
  .city-card-basic {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;

    .cityNamePanel,
    .nationNamePanel {
      grid-column: 1 / 4;
    }
  }
}
</style>
