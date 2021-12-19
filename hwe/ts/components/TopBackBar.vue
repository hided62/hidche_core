<template>
  <div class="bg0 back_bar">
    <button type="button" class="btn btn-sammo-base2 back_btn" @click="back">
      돌아가기
    </button>
    <h2 class="title">{{ title }}</h2>
  </div>
</template>

<script lang="ts">
import "@scss/game_bg.scss";
import { defineComponent, PropType } from "vue";

export default defineComponent({
  name: "TopBackBar",
  methods: {
    back() {
      if (this.type === "normal") {
        location.href = "./";
      } else if (this.type == "chief") {
        location.href = "b_chiefcenter.php";
      } else {
        //TODO: window.close하려면 부모창이 있어야함!
        window.close();
      }
    },
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    type: {
      type: String as PropType<"normal" | "chief" | "close">,
      default: "normal",
      required: false,
    },
  },
});
</script>


<style scoped>
.back_bar {
  max-width: 1000px;
  width: 100%;
  margin: auto;
  position: relative;
  border-left: 1px solid #888;
  border-right: 1px solid #888;
  height: 24pt;
}

.back_btn {
  position: absolute;
  left: 0;
  height: 24pt;
}

.title {
  text-align: center;
  line-height: 24pt;
  font-size: 18pt;
  margin: 0;
}
</style>