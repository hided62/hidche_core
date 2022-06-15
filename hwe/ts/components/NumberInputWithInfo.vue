<template>
  <div class="row form-group number-input-with-info">
    <label v-if="!right && title" class="col col-form-label">{{ title }}</label>
    <div class="col">
      <input
        ref="input"
        v-model="rawValue"
        type="number"
        :step="step ?? undefined"
        class="form-control f_tnum"
        :min="min ?? undefined"
        :max="max ?? undefined"
        :style="{ display: editmode ? undefined : 'none' }"
        @blur="onBlurNumber"
        @input="updateValue"
      />
      <input
        type="text"
        class="form-control f_tnum"
        :readonly="readonly"
        :value="printValue"
        :style="{ display: !editmode ? undefined : 'none' }"
        @focus="onFocusText"
      />
    </div>
    <label v-if="right && title" class="col col-form-label">{{ title }}</label>
  </div>
  <div style="text-align: right">
    <small class="form-text text-muted"><slot /></small>
  </div>
</template>
<script lang="ts">
import { clamp } from "lodash";
import { defineComponent } from "vue";

export default defineComponent({
  name: "NumberInputWithInfo",
  props: {
    readonly: {
      type: Boolean,
      required: false,
      default: false,
    },
    int: {
      type: Boolean,
      required: false,
      default: true,
    },
    title: {
      type: String,
      required: false,
      default: null,
    },
    min: {
      type: Number,
      required: false,
      default: 0,
    },
    max: {
      type: Number,
      default: undefined,
      required: false,
    },
    step: {
      type: Number,
      default: undefined,
      required: false,
    },
    modelValue: {
      type: Number,
      default: 0,
    },
    right: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  emits: ["update:modelValue"],
  data() {
    return {
      editmode: false,
      rawValue: this.modelValue,
      printValue: this.modelValue.toLocaleString(),
    };
  },
  watch: {
    modelValue: function (newVal: number) {
      this.rawValue = newVal;
      this.printValue = newVal.toLocaleString();
    },
  },
  methods: {
    updateValue() {
      if (this.readonly) {
        return;
      }
      if (this.int) {
        this.rawValue = Math.floor(this.rawValue);
      }
      this.printValue = this.rawValue.toLocaleString();
      if (this.min !== undefined || this.max !== undefined) {
        const clampedValue = clamp(this.rawValue, this.min ?? this.rawValue, this.max ?? this.rawValue);
        this.$emit("update:modelValue", clampedValue);
      } else {
        this.$emit("update:modelValue", this.rawValue);
      }
    },
    onBlurNumber() {
      this.editmode = false;
      this.printValue = this.rawValue.toLocaleString();
      if (this.min !== undefined || this.max !== undefined) {
        const clampedValue = clamp(this.rawValue, this.min ?? this.rawValue, this.max ?? this.rawValue);
        if (clampedValue !== this.rawValue) {
          this.rawValue = clampedValue;
          this.updateValue();
        }
      }
    },
    onFocusText() {
      if (this.readonly) {
        return;
      }
      this.editmode = true;
      setTimeout(() => {
        (this.$refs.input as HTMLInputElement).focus();
      }, 0);
    },
  },
});
</script>
