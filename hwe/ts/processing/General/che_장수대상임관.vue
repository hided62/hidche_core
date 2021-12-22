<template>
  <TopBackBar :title="commandName" v-model:searchable="searchable" />
  <div class="bg0">
    <div>
      장수를 따라 임관합니다.<br />
      이미 임관/등용되었던 국가는 다시 임관할 수 없습니다.<br />
      바로 군주의 위치로 이동합니다.<br />
      임관할 국가를 목록에서 선택하세요.<br />
    </div>
    <div class="row">
      <div class="col-8 col-md-4">
        장수 :
        <SelectGeneral
          :generals="generalList"
          :groupByNation="nationList"
          :textHelper="textHelpGeneral"
          :searchable="searchable"
          v-model="selectedGeneralID"
        />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
    <div class="nation-list">
      <div class="nation-header nation-row bg1 center">
        <div>국가명</div>
        <div>임관권유문</div>
        <div class="zoom-toggle d-grid">
          <b-button
            :pressed="toggleZoom"
            :variant="toggleZoom ? 'info' : 'secondary'"
            v-model="toggleZoom"
            @click="toggleZoom = !toggleZoom"
            >{{ toggleZoom ? "작게 보기" : "크게 보기" }}</b-button
          >
        </div>
      </div>
      <div
        v-for="[, nation] in nationList"
        :key="nation.id"
        :class="['nation-row', 's-border-b', toggleZoom ? 'on-zoom' : 'on-fit']"
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
import SelectGeneral from "@/processing/SelectGeneral.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  convertGeneralList,
  getProcSearchable,
  procGeneralItem,
  procGeneralKey,
  procGeneralRawItemList,
  procNationItem,
  procNationList,
} from "../processingRes";
import { getNpcColor } from "@/common_legacy";
import { isBrightColor } from "@/util/isBrightColor";
declare const commandName: string;

declare const procRes: {
  generals: procGeneralRawItemList;
  generalsKey: procGeneralKey[];
  nationList: procNationList;
};

export default defineComponent({
  components: {
    SelectGeneral,
    TopBackBar,
    BottomBar,
  },
  setup() {
    const generalList = convertGeneralList(
      procRes.generalsKey,
      procRes.generals
    );

    const toggleZoom = ref(true);
    const selectedGeneralID = ref(generalList[0].no);

    function textHelpGeneral(gen: procGeneralItem): string {
      const nameColor = getNpcColor(gen.npc);
      const name = nameColor
        ? `<span style="color:${nameColor}">${gen.name}</span>`
        : gen.name;
      return name;
    }

    const nationList = new Map<number, procNationItem>();
    for (const nationItem of procRes.nationList) {
      nationList.set(nationItem.id, nationItem);
    }

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          destGeneralID: selectedGeneralID.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      searchable: getProcSearchable(),
      nationList: ref(nationList),
      selectedGeneralID,
      generalList,
      commandName,
      toggleZoom,
      isBrightColor,
      textHelpGeneral,
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

  .zoom-toggle {
    display: none;
  }

  .zoom-toggle > * {
    display: none;
  }
}

@include media-500px {
  .nation-list .nation-row.nation-header {
    display: grid;
    grid-template-columns: 3fr 1fr;
    grid-template-rows: 1fr 1fr;

    .zoom-toggle {
      grid-column: 2/3;
      grid-row: 1/3;
    }
  }

  .nation-list .nation-row {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: 1fr minmax(1fr, calc(200px * 500 / 870));
  }

  .on-fit {
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

  .on-zoom {
    .nation-scout-plate {
      max-height: 200px;
      overflow-y: hidden;
      overflow-x: auto;
    }

    .nation-scout-msg {
      max-width: 870px;
    }
  }
}
</style>