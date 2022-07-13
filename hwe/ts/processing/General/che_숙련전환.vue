<template>
  <TopBackBar :title="commandName" />
  <div class="bg0">
    <div>본인의 특정 병종 숙련을 40% 줄이고, 줄어든 숙련 중 9/10(90%p)를 다른 병종 숙련으로 전환합니다.</div>
    <div class="row">
      <div class="col-4 col-md-2">
        감소 대상 숙련 :
        <b-form-select v-model="srcArmTypeID">
          <b-form-select-option v-for="[armType, dexInfo] in dexFullInfo" :key="armType" :value="armType">
            {{ dexInfo.name }} (<span :style="{ color: dexInfo.currentInfo.color }">{{ dexInfo.currentInfo.name }}</span
            >)
          </b-form-select-option>
        </b-form-select>
      </div>
      <div class="col-4 col-md-2">
        전환 대상 숙련 :
        <b-form-select v-model="destArmTypeID">
          <b-form-select-option v-for="[armType, dexInfo] in dexFullInfo" :key="armType" :value="armType">
            {{ dexInfo.name }} (<span :style="{ color: dexInfo.currentInfo.color }">{{ dexInfo.currentInfo.name }}</span
            >)
          </b-form-select-option>
        </b-form-select>
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button @click="submit">
          {{ commandName }}
        </b-button>
      </div>
      <div
        :style="{
          display: 'grid',
          gridTemplateColumns: '3ch 1ch 2ch 10ch 1ch 3ch 1ch 2ch 10ch 1ch',
        }"
      >
        <div>{{ unwrap(dexFullInfo.get(srcArmTypeID)).name }}</div>
        <div class="text-end">[</div>
        <div :style="`color:${unwrap(dexFullInfo.get(srcArmTypeID)).currentInfo.color}`">
          {{ unwrap(dexFullInfo.get(srcArmTypeID)).currentInfo.name }}
        </div>
        <div class="f_tnum text-end">
          {{ convNumberFormat(unwrap(dexFullInfo.get(srcArmTypeID)).currentInfo.amount) }}
        </div>
        <div>]</div>
        <div class="text-center">→</div>
        <div class="text-end">[</div>
        <div :style="`color:${unwrap(dexFullInfo.get(srcArmTypeID)).decreasedInfo.color}`">
          {{ unwrap(dexFullInfo.get(srcArmTypeID)).decreasedInfo.name }}
        </div>
        <div class="f_tnum text-end">
          {{ convNumberFormat(unwrap(dexFullInfo.get(srcArmTypeID)).decreasedInfo.amount) }}
        </div>
        <div>]</div>
      </div>
      <div
        :style="{
          display: 'grid',
          gridTemplateColumns: '3ch 1ch 2ch 10ch 1ch 3ch 1ch 2ch 10ch 1ch',
        }"
      >
        <div>{{ unwrap(dexFullInfo.get(destArmTypeID)).name }}</div>
        <div class="text-end">[</div>
        <template v-if="srcArmTypeID == destArmTypeID">
          <div :style="`color:${unwrap(dexFullInfo.get(destArmTypeID)).decreasedInfo.color}`">
            {{ unwrap(dexFullInfo.get(destArmTypeID)).decreasedInfo.name }}
          </div>
          <div class="f_tnum text-end">
            {{ convNumberFormat(unwrap(dexFullInfo.get(destArmTypeID)).decreasedInfo.amount) }}
          </div>
        </template>
        <template v-else>
          <div :style="`color:${unwrap(dexFullInfo.get(destArmTypeID)).currentInfo.color}`">
            {{ unwrap(dexFullInfo.get(destArmTypeID)).currentInfo.name }}
          </div>
          <div class="f_tnum text-end">
            {{ convNumberFormat(unwrap(dexFullInfo.get(destArmTypeID)).currentInfo.amount) }}
          </div>
        </template>
        <div>]</div>
        <div class="text-center">→</div>
        <div class="text-end">[</div>
        <div :style="`color:${unwrap(unwrap(dexFullInfo.get(destArmTypeID)).afterInfo.get(srcArmTypeID)).color}`">
          {{ unwrap(unwrap(dexFullInfo.get(destArmTypeID)).afterInfo.get(srcArmTypeID)).name }}
        </div>
        <div class="f_tnum text-end">
          {{ convNumberFormat(unwrap(unwrap(dexFullInfo.get(destArmTypeID)).afterInfo.get(srcArmTypeID)).amount) }}
        </div>
        <div>]</div>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
type DexInfo = {
  amount: number;
  color: string;
  name: string;
};
declare const staticValues: {
  commandName: string;
};
declare const procRes: {
  ownDexList: {
    armType: number;
    name: string;
    amount: number;
  }[];
  dexLevelList: DexInfo[];
  decreaseCoeff: number;
  convertCoeff: number;
};
</script>

<script lang="ts" setup>
import { ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";

const commandName = staticValues.commandName;

const srcArmTypeID = ref(procRes.ownDexList[0].armType);
const destArmTypeID = ref(procRes.ownDexList[0].armType);

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      srcArmType: srcArmTypeID.value,
      destArmType: destArmTypeID.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}

function getDexCall(dex: number): { color: string; name: string } {
  if (dex < 0) {
    throw `올바르지 않은 수치: ${dex}`;
  }

  let color = "";
  let name = "";
  for (const nextDexLevel of procRes.dexLevelList) {
    if (dex < nextDexLevel.amount) {
      break;
    }
    color = nextDexLevel.color;
    name = nextDexLevel.name;
  }

  return {
    color,
    name,
  };
}

const dexFullInfo = new Map<
  number,
  {
    armType: number;
    name: string;
    amount: number;
    decresedAmount: number;
    currentInfo: DexInfo;
    decreasedInfo: DexInfo;
    afterInfo: Map<number, DexInfo>;
  }
>();

for (const dexItem of procRes.ownDexList) {
  const amount = dexItem.amount;
  const currentInfo = { ...getDexCall(amount), amount };

  const decresedAmount = amount * procRes.decreaseCoeff;
  const decresedAfterAmount = amount - decresedAmount;
  const decreasedInfo = {
    ...getDexCall(decresedAfterAmount),
    amount: decresedAfterAmount,
  };

  dexFullInfo.set(dexItem.armType, {
    ...dexItem,
    decresedAmount,
    currentInfo,
    decreasedInfo,
    afterInfo: new Map(),
  });
}

for (const [armType, dexItem] of dexFullInfo.entries()) {
  for (const [fromArmType, fromDexItem] of dexFullInfo.entries()) {
    let afterAmount = fromDexItem.decresedAmount * procRes.convertCoeff;
    if (armType != fromArmType) {
      afterAmount += dexItem.amount;
    } else {
      afterAmount += dexItem.decresedAmount;
    }

    dexItem.afterInfo.set(fromArmType, {
      amount: afterAmount,
      ...getDexCall(afterAmount),
    });
  }
}

function convNumberFormat(value: number): string {
  return Math.floor(value).toLocaleString();
}
</script>
