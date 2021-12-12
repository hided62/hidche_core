<template>
  <div class="row form-group number-input-with-info">
    <label v-if="!right" class="col-6 col-form-label ">{{ title }}</label>
    <div class="col-6">
      <input
        ref="input"
        type="number"
        :step="step ?? undefined"
        v-model="rawValue"
        class="form-control f_tnum"
        :min="min ?? undefined"
        :max="max ?? undefined"
        @blur="onBlurNumber"
        @input="updateValue"
        :style="{ display: editmode ? undefined : 'none' }"
      />
      <input
        type="text"
        class="form-control f_tnum"
        :readonly="readonly"
        :value="printValue"
        @focus="onFocusText"
        :style="{ display: !editmode ? undefined : 'none' }"
      />
    </div>
    <label v-if="right" class="col-6 col-form-label">{{ title }}</label>
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
      required: true,
    },
    min: {
      type: Number,
      required: false,
      default: 0,
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
    right: {
      type: Boolean,
      required: false,
      default: false,
    }
  },
  emits: ["update:modelValue"],
  data() {
    return {
      editmode: false,
      rawValue: this.modelValue,
      printValue: this.modelValue.toLocaleString(),
    };
  },
  watch:{
    modelValue: function(newVal:number){
      this.rawValue = newVal;
      this.printValue = newVal.toLocaleString();
    }
  },
  methods: {
    updateValue() {
      if(this.readonly){
        return;
      }
      if (this.int) {
        this.rawValue = Math.floor(this.rawValue);
      }
      this.printValue = this.rawValue.toLocaleString();
      this.$emit("update:modelValue", this.rawValue);
    },
    onBlurNumber() {
      this.editmode = false;
      this.printValue = this.rawValue.toLocaleString();
    },
    onFocusText() {
      if(this.readonly){
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
