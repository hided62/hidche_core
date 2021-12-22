<template>
  <TopBackBar :title="commandName" type="chief" />
  <div class="bg0">
    <div>
      나라의 이름을 바꿉니다. 황제가 된 후 1회 가능합니다.<br />
    </div>
    <div class="row">
      <div class="col-6 col-md-3">
        국명 :
        <b-form-input maxlength="18" v-model="destNationName"/>
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";

declare const commandName: string;

export default defineComponent({
  components: {
    TopBackBar,
    BottomBar,
  },
  setup() {
    const destNationName = ref("");

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          nationName: destNationName.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      destNationName,
      commandName,
      submit,
    };
  },
});
</script>
