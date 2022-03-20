<template>
  <div class="bg0 back_bar">
    <button type="button" class="btn btn-sammo-base2 back_btn" @click="back">
      돌아가기</button
    ><button
      type="button"
      v-if="reloadable"
      class="btn btn-sammo-base2 reload_btn"
      @click="reload"
    >
      갱신
    </button>
    <div v-else></div>
    <h2 class="title">{{ title }}</h2>
    <div>&nbsp;</div>
    <b-button
      class="btn-toggle-zoom"
      :variant="toggleSearch ? 'info' : 'secondary'"
      :pressed="toggleSearch"
      v-if="toggleSearch !== undefined"
      @click="toggleSearch = !toggleSearch"
      >{{ toggleSearch ? "검색 켜짐" : "검색 꺼짐" }}</b-button
    >
  </div>
</template>

<script lang="ts">
import "@scss/game_bg.scss";
import { defineComponent, type PropType } from "vue";

export default defineComponent({
  name: "TopBackBar",
  methods: {
    back() {
      if (this.type === "normal") {
        location.href = "./";
      } else if (this.type == "chief") {
        location.href = "v_chiefCenter.php";
      } else {
        //TODO: window.close하려면 부모창이 있어야함!
        window.close();
      }
    },
    reload() {
      this.$emit("reload");
    },
  },
  data() {
    return {
      toggleSearch: this.searchable,
    };
  },
  emits: ["update:searchable", "reload"],
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
    reloadable: {
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
  display: grid;
  grid-template-columns: 80px 80px 1fr 80px 80px;
  position: relative;
  height: 24pt;
}

.reload_btn {
  height: 24pt;
  margin-right: 2px;
}

.back_btn {
  height: 24pt;
  margin-right: 2px;
}

.btn-toggle-zoom {
  height: 24pt;
}

.title {
  text-align: center;
  line-height: 24pt;
  font-size: 18pt;
  margin: 0;
}
</style>