<template>
  <TopBackBar :title="commandName" type="chief" />
  <div class="bg0">
    <div>
      국기를 변경합니다. 단 1회 가능합니다.<br>
    </div>
    <div class="row">
      <div class="col-6 col-md-3">
        색상 :
        <ColorSelect :colors="colors" v-model="selectedColorID" />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import ColorSelect from "@/processing/ColorSelect.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";

declare const commandName: string;

declare const procRes: {
  colors: string[],
};

export default defineComponent({
  components: {
    ColorSelect,
    TopBackBar,
    BottomBar,
  },
  setup() {

    const selectedColorID = ref(0);

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          colorType: selectedColorID.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      selectedColorID,
      colors: procRes.colors,
      commandName,
      submit,
    };
  },
});
</script>
