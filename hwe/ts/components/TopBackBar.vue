<template>
  <div class="bg0 back_bar">
    <button type="button" class="btn btn-sammo-base2 back_btn" @click="back">
      돌아가기
    </button><b-button
      class="btn-toggle-zoom"
      :variant="toggleSearch ? 'info' : 'secondary'"
      :pressed="toggleSearch"
      v-if="toggleSearch !== undefined"
      @click="toggleSearch = !toggleSearch"
      >{{ toggleSearch ? "검색 켜짐" : "검색 꺼짐" }}</b-button
    >
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
  data() {
    return {
      toggleSearch: this.searchable,
    };
  },
  emits: ["update:searchable"],
  watch: {
    toggleSearch(val: boolean) {
      this.$emit("update:searchable", val);
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
    searchable: {
      type: Boolean,
      default: undefined,
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
  height: 24pt;
}

.back_btn {
  position: absolute;
  left: 0;
  height: 24pt;
}

.btn-toggle-zoom{
  position: absolute;
  height: 24pt;
  right: 0;
}

.title {
  text-align: center;
  line-height: 24pt;
  font-size: 18pt;
  margin: 0;
}
</style>