<template>
  <div class="global-menu">
    <template v-for="(item, idx) in filteredMenu" :key="idx">
      <BButton
        v-if="item.type === 'item'"
        class="col"
        :variant="variant"
        :href="item.url"
        :target="item.newTab ? '_blank' : undefined"
        >{{ item.name }}</BButton
      >
      <template v-else-if="item.type === 'multi'">
        <BDropdown :variant="variant" :text="item.name" class="col">
          <BDropdownItem
            v-for="(subItem, subIdx) in item.subMenu"
            :key="subIdx"
            :variant="variant"
            :href="subItem.url"
            :target="subItem.newTab ? '_blank' : undefined"
            >{{ subItem.name }}</BDropdownItem
          >
        </BDropdown>
      </template>
      <template v-else-if="item.type === 'split'">
        <BDropdown
          split
          class="col"
          :variant="variant"
          :text="item.main.name"
          :splitHref="item.main.url"
          @click="splitClick(item.main)($event)"
        >
          <BDropdownItem
            v-for="(subItem, subIdx) in item.subMenu"
            :key="subIdx"
            :href="subItem.url"
            :target="subItem.newTab ? '_blank' : undefined"
            >{{ subItem.name }}</BDropdownItem
          >
        </BDropdown>
      </template>
    </template>
  </div>
</template>

<script setup lang="ts">
import type { GetFrontInfoResponse, GetMenuResponse, MenuItem, MenuMulti, MenuSplit } from "@/defs/API/Global";
import { BButton, BDropdown, BDropdownItem, type ButtonVariant } from "bootstrap-vue-3";
import { isArray } from "lodash-es";
import { computed, toRef } from "vue";

const props = defineProps<{
  modelValue: GetMenuResponse["menu"];
  globalInfo: GetFrontInfoResponse["global"];
  variant: ButtonVariant | 'sammo-base2',
  mobileRowSize?: number,
  desktopRowSize?: number,
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

function splitClick(menu: MenuItem) {
  return (e: Event) => {
    if (!menu.newTab) {
      return;
    }
    e.preventDefault();
    e.stopPropagation();
    window.open(menu.url);
  };
}
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";
.global-menu {
  display: grid;
  gap: 0.1rem;
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
