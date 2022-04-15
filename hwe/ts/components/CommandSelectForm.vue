<template>
  <div v-if="showForm" class="my-1">
    <div class="commandCategory row gx-0 gy-1">
      <div
        v-for="[categoryKey, { deco: categoryDeco }] of commandList"
        :key="categoryKey"
        class="categoryItem col-4 d-grid"
      >
        <BButton variant="success" :active="chosenCategory == categoryKey" @click="chosenCategory = categoryKey">
          {{ categoryDeco.altName ?? categoryDeco.name }}
        </BButton>
      </div>
    </div>
    <div
      class="commandList my-1"
      :style="{
        display: 'grid',
        alignItems: 'self-start',
      }"
    >
      <div
        v-for="[category, { values }] of commandList"
        :key="category"
        class="row gx-1 gy-1"
        :style="{ visibility: category == chosenCategory ? 'visible' : 'hidden', gridRow: '1', gridColumn: '1' }"
      >
        <div
          v-for="commandItem of values"
          :key="commandItem.value"
          class="col-6 d-grid"
          @click="close(commandItem.value)"
        >
          <div class="commandItem">
            <div class="commandBody">
              <p :class="['center', 'my-0', commandItem.possible ? '' : 'commandImpossible']">
                {{ commandItem.simpleName }}
                <span v-if="commandItem.compensation > 0" class="compensatePositive">▲</span>
                <span v-else-if="commandItem.compensation < 0" class="compensateNegative">▼</span>
              </p>
              <small class="center" :style="{ display: 'block' }">
                {{
                  commandItem.title.startsWith(commandItem.simpleName)
                    ? commandItem.title.substring(commandItem.simpleName.length)
                    : commandItem.title
                }}
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="!hideClose" class="commandBottom row mt-1 mb-1">
      <div class="offset-8 col-4 d-grid">
        <BButton @click="close()"> 닫기 </BButton>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import type { CommandItem } from "@/defs";
import { BButton } from "bootstrap-vue-3";
import { ref, type PropType, watch, onMounted } from "vue";

interface CategoryDecoration {
  name: string;
  altName?: string;
  //icon?: string,
  //color?: string,
  //backgroundColor?: string,
}

const props = defineProps({
  categoryInfo: {
    type: Object as PropType<Record<string, Omit<CategoryDecoration, "name">>>,
    default: () => {
      return {};
    },
    required: false,
  },
  commandList: {
    type: Object as PropType<
      {
        category: string;
        values: CommandItem[];
      }[]
    >,
    required: true,
  },
  anchor: {
    type: String,
    required: false,
    default: ".commandSelectFormAnchor",
  },
  hideClose: {
    type: Boolean,
    required: false,
    default: true,
  },
  activatedCategory: {
    type: String,
    required: false,
    default: "",
  },
});

const chosenCategory = ref<string>("-");
const chosenSubList = ref<CommandItem[]>([]);

const categories = new Set(props.commandList.map(({ category }) => category));

watch(
  () => props.activatedCategory,
  (newValue) => {
    chosenCategory.value = newValue;
  }
);

const showForm = ref(false);

function convCategoryDeco(category: string): CategoryDecoration {
  const itemInfo = props.categoryInfo?.[category];
  if (!itemInfo) {
    return {
      name: category,
    };
  }
  return {
    name: category,
    ...itemInfo,
  };
}

const commandList = ref(
  new Map<
    string,
    {
      deco: CategoryDecoration;
      values: CommandItem[];
    }
  >()
);

function updateCommandList(rawCommandList: typeof props.commandList) {
  commandList.value.clear();
  for (const { category, values } of rawCommandList) {
    commandList.value.set(category, {
      deco: convCategoryDeco(category),
      values,
    });
  }
}

watch(() => props.commandList, updateCommandList);
updateCommandList(props.commandList);

watch(chosenCategory, (category) => {
  const itemInfo = commandList.value?.get(category);
  if (itemInfo === undefined) {
    console.error(`category 없음: ${category}`);
    return;
  }
  chosenSubList.value = itemInfo.values;
  if (props.activatedCategory !== category) {
    emits("update:activatedCategory", category);
  }
});

onMounted(() => {
  if (!categories.has(props.activatedCategory)) {
    chosenCategory.value = props.commandList[0].category;
  } else {
    chosenCategory.value = props.activatedCategory;
  }
});

function show(): void {
  showForm.value = true;
}

function toggle(): void {
  showForm.value = !showForm.value;
  if (showForm.value === false) {
    emits("onClose");
  }
}

function close(category?: string): void {
  showForm.value = false;
  emits("onClose", category);
}

const emits = defineEmits<{
  (event: "onClose", command?: string): void;
  (event: "update:activatedCategory", category: string): void;
}>();

defineExpose({
  show,
  close,
  toggle,
});
</script>

<style scoped>
.commandItem {
  border: gray 1px solid;
  border-radius: 0.5em;
  overflow: hidden;
  cursor: pointer;
  padding: 0.1em;
  margin: 0;
  min-height: 2.8em;
  display: flex;
  align-items: center;
  justify-content: center;
}

</style>
