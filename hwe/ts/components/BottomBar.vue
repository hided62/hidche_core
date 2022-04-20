<template>
  <div class="bg0" style="padding-top: 20px">
    <button type="button" class="btn btn-sammo-base2 back_btn" @click="back">
      {{ props.type == "close" ? "창 닫기" : "돌아가기" }}
    </button>
    <div />
  </div>
</template>

<script lang="ts" setup>
import type { PropType } from "vue";
import "@scss/game_bg.scss";

const props = defineProps({
  type: {
    type: String as PropType<"normal" | "chief" | "close">,
    default: "normal",
    required: false,
  },
});

function back() {
  if (props.type === "normal") {
    location.href = "./";
  } else if (props.type == "chief") {
    location.href = "v_chiefCenter.php";
  } else {
    //TODO: window.close하려면 부모창이 있어야함!
    window.close();
  }
}
</script>
