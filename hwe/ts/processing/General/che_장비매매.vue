<template>
  <!-- eslint-disable vue/no-v-html -->
  <TopBackBar v-model:searchable="searchable" :title="commandName" />
  <div class="bg0">
    <div>
      장비를 구입하거나 매각합니다.
      <br />
      현재 구입 불가능한 것은 <span style="color: red">붉은색</span>으로 표시됩니다.
      <br />
      현재 도시 치안 : {{ citySecu.toLocaleString() }} &nbsp;&nbsp;&nbsp;현재 자금 : {{ gold.toLocaleString() }}
      <br />
    </div>
    <div v-if="selectedItemObj" class="row">
      <div class="col-8 col-lg-4">
        장비:
        <Multiselect
          v-model="selectedItemObj"
          class="selectedItemObj"
          :allow-empty="false"
          :options="forFind"
          :group-select="false"
          group-values="values"
          group-label="category"
          label="searchText"
          track-by="simpleName"
          :show-labels="false"
          select-label="선택(엔터)"
          select-group-label
          selected-label="선택됨"
          deselect-label="해제(엔터)"
          deselect-group-label
          placeholder="아이템 선택"
          :max-height="400"
          :searchable="searchable"
        >
          <template #option="props">
            <div
              v-if="props.option.html"
              :style="{
                color: props.option.notAvailable ? 'red' : undefined,
              }"
              v-html="`${props.option.html} ${props.option.notAvailable ? '(불가)' : ''}`"
            />
            <div
              v-else-if="props.option.simpleName"
              :style="{
                color: props.option.notAvailable ? 'red' : undefined,
              }"
            >
              {{ props.option.simpleName }}
              {{ props.option.notAvailable ? "(불가)" : undefined }}
            </div>
          </template>
          <template #singleLabel="props">
            [{{ ItemTypeNameMap[props.option.type as keyof typeof ItemTypeNameMap] }}]
            {{ props.option.simpleName }}
          </template>
        </Multiselect>
      </div>
      <div class="col-4 col-lg-2 d-grid">
        <b-button @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
    <div v-if="selectedItemObj && selectedItemObj.obj.id != NoneValue" class="row">
      <div class="col-4 col-lg-2 align-self-center text-center">
        {{ selectedItemObj.obj.name }}
      </div>
      <div class="col" v-html="selectedItemObj.obj.info" />
    </div>
  </div>

  <BottomBar :title="commandName" />
</template>

<script lang="ts">
declare const staticValues: {
  commandName: string;
};

declare const procRes: {
  citySecu: number;
  gold: number;
  itemList: procItemList;
  ownItem: Record<ItemTypeKey, procItemType>;
};
</script>
<script setup lang="ts">
import { Multiselect } from "vue-multiselect";
import { onMounted, reactive, ref, toRefs } from "vue";
import { unwrap } from "@/util/unwrap";
import { entriesWithType } from "@util/entriesWithType";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { getProcSearchable, type procItemList, type procItemType } from "../processingRes";
import { type ItemTypeKey, ItemTypeNameMap, NoneValue, type ValuesOf } from "@/defs";
import { convertSearch초성 } from "@/util/convertSearch초성";

const commandName = staticValues.commandName;

type selectItemKey = {
  type: ItemTypeKey;
  id: string;
  html: string;
  simpleName: string;
  searchText: string;
  notAvailable?: boolean;
  obj: procItemType;
};

const forFind = ref<
  {
    category: ValuesOf<typeof ItemTypeNameMap> | "판매";
    values: selectItemKey[];
  }[]
>([]);

//판매 처리
const forSell = ref<typeof forFind.value[0]>({
  category: "소유 물품 판매",
  values: [],
});
const selectedItemObj = ref<selectItemKey>();

const self = reactive(procRes);
const { citySecu, gold } = toRefs(self);

onMounted(() => {
  forFind.value = [];
  forSell.value.values = [];
  for (const [type, ownItem] of entriesWithType(procRes.ownItem)) {
    const typeName = ItemTypeNameMap[type];
    const itemNameHelp = ownItem.id == NoneValue ? "" : ` [${ownItem.name}, ${(ownItem.cost / 2).toLocaleString()}금]`;
    forSell.value.values.push({
      type,
      id: NoneValue,
      html: `${typeName} 판매${itemNameHelp}`,
      simpleName: `${ownItem.id == NoneValue ? typeName : ownItem.name} 판매`,
      searchText: convertSearch초성(typeName).join("|"),
      notAvailable: ownItem.id == NoneValue,
      obj: ownItem,
    });
  }
  forFind.value.push(forSell.value);
  selectedItemObj.value = forSell.value.values[0];

  for (const [type, itemSubList] of entriesWithType(procRes.itemList)) {
    const values: selectItemKey[] = [];
    const forBuy: typeof forFind.value[0] = {
      category: `${ItemTypeNameMap[type]} 구매`,
      values,
    };

    for (const itemObj of itemSubList.values) {
      values.push({
        type,
        id: itemObj.id,
        html: `${
          itemObj.name
        } 구매 [${itemObj.cost.toLocaleString()}금, 필요 치안 ${itemObj.reqSecu.toLocaleString()}]`,
        simpleName: `${itemObj.name} 구매`,
        searchText: convertSearch초성(itemObj.name).join("|"),
        notAvailable: itemObj.reqSecu > procRes.citySecu || procRes.gold < itemObj.cost,
        obj: itemObj,
      });
    }

    forFind.value.push(forBuy);
  }
});

async function submit(e: Event) {
  if(!selectedItemObj.value) return;

  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      itemType: selectedItemObj.value.type,
      itemCode: selectedItemObj.value.id,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}

const searchable = getProcSearchable();

</script>
