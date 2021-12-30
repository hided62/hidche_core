<template>
  <CToaster class="my-toaster" placement="top-end" v-if="toasts.length > 0">
    <template v-for="(toast, index) in toasts" :key="index">
      <CToast :delay="toast.delay ?? delay">
        <CToastHeader :class="['text-white', `bg-${toast.type ?? 'primary'}`]">
          <span class="me-auto fw-bold">{{ toast.title }}</span>
          <CToastClose class="my-close" color="secondary" size="sm" />
        </CToastHeader>
        <CToastBody> {{ toast.content }} {{ toast.visible }} </CToastBody>
      </CToast>
    </template>
  </CToaster>
</template>
<script lang="ts">
import { defineComponent, PropType, ref, watch } from "vue";
import { ToastType } from "@/defs";
import {
  CToaster,
  CToast,
  CToastHeader,
  CToastBody,
  CToastClose,
} from "@coreui/vue/src/components/toast";

export default defineComponent({
  components: {
    CToaster,
    CToast,
    CToastHeader,
    CToastBody,
    CToastClose,
  },
  emits: ["update:modelValue"],
  props: {
    modelValue: {
      type: Array as PropType<ToastType[]>,
      required: true,
    },
    delay: {
      type: Number,
      required: false,
      default: 5000,
    },
  },
  setup(props, { emit }) {
    let doneCnt = 0;
    const doneMap = new Map<ToastType, boolean>();
    let toasts = ref(props.modelValue);

    function onClose(toast: ToastType) {
      const status = doneMap.get(toast);
      if (status === undefined) {
        return;
      }
      if (status) {
        return;
      }
      doneCnt -= 1;

      if (doneCnt != 0) {
        doneMap.set(toast, true);
        return;
      }

      doneMap.clear();
      toasts.value.length = 0;
      emit("update:modelValue", toasts);
    }

    watch(props.modelValue, (values) => {
      for (const item of values) {
        if (!doneMap.has(item)) {
          doneMap.set(item, false);
          doneCnt += 1;

          setTimeout(() => {
            onClose(item);
          }, (item.delay ?? props.delay) + 1000);
        }
      }
      if (toasts.value !== values) {
        toasts.value = values;
      }
    });

    return {
      onClose,
      toasts,
    };
  },
});
</script>
<style>
.my-toaster {
  z-index: 199;
}
.my-toaster .btn.btn-close.my-close,
.my-toaster .btn.btn-close.my-close:hover,
.my-toaster .btn.btn-close.my-close:active {
  border: 0;
  padding: 0;
}
</style>