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
    :searchable="searchMode"
  >
    <template v-slot:option="props">
      <div v-if="props.option.title" v-html="props.option.title"></div>
      <div
        v-if="props.option.$groupLabel !== undefined"
        class="margin-filler"
        :style="{
          backgroundColor: groupByNation.get(props.option.$groupLabel).color,
          color: isBrightColor(
            groupByNation.get(props.option.$groupLabel).color
          )
            ? 'black'
            : 'white',
        }"
      >
        {{ groupByNation.get(props.option.$groupLabel).name }}
      </div>
    </template>
    <template v-slot:singleLabel="props">
      {{ props.option.simpleName }}
    </template>
  </v-multiselect>
</template>
<script lang="ts">
import { getNpcColor } from "@/common_legacy";
import { automata초성All } from "@/util/automata초성";
import { filter초성withAlphabet } from "@/util/filter초성withAlphabet";
import { isBrightColor } from "@/util/isBrightColor";
import { unwrap } from "@/util/unwrap";
import { defineComponent, PropType } from "vue";
import {
  procGeneralItem,
  procGeneralList,
  procNationItem,
} from "./processingRes";

type SelectedGeneral = {
  value: number;
  searchText: string;
  title: string;
  simpleName: string;
  obj: procGeneralItem;
};

export default defineComponent({
  props: {
    modelValue: {
      type: Number,
      required: true,
    },
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
  },
  emits: ["update:modelValue"],
  watch: {
    modelValue(val: number) {
      const target = this.targets.get(val);
      this.selectedGeneral = target;
    },
    selectedGeneral(val: SelectedGeneral) {
      this.$emit("update:modelValue", val.value);
    },
  },
  data() {
    const forFind: (
      | SelectedGeneral
      | {
          values: SelectedGeneral[];
          nationID: number;
        }
    )[] = [];
    const forFindGroup = new Map<number, SelectedGeneral[]>();
    const targets = new Map<number, SelectedGeneral>();

    let selectedGeneral;

    for (const gen of this.generals) {
      let groupArray = forFind;
      if (this.groupByNation) {
        const nationID = gen.nationID ?? 0;
        if (!forFindGroup.has(nationID)) {
          const nationItem = unwrap(this.groupByNation.get(nationID));
          let tmpArr: SelectedGeneral[] = [];
          forFindGroup.set(nationID, tmpArr);
          groupArray = tmpArr;

          forFind.push({
            nationID: nationItem.id,
            values: tmpArr,
          });
        } else {
          groupArray = unwrap(forFindGroup.get(nationID));
        }
      }

      const nameColor = getNpcColor(gen.npc);
      const name = nameColor
        ? `<span style="color:${nameColor}">${gen.name}</span>`
        : gen.name;

      const [filteredTextH, filteredTextA] = filter초성withAlphabet(gen.name);
      const [filteredTextHL1, filteredTextHL2] = automata초성All(filteredTextH);
      const obj: SelectedGeneral = {
        value: gen.no,
        title: this.textHelper
          ? this.textHelper(gen)
          : `${name} (${gen.leadership}/${gen.leadership}/${gen.intel})`,
        simpleName: gen.name,
        searchText: `${gen.name} ${filteredTextH} ${filteredTextA} ${filteredTextHL1} ${filteredTextHL2}`,
        obj: gen,
      };
      if (gen.no == this.modelValue) {
        selectedGeneral = obj;
      }
      groupArray.push(obj);
      targets.set(gen.no, obj);
    }
    console.log(forFind);
    return {
      selectedGeneral,
      searchMode: true,
      forFind,
      targets,
      isBrightColor,
    };
  },
});
</script>

<style lang="scss">
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