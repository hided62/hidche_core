<template>
  <TopBackBar :title="commandName" type="chief" />
  <div class="bg0">
    <div>국기를 변경합니다. 단 1회 가능합니다.<br /></div>
    <div class="row">
      <div class="col-6 col-lg-3">
        색상 :
        <ColorSelect v-model="selectedColorID" :colors="colors" />
      </div>
      <div class="col-4 col-lg-2 d-grid">
        <b-button @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" type="chief" />
</template>

<script lang="ts">
declare const staticValues: {
  commandName: string;
};

declare const procRes: {
  colors: string[];
};
</script>

<script lang="ts" setup>
import ColorSelect from "@/processing/SelectColor.vue";
import { ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";

const commandName = staticValues.commandName;
const { colors } = procRes;

const selectedColorID = ref(0);

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      colorType: selectedColorID.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}
</script>
