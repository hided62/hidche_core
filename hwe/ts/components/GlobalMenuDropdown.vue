<template>
  <ul class="dropdown-menu dropdown-menu-start">
    <template v-for="(item, idx) in filteredMenu" :key="idx">
      <li v-if="item.type === 'item'">
        <a class="dropdown-item" :href="item.url" :target="item.newTab ? '_blank' : undefined">
          {{ item.name }}
        </a>
      </li>
      <template v-else-if="item.type === 'multi'">
        <li :style="{ orphans: item.subMenu.length + 1 }">
          <span class="dropdown-item disabled">{{ item.name }}</span>
        </li>
        <li v-for="(subItem, subIdx) in item.subMenu" :key="subIdx">
          <a class="dropdown-item subItem" :href="subItem.url" :target="subItem.newTab ? '_blank' : undefined">{{
            subItem.name
          }}</a>
        </li>
      </template>
      <template v-else-if="item.type === 'split'">
        <li :style="{ orphans: item.subMenu.length + 1 }">
          <a class="dropdown-item" :href="item.main.url" :target="item.main.newTab ? '_blank' : undefined">{{
            item.main.name
          }}</a>
        </li>
        <li v-for="(subItem, subIdx) in item.subMenu" :key="subIdx">
          <a class="dropdown-item subItem" :href="subItem.url" :target="subItem.newTab ? '_blank' : undefined">{{
            subItem.name
          }}</a>
        </li>
      </template>
    </template>
  </ul>
</template>

<script setup lang="ts">
import type { GetFrontInfoResponse, GetMenuResponse, MenuItem, MenuMulti, MenuSplit } from "@/defs/API/Global";
import { isArray } from "lodash";
import { computed, toRef } from "vue";

const props = defineProps<{
  modelValue: GetMenuResponse["menu"];
  globalInfo: GetFrontInfoResponse["global"];
  mobileRowSize?: number;
  desktopRowSize?: number;
  columns?: number;
}>();

const columns = computed(() => {
  return props.columns ?? 3;
});

const modelValue = toRef(props, "modelValue");
const globalInfo = toRef(props, "globalInfo");

type MenuVariant = MenuItem | MenuSplit | MenuMulti;

function filterMenu(menu: MenuVariant | MenuVariant[]): MenuVariant | MenuVariant[] | undefined {
  if (isArray(menu)) {
    return menu.filter(filterMenu) as MenuVariant[];
  }

  if (menu.type === "item") {
    if (!menu.condShowVar) {
      return menu;
    }
    const cond = menu.condShowVar;
    if (cond in globalInfo.value) {
      if (cond.startsWith("!")) {
        if (!globalInfo.value[cond.slice(1) as keyof GetFrontInfoResponse["global"]]) {
          return menu;
        }
      } else if (globalInfo.value[cond as keyof GetFrontInfoResponse["global"]]) {
        return menu;
      }
    }
    return undefined;
  }

  if (menu.type === "multi") {
    const filtered = menu.subMenu.filter(filterMenu) as MenuVariant[];
    if (filtered.length === 0) {
      return undefined;
    }
    if (filtered.length === 1) {
      return filtered[0];
    }
    return filtered;
  }

  if (menu.type === "split") {
    const filterMain = filterMenu(menu.main);
    if (!filterMain) {
      return undefined;
    }
    const filtered = menu.subMenu.filter(filterMenu) as MenuVariant[];
    if (filtered.length === 0) {
      return filterMain;
    }
    return filtered;
  }
}

const filteredMenu = computed(() => filterMenu(modelValue.value) as GetMenuResponse["menu"]);
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

.subItem {
  padding-left: 1.5rem;
}

.dropdown-menu {
  columns: v-bind(columns);
}
</style>
