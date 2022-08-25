<template>
  <v-multiselect
    v-model="selectedGeneral"
    class="selectedGeneral"
    :allow-empty="false"
    :options="forFind"
    :group-select="false"
    :group-values="groupByNation ? 'values' : undefined"
    group-label="nationID"
    label="searchText"
    track-by="value"
    :show-labels="false"
    selectLabel="선택(엔터)"
    selectGroupLabel=""
    selectedLabel="선택됨"
    deselectLabel="해제(엔터)"
    deselectGroupLabel=""
    placeholder="장수 선택"
    :maxHeight="400"
    :searchable="searchable"
  >
    <template #option="prop">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-if="prop.option.title" v-html="prop.option.title" />
      <div
        v-if="prop.option.$groupLabel !== undefined"
        class="margin-filler"
        :style="{
          backgroundColor: groupByNation?.get(prop.option.$groupLabel)?.color,
          color: isBrightColor(groupByNation?.get(prop.option.$groupLabel)?.color ?? '#ffffff') ? 'black' : 'white',
        }"
      >
        {{ groupByNation?.get(prop.option.$groupLabel)?.name }}
      </div>
    </template>
    <template #singleLabel="prop">
      {{ prop.option.simpleName }}
      {{ groupByNation ? `[${groupByNation.get(prop.option.obj.nationID)?.name}]` : undefined }}
    </template>
  </v-multiselect>
</template>
<script setup lang="ts">
import { getNPCColor } from "@/utilGame";
import { convertSearch초성 } from "@/util/convertSearch초성";
import { isBrightColor } from "@/util/isBrightColor";
import { unwrap } from "@/util/unwrap";
import { ref, watch, type PropType } from "vue";
import VueTypes from "vue-types";
import type { procGeneralItem, procGeneralList, procNationItem } from "./processingRes";

type SelectedGeneral = {
  value: number;
  searchText: string;
  title: string;
  simpleName: string;
  obj: procGeneralItem;
};

const props = defineProps({
  modelValue: VueTypes.number.isRequired,
  generals: {
    type: Array as PropType<procGeneralList>,
    required: true,
  },
  textHelper: {
    type: Function as PropType<(item: procGeneralItem) => string>,
    required: false,
    default: undefined,
  },
  groupByNation: {
    type: Map as PropType<Map<number, procNationItem>>,
    required: false,
    default: undefined,
  },
  searchable: {
    type: Boolean,
    required: false,
    default: true,
  },
  troops: {
    type: Object as PropType<Record<number, { troop_leader: number; nation: number; name: string }>>,
    required: false,
    default: undefined,
  },
});

const emit = defineEmits<{
  (event: "update:modelValue", value: number): void;
}>();

const selectedGeneral = ref<SelectedGeneral>();
const targets = new Map<number, SelectedGeneral>();

watch(
  () => props.modelValue,
  (val) => {
    const target = targets.get(val);
    selectedGeneral.value = target;
  }
);

watch(selectedGeneral, (val) => {
  if (val === undefined) {
    return;
  }
  emit("update:modelValue", val.value);
});

const forFind = ref<
  (
    | SelectedGeneral
    | {
        values: SelectedGeneral[];
        nationID: number;
      }
  )[]
>([]);

const forFindGroup = ref(new Map<number, SelectedGeneral[]>());

watch(
  () => props.generals,
  (generals) => {
    const tmpFind: typeof forFind["value"] = [];
    const tmpFindGroup = new Map<number, SelectedGeneral[]>();

    for (const gen of generals) {
      let groupArray = tmpFind;
      if (props.groupByNation) {
        const nationID = gen.nationID ?? 0;
        if (!tmpFindGroup.has(nationID)) {
          const nationItem = unwrap(props.groupByNation.get(nationID));
          let tmpArr: SelectedGeneral[] = [];
          tmpFindGroup.set(nationID, tmpArr);
          groupArray = tmpArr;

          tmpFind.push({
            nationID: nationItem.id,
            values: tmpArr,
          });
        } else {
          groupArray = unwrap(tmpFindGroup.get(nationID));
        }
      }

      const nameColor = getNPCColor(gen.npc);
      const name = nameColor ? `<span style="color:${nameColor}">${gen.name}</span>` : gen.name;

      const searchText = convertSearch초성(gen.name);
      if (gen.no == gen.troopID && props.troops !== undefined && gen.no in props.troops) {
        const troopName = props.troops[gen.no].name;
        console.log(troopName);
        searchText.push(...convertSearch초성(troopName));
      }

      const obj: SelectedGeneral = {
        value: gen.no,
        title: props.textHelper ? props.textHelper(gen) : `${name} (${gen.leadership}/${gen.strength}/${gen.intel})`,
        simpleName: gen.name,
        searchText: searchText.join("|"),
        obj: gen,
      };
      if (gen.no == props.modelValue) {
        selectedGeneral.value = obj;
      }
      groupArray.push(obj);
      targets.set(gen.no, obj);
    }

    forFindGroup.value = tmpFindGroup;
    forFind.value = tmpFind;
  },
  { immediate: true }
);
</script>
<style lang="scss">
@import "@scss/common/break_500px.scss";
@import "@scss/common/variables.scss";
@import "@scss/common/bootswatch_custom_variables.scss";
@import "bootstrap/scss/bootstrap-utilities.scss";

.selectedGeneral {
  $vue-multiselect-bg: $gray-700;
  $vue-multiselect-color: $gray-100;
  $form-select-color: $gray-100;
  $text-muted: $gray-400;
  $dark: $gray-100;
  $light: $gray-700;
  $vue-multiselect-option-selected-bg: $gray-600;
  @import "@scss/common/vue-multiselect.scss";
  color: $vue-multiselect-color;

  input {
    color: $vue-multiselect-color;
  }

  .multiselect__option--group {
    padding: 0;
  }
  .margin-filler {
    padding: calc($vue-multiselect-padding-y + 0.17em) $vue-multiselect-padding-x;
  }
}
</style>
