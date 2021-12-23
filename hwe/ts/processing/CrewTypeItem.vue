<template>
  <div class="crewTypeItem crewTypeSubGrid text-center s-border-b">
    <div
      class="crewTypeImg"
      :style="{
        background: '#222222 no-repeat center',
        backgroundImage: `url('${crewType.img}')`,
        backgroundSize: '64px',
        outline: 'solid 1px gray',
      }"
    ></div>
    <div
      :style="{
        backgroundColor: crewType.notAvailable
          ? 'red'
          : crewType.reqTech == 0
          ? 'green'
          : 'limegreen',
        height: '100%',
      }"
      class="d-grid crewTypeName"
    >
      <div style="margin: auto">
        {{ crewType.name }}
      </div>
    </div>
    <div>{{ crewType.attack }}</div>
    <div>{{ crewType.defence }}</div>
    <div>{{ crewType.speed }}</div>
    <div>{{ crewType.avoid }}</div>
    <div>{{ crewType.baseCost.toFixed(1) }}</div>
    <div>{{ crewType.baseRice.toFixed(1) }}</div>
    <div class="crewTypePanel">
      <b-button-group
        ><b-button class="py-1" variant="dark" @click="beHalf">절반</b-button
        ><b-button class="py-1" variant="dark" @click="beFilled"
          >채우기</b-button
        ><b-button class="py-1" variant="dark" @click="beFull"
          >가득</b-button
        ></b-button-group
      >
      <div class="row">
        <div class="col mx-2">
          <div class="input-group my-0">
            <span class="input-group-text py-1">병력</span>
            <input
              type="number"
              class="form-control py-1 f_tnum px-0 text-end"
              v-model="amount"
              min="1"
            />
            <span class="input-group-text py-1 f_tnum">00명</span>
            <span
              class="input-group-text py-1 f_tnum"
              style="
                text-align: right;
                min-width: 10ch;
                color: #303030;
                background-color: #ddd;
              "
              ><div style="margin-left: auto">
                {{ Math.ceil(amount * crewType.baseCost * goldCoeff).toLocaleString() }}금
              </div></span
            >
          </div>
        </div>
      </div>
    </div>
    <div class="crewTypeBtn d-grid">
      <b-button variant="primary" @click="doSubmit">{{ commandName }}</b-button>
    </div>
    <div
      class="crewTypeInfo text-start"
      v-html="crewType.info.join('<br>')"
    ></div>
  </div>
</template>
<script lang="ts">
import { defineComponent, ref } from "vue";
import VueTypes from "vue-types";

export default defineComponent({
  props: {
    crewType: VueTypes.object.isRequired,
    leadership: VueTypes.number.isRequired,
    commandName: VueTypes.string.isRequired,
    currentCrewType: VueTypes.number.def(-1),
    crew: VueTypes.number.def(0),
    goldCoeff: VueTypes.number.isRequired,
  },
  emits: ["submitOutput", "update:amount"],
  watch: {
    amount(val: number) {
      this.$emit("update:amount", val);
    },
  },
  setup(props, { emit }) {
    const amount = ref(0);

    function beHalf() {
      amount.value = Math.ceil(props.leadership * 0.5);
    }

    function beFilled() {
      if (props.crewType.id == props.currentCrewType) {
        amount.value = Math.max(
          1,
          props.leadership - Math.floor(props.crew / 100)
        );
      } else {
        amount.value = props.leadership;
      }
    }

    function beFull() {
      amount.value = Math.floor(props.leadership * 1.2);
    }

    function doSubmit(e: Event) {
      emit("submitOutput", e, amount.value, props.crewType.id);
    }

    beFilled();

    return {
      amount,
      beHalf,
      beFilled,
      beFull,
      doSubmit,
    };
  },
});
</script>
