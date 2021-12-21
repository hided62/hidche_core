<template>
  <TopBackBar :title="commandName" type="chief" />
  <div class="bg0">
    <div>
      국가에 임관합니다.<br />
      이미 임관/등용되었던 국가는 다시 임관할 수 없습니다.<br />
      바로 군주의 위치로 이동합니다.<br />
      임관할 국가를 목록에서 선택하세요.<br />
    </div>
    <div class="row">
      <div class="col-6 col-md-3">
        국가 :
        <SelectNation :nations="nationList" v-model="selectedNationID" />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
    <div class="nation-list">
      <div class="nation-header nation-row bg1 center">
        <div>국가명</div>
        <div>임관권유문</div>
      </div>
      <div
        v-for="[, nation] in nationList"
        :key="nation.id"
        class="nation-row s-border-b"
        @click="selectedNationID = nation.id"
      >
        <div
          :style="{
            backgroundColor: nation.color,
            color: isBrightColor(nation.color) ? 'black' : 'white',
            fontSize: '1.3em',
          }"
          class="d-grid"
        >
          <div class="align-self-center center">{{ nation.name }}</div>
        </div>
        <div class="nation-scout-plate align-self-center">
          <div class="nation-scout-msg" v-html="nation.scoutMsg" />
        </div>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import SelectNation from "@/processing/SelectNation.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { procNationItem, procNationList } from "../processingRes";
import { isBrightColor } from "@/util/isBrightColor";
declare const commandName: string;

declare const procRes: {
  nationList: procNationList;
};

export default defineComponent({
  components: {
    SelectNation,
    TopBackBar,
    BottomBar,
  },
  setup() {
    const nationList = new Map<number, procNationItem>();
    for (const nationItem of procRes.nationList) {
      nationList.set(nationItem.id, nationItem);
    }

    const selectedNationID = ref(procRes.nationList[0].id);

    function selectedNation(nationID: number) {
      selectedNationID.value = nationID;
    }

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          destNationID: selectedNationID.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      nationList: ref(nationList),
      selectedNationID,
      commandName,
      isBrightColor,
      selectedNation,
      submit,
    };
  },
});
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

@include media-1000px {
  .nation-list .nation-row {
    display: grid;
    grid-template-columns: 130px 870px;
  }
}

@include media-500px {
  .nation-list .nation-row {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: 1fr minmax(1fr, calc(200px * 500 / 870));
  }

  .nation-scout-plate {
    max-height: calc(200px * 500 / 870);
    overflow: hidden;
  }

  .nation-scout-msg {
    width: 870px;
    transform-origin: 0px 0px;
    transform: scale(calc(500 / 870));
  }
}
</style>