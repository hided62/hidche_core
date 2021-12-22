<template>
  <TopBackBar :title="commandName" />
  <div class="bg0" v-if="!available건국">더 이상 건국은 불가능합니다.</div>
  <div class="bg0" v-else>
    <div>현재 도시에서 나라를 세웁니다. 중, 소도시에서만 가능합니다.</div>
    <ul>
      <li v-for="nationType in nationTypes" :key="nationType.type" class="row">
        <div class="col-2 col-md-1">- {{ nationType.name }}</div>
        <div class="col-4 col-md-2">
          : <span style="color: cyan">{{ nationType.pros }}</span
          >,
        </div>
        <div class="col-4 col-md-2">
          <span style="color: magenta">{{ nationType.cons }}</span>
        </div>
      </li>
    </ul>
    <div class="row">
      <div class="col-4 col-md-2">
        국명 : <b-form-input maxlength="18" v-model="destNationName" />
      </div>
      <div class="col-3 col-md-2">
        색상 : <ColorSelect :colors="colors" v-model="selectedColorID" />
      </div>
      <div class="col-3 col-md-2">
        <label>성향 :</label>
        <b-form-select
          :options="nationTypesOption"
          v-model="selectedNationType"
        />
      </div>

      <div class="col-2 col-md-2 d-grid">
        <b-button @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import ColorSelect from "@/processing/SelectColor.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { procNationTypeList } from "../processingRes";

declare const commandName: string;

declare const procRes: {
  available건국: boolean;
  colors: string[];
  nationTypes: procNationTypeList;
};

export default defineComponent({
  components: {
    ColorSelect,
    TopBackBar,
    BottomBar,
  },
  setup() {
    const destNationName = ref("");
    const selectedColorID = ref(0);

    console.log(procRes.nationTypes);
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
    console.log(nationTypesOption);

    return {
      available건국: procRes.available건국,
      selectedColorID,
      selectedNationType,
      colors: procRes.colors,
      nationTypes: procRes.nationTypes,
      nationTypesOption,
      destNationName,
      commandName,
      submit,
    };
  },
});
</script>
