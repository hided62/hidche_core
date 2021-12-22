<template>
  <TopBackBar :title="commandName" v-model:searchable="searchable" />
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
          :generals="generalList"
          :groupByNation="nationList"
          :textHelper="textHelpGeneral"
          :searchable="searchable"
          v-model="selectedGeneralID"
        />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import SelectGeneral from "@/processing/SelectGeneral.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  convertGeneralList,
  getProcSearchable,
  procGeneralItem,
  procGeneralKey,
  procGeneralRawItemList,
  procNationItem,
  procNationList,
} from "../processingRes";
import { getNpcColor } from "@/common_legacy";
declare const commandName: string;

declare const procRes: {
  generals: procGeneralRawItemList;
  generalsKey: procGeneralKey[];
  nationList: procNationList;
};

export default defineComponent({
  components: {
    SelectGeneral,
    TopBackBar,
    BottomBar,
  },
  setup() {
    const generalList = convertGeneralList(
      procRes.generalsKey,
      procRes.generals
    );

    const selectedGeneralID = ref(generalList[0].no);

    function textHelpGeneral(gen: procGeneralItem): string {
      const nameColor = getNpcColor(gen.npc);
      const name = nameColor
        ? `<span style="color:${nameColor}">${gen.name}</span>`
        : gen.name;
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

    const nationList = new Map<number, procNationItem>();
    for (const nationItem of procRes.nationList) {
      nationList.set(nationItem.id, nationItem);
    }

    return {
      searchable: getProcSearchable(),
      selectedGeneralID,
      generalList,
      nationList,
      commandName,
      textHelpGeneral,
      submit,
    };
  },
});
</script>
