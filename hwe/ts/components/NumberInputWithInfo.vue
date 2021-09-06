<template>
  <div class="row form-group">
    <label :for="arg" class="col-sm-6 col-form-label">{{ title }}</label>
    <div class="col-sm-6">
      <input
        ref="input"
        :id="arg"
        type="number"
        :step="step ?? undefined"
        v-model="rawValue"
        class="form-control"
        :min="min ?? undefined"
        :max="max ?? undefined"
        @blur="onBlurNumber"
        @input="updateValue"
        :style="{ display: editmode ? undefined : 'none' }"
      />
      <input
        type="text"
        class="form-control"
        :value="printValue"
        @focus="onFocusText"
        :style="{ display: !editmode ? undefined : 'none' }"
      />
    </div>
  </div>
  <div style="text-align: right">
    <small class="form-text text-muted"><slot></slot></small>
  </div>
</template>
<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "NumberInputWithInfo",
  props: {
    int: {
      type: Boolean,
      required: false,
      default: true,
    },
    arg: {
      type: String,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
    min: {
      type: Number,
      required: false,
    },
    max: {
      type: Number,
      required: false,
    },
    step: {
      type: Number,
      required: false,
    },
    modelValue: {
      type: Number,
      default: 0,
    },
  },
  emits: ["input"],
  data() {
    return {
      editmode: false,
      rawValue: this.modelValue,
      printValue: this.modelValue.toLocaleString(),
    };
  },
  methods: {
    updateValue() {
      if (this.int) {
        this.rawValue = Math.floor(this.rawValue);
      }
      this.printValue = this.rawValue.toLocaleString();
      this.$emit("input", this.rawValue);
    },
    onBlurNumber() {
      this.editmode = false;
      this.printValue = this.rawValue.toLocaleString();
    },
    onFocusText() {
      this.editmode = true;
      setTimeout(() => {
        (this.$refs.input as HTMLInputElement).focus();
      }, 0);
    },
  },
});
</script>
