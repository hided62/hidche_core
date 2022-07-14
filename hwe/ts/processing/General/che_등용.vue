<template>
  <TopBackBar v-model:searchable="searchable" :title="commandName" />
  <div class="bg0">
    <div>
      재야나 타국의 장수를 등용합니다.<br />
      서신은 개인 메세지로 전달됩니다.<br />
      등용할 장수를 목록에서 선택하세요.<br />
    </div>
    <div class="row">
      <div class="col-12 col-md-6">
        장수 :
        <SelectGeneral
          v-model="selectedGeneralID"
          :generals="generalList"
          :groupByNation="nationList"
          :textHelper="textHelpGeneral"
          :searchable="searchable"
        />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
declare const staticValues: {
  commandName: string;
};

declare const procRes: {
  generals: procGeneralRawItemList;
  generalsKey: procGeneralKey[];
  nationList: procNationList;
};
</script>
<script setup lang="ts">
import SelectGeneral from "@/processing/SelectGeneral.vue";
import { onMounted, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  convertGeneralList,
  getProcSearchable,
  type procGeneralItem,
  type procGeneralKey,
  type procGeneralList,
  type procGeneralRawItemList,
  type procNationItem,
  type procNationList,
} from "../processingRes";
import { getNpcColor } from "@/common_legacy";

const commandName = staticValues.commandName;
const searchable = getProcSearchable();
const generalList = ref<procGeneralList>([]);
const selectedGeneralID = ref(0);

function textHelpGeneral(gen: procGeneralItem): string {
  const nameColor = getNpcColor(gen.npc);
  const name = nameColor ? `<span style="color:${nameColor}">${gen.name}</span>` : gen.name;
  return name;
}

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      destGeneralID: selectedGeneralID.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}

const nationList = ref(new Map<number, procNationItem>());

onMounted(() => {
  generalList.value = convertGeneralList(procRes.generalsKey, procRes.generals);
  selectedGeneralID.value = generalList.value[0].no;
  nationList.value.clear();
  for (const nationItem of procRes.nationList) {
    nationList.value.set(nationItem.id, nationItem);
  }
});
</script>
