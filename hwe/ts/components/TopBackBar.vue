<template>
  <div :class="['bg0', 'back_bar', teleportZone ? 'back_bar_teleport' : undefined]">
    <button type="button" class="btn btn-sammo-base2 back_btn" @click="back">
      {{ props.type == "close" ? "창 닫기" : "돌아가기" }}</button
    ><button v-if="reloadable" type="button" class="btn btn-sammo-base2 reload_btn" @click="reload">갱신</button>
    <div v-else />
    <h2 class="title">
      {{ title }}
    </h2>
    <div v-if="teleportZone" :id="teleportZone" class="teleport-zone"></div>
    <template v-else>
      <div>&nbsp;</div>
      <b-button
        v-if="toggleSearch !== undefined"
        class="btn-toggle-zoom"
        :variant="toggleSearch ? 'info' : 'secondary'"
        :pressed="toggleSearch"
        @click="toggleSearch = !toggleSearch"
      >
        {{ toggleSearch ? "검색 켜짐" : "검색 꺼짐" }}
      </b-button>
    </template>
  </div>
</template>

<script lang="ts" setup>
import "@scss/game_bg.scss";
import { type PropType, ref, watch } from "vue";
import VueTypes from "vue-types";

const props = defineProps({
  title: VueTypes.string.isRequired,
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
  teleportZone: {
    type: String,
    default: undefined,
    required: false,
  },
});

const emit = defineEmits(["update:searchable", "reload"]);

const toggleSearch = ref(props.searchable);

watch(toggleSearch, (val) => {
  emit("update:searchable", val);
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
function reload() {
  emit("reload");
}
</script>

<style scoped>
.back_bar {
  max-width: 1000px;
  width: 100%;
  margin: auto;
  display: grid;
  grid-template-columns: 90px 90px 1fr 90px 90px;
  position: relative;
  height: 24pt;
}

.back_bar.back_bar_teleport {
  grid-template-columns: 90px 90px 1fr 180px;
}

.reload_btn {
  height: 24pt;
  margin-right: 2px;
}

.teleport-zone {
  height: 24pt;
}

.back_btn {
  height: 24pt;
  margin-right: 2px;
}

.btn-toggle-zoom {
  height: 24pt;
  position: relative;
}

.title {
  text-align: center;
  line-height: 24pt;
  font-size: 18pt;
  margin: 0;
}
</style>
