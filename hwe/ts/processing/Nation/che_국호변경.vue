<template>
  <TopBackBar :title="commandName" type="chief" />
  <div class="bg0">
    <div>나라의 이름을 바꿉니다. 황제가 된 후 1회 가능합니다.<br /></div>
    <div class="row">
      <div class="col-6 col-md-3">
        국명 :
        <b-form-input v-model="destNationName" maxlength="18" />
      </div>
      <div class="col-4 col-md-2 d-grid">
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
</script>

<script lang="ts" setup>
import { ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";

const commandName = staticValues.commandName;

const destNationName = ref("");

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      nationName: destNationName.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}
</script>
