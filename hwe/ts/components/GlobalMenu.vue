<template>
  <div class="global-menu">
    <template v-for="(item, idx) in filteredMenu" :key="idx">
      <BButton
        v-if="item.type === 'item'"
        class="col"
        :variant="variant"
        :href="item.url"
        :target="item.newTab ? '_blank' : undefined"
        @click="menuClick($event, item)"
        >{{ item.name }}</BButton
      >
      <BDropdown v-else-if="item.type === 'multi'" :variant="variant" :text="item.name" class="col">
        <template v-for="(subItem, subIdx) in item.subMenu" :key="subIdx">
          <template v-if="subItem.type === 'item'">
            <BDropdownItem
              :variant="variant"
              :href="subItem.url"
              :target="subItem.newTab ? '_blank' : undefined"
              @click="menuClick($event, subItem)"
              >{{ subItem.name }}</BDropdownItem
            >
          </template>
          <BDropdownDivider v-else />
        </template>
      </BDropdown>
      <BDropdown
        v-else-if="item.type === 'split'"
        split
        class="col"
        :variant="variant"
        :text="item.main.name"
        :splitHref="item.main.url"
        @click="menuClick($event, item.main)"
      >
        <template v-for="(subItem, subIdx) in item.subMenu" :key="subIdx">
          <BDropdownItem
            v-if="subItem.type === 'item'"
            :variant="variant"
            :href="subItem.url"
            :target="subItem.newTab ? '_blank' : undefined"
            @click="menuClick($event, subItem)"
            >{{ subItem.name }}</BDropdownItem
          >
          <BDropdownDivider v-else />
        </template>
      </BDropdown>
    </template>
  </div>
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
import { BButton, BDropdown, BDropdownItem, BDropdownDivider, type ButtonVariant } from "bootstrap-vue-next";
import { isArray } from "lodash-es";
import { computed, toRef } from "vue";

const props = defineProps<{
  modelValue: GetMenuResponse["menu"];
  globalInfo: GetFrontInfoResponse["global"];
  variant: ButtonVariant | "sammo-base2";
  mobileRowSize?: number;
  desktopRowSize?: number;
}>();

const emit = defineEmits<{
  (event: "reqCall", value: string): void;
}>();

const mobileRowSize = computed(() => {
  return props.mobileRowSize || 4;
});

const desktopRowSize = computed(() => {
  return props.desktopRowSize || 8;
});

const variant = computed(() => {
  return props.variant as ButtonVariant;
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
.global-menu {
  display: grid;
  gap: 0.1rem;
  white-space: nowrap;
}

@include media-1000px {
  .global-menu {
    grid-template-columns: repeat(v-bind(desktopRowSize), 1fr);
  }
}
@include media-500px {
  .global-menu {
    grid-template-columns: repeat(v-bind(mobileRowSize), 1fr);
  }
}
</style>
