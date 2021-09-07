<template>
  <CToaster placement="top-end">
    <CToast
      v-for="(toast, index) in modelValue"
      :key="index"
      :delay="toast.delay??delay"
    >
      <CToastHeader
        :class="['text-white', `bg-${toast.type ?? 'primary'}`]"
        close
      >
        <span class="me-auto fw-bold">{{ toast.title }}</span>
      </CToastHeader>
      <CToastBody>
        {{ toast.content }}
      </CToastBody>
    </CToast>
  </CToaster>
</template>
<script lang="ts">
import { defineComponent, PropType } from "vue";
import { ToastType } from "../defs";
import { CToaster, CToast, CToastHeader, CToastBody } from "@coreui/vue";

export default defineComponent({
  name: "MyToast",
  components: {
    CToaster,
    CToast,
    CToastHeader,
    CToastBody,
  },
  emits: ["update:modelValue"],
  props: {
    modelValue: {
      type: Array as PropType<ToastType[]>,
      required: true,
    },
    delay: {
        type: Number,
        required:false,
        default:5000,
    }
  },
});
</script>
