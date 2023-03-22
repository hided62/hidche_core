<template>
  <ul class="dropdown-menu dropdown-menu-start">
    <template v-for="(item, idx) in filteredMenu" :key="idx">
      <li v-if="item.type === 'item'">
        <a
          class="dropdown-item"
          :href="item.url"
          :target="item.newTab ? '_blank' : undefined"
          @click="menuClick($event, item)"
        >
          {{ item.name }}
        </a>
      </li>
      <template v-else-if="item.type === 'multi'">
        <li :style="{ orphans: item.subMenu.length + 1 }">
          <span class="dropdown-item disabled">{{ item.name }}</span>
        </li>
        <template v-for="(subItem, subIdx) in item.subMenu" :key="subIdx">
          <li v-if="subItem.type === 'item'">
            <a
              class="dropdown-item subItem"
              :href="subItem.url"
              :target="subItem.newTab ? '_blank' : undefined"
              @click="menuClick($event, subItem)"
              >{{ subItem.name }}</a
            >
          </li>
          <hr v-else class="dropdown-divider" />
        </template>
        <hr class="dropdown-divider" />
      </template>
      <template v-else-if="item.type === 'split'">
        <li :style="{ orphans: item.subMenu.length + 1 }">
          <a
            class="dropdown-item"
            :href="item.main.url"
            :target="item.main.newTab ? '_blank' : undefined"
            @click="menuClick($event, item.main)"
            >{{ item.main.name }}</a
          >
        </li>
        <template v-for="(subItem, subIdx) in item.subMenu" :key="subIdx">
          <li v-if="subItem.type === 'item'">
            <a
              class="dropdown-item subItem"
              :href="subItem.url"
              :target="subItem.newTab ? '_blank' : undefined"
              @click="menuClick($event, subItem)"
              >{{ subItem.name }}</a
            >
          </li>
          <hr v-else class="dropdown-divider" />
        </template>
        <hr class="dropdown-divider" />
      </template>
    </template>
  </ul>
</template>

<script setup lang="ts">
import type {
  GetFrontInfoResponse,
  GetMenuResponse,
  MenuItem,
  MenuLine,
  MenuMulti,
  MenuSplit,
} from "@/defs/API/Global";
import { isArray } from "lodash-es";
import { computed, toRef } from "vue";

const props = defineProps<{
  modelValue: GetMenuResponse["menu"];
  globalInfo: GetFrontInfoResponse["global"];
  mobileRowSize?: number;
  desktopRowSize?: number;
  columns?: number;
}>();

const emit = defineEmits<{
  (event: "reqCall", value: string): void;
}>();

const columns = computed(() => {
  return props.columns ?? 3;
});

const modelValue = toRef(props, "modelValue");
const globalInfo = toRef(props, "globalInfo");

type MenuVariant = MenuItem | MenuSplit | MenuMulti | MenuLine;

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

function menuClick(e: Event, menu: MenuItem) {
  if (menu.funcCall) {
    //TODO: CTRL+클릭도 대응해야하는지 고민이 필요함
    e.preventDefault();
    emit("reqCall", menu.url);
    return;
  }
  if (!menu.url) {
    e.preventDefault();
    return;
  }
  if (!menu.newTab) {
    //자동 핸들러를 따름
    return;
  }
  e.preventDefault();
  window.open(menu.url);
}
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
