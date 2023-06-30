<template>
  <TopBackBar :title="commandName" />
  <div v-if="!available건국" class="bg0">더 이상 건국은 불가능합니다.</div>
  <div v-else class="bg0">
    <div>현재 도시에서 나라를 세웁니다. 도시 규모의 제한이 없습니다.</div>
    <ul>
      <li v-for="nationType in nationTypes" :key="nationType.type" class="row">
        <div class="col-2 col-lg-1">- {{ nationType.name }}</div>
        <div class="col-4 col-lg-2">
          : <span style="color: cyan">{{ nationType.pros }}</span
          >,
        </div>
        <div class="col-4 col-lg-2">
          <span style="color: magenta">{{ nationType.cons }}</span>
        </div>
      </li>
    </ul>
    <div class="row">
      <div class="col-4 col-lg-2">국명 : <b-form-input v-model="destNationName" maxlength="18" /></div>
      <div class="col-3 col-lg-2">색상 : <ColorSelect v-model="selectedColorID" :colors="colors" /></div>
      <div class="col-3 col-lg-2">
        <label>성향 :</label>
        <b-form-select v-model="selectedNationType" :options="nationTypesOption" />
      </div>

      <div class="col-2 col-lg-2 d-grid">
        <b-button @click="submit">
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
  available건국: boolean;
  colors: string[];
  nationTypes: procNationTypeList;
};
</script>
<script setup lang="ts">
import ColorSelect from "@/processing/SelectColor.vue";
import { ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import type { procNationTypeList } from "../processingRes";

const commandName = staticValues.commandName;
const { nationTypes, available건국, colors } = procRes;

const destNationName = ref("");
const selectedColorID = ref(0);

const selectedNationType = ref(Object.values(procRes.nationTypes)[0].type);

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      colorType: selectedColorID.value,
      nationName: destNationName.value,
      nationType: selectedNationType.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}

const nationTypesOption: { html: string; value: string }[] = [];
for (const nationType of Object.values(procRes.nationTypes)) {
  nationTypesOption.push({
    html: nationType.name,
    value: nationType.type,
  });
}
</script>
