<template>
  <TopBackBar v-model:searchable="searchable" :title="commandName" />
  <div class="bg0">
    <div v-if="commandName == '등용'">
      재야나 타국의 장수를 등용합니다.<br />
      서신은 개인 메세지로 전달됩니다.<br />
      등용할 장수를 목록에서 선택하세요.<br />
    </div>
    <div v-if="commandName == '선양'">
      군주의 자리를 다른 장수에게 물려줍니다.<br />
      장수를 선택하세요.<br />
    </div>
    <div class="row">
      <div class="col-9 col-md-4">
        장수 :
        <SelectGeneral
          v-model="selectedGeneralID"
          :generals="generalList"
          :textHelper="textHelpGeneral"
          :searchable="searchable"
        />
      </div>
      <div class="col-3 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import SelectGeneral from "@/processing/SelectGeneral.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  convertGeneralList,
  getProcSearchable,
  type procGeneralItem,
  type procGeneralKey,
  type procGeneralRawItemList,
} from "../processingRes";
import { getNpcColor } from "@/common_legacy";
declare const commandName: string;

declare const procRes: {
  generals: procGeneralRawItemList;
  generalsKey: procGeneralKey[];
};

export default defineComponent({
  components: {
    SelectGeneral,
    TopBackBar,
    BottomBar,
  },
  setup() {
    const generalList = convertGeneralList(procRes.generalsKey, procRes.generals);

    const selectedGeneralID = ref(generalList[0].no);

    function textHelpGeneral(gen: procGeneralItem): string {
      const nameColor = getNpcColor(gen.npc);
      const name = nameColor ? `<span style="color:${nameColor}">${gen.name}</span>` : gen.name;
      return `${name} (${gen.leadership}/${gen.strength}/${gen.intel})`;
    }

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          destGeneralID: selectedGeneralID.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      searchable: getProcSearchable(),
      selectedGeneralID,
      generalList,
      commandName,
      textHelpGeneral,
      submit,
    };
  },
});
</script>
