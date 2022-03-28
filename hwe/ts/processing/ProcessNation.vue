<template>
  <TopBackBar v-model:searchable="searchable" :title="commandName" :type="procEntryMode" />
  <div class="bg0">
    <MapLegacyTemplate
      v-model="selectedCityObj"
      :isDetailMap="false"
      :clickableAll="true"
      :neutralView="true"
      :useCachedMap="true"
      :mapName="mapName"
    />

    <div v-if="commandName == '선전포고'">
      타국에게 선전 포고합니다.<br />
      선전 포고할 국가를 목록에서 선택하세요.<br />
      고립되지 않은 아국 도시에서 인접한 국가에 선포 가능합니다.<br />
      초반제한 해제 2년전부터 선포가 가능합니다. ({{ startYear + 1 }}년 1월부터 가능)<br />
      현재 선포가 불가능한 국가는 배경색이
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '급습'">
      선택된 국가에 급습을 발동합니다.<br />
      선포, 전쟁중인 상대국에만 가능합니다.<br />
      상대 국가를 목록에서 선택하세요.<br />
      현재 급습이 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '불가침 파기 제의'">
      불가침중인 국가에 조약 파기를 제의합니다.<br />
      제의할 국가를 목록에서 선택하세요.<br />
      현재 제의가 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '이호경식'">
      선택된 국가에 이호경식을 발동합니다.<br />
      선포, 전쟁중인 상대국에만 가능합니다.<br />
      상대 국가를 목록에서 선택하세요.<br />
      현재 이호경식이 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '종전 제의'">
      전쟁중인 국가에 종전을 제의합니다.<br />
      제의할 국가를 목록에서 선택하세요.<br />
      현재 제의가 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div v-else-if="commandName == '허보'">
      전쟁중인 국가에 종전을 제의합니다.<br />
      제의할 국가를 목록에서 선택하세요.<br />
      현재 제의가 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div class="row">
      <div class="col-6 col-md-3">
        국가 :
        <SelectNation v-model="selectedNationID" :nations="nationList" :searchable="searchable" />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" :type="procEntryMode" />
</template>

<script lang="ts">
import MapLegacyTemplate, { type MapCityParsed } from "@/components/MapLegacyTemplate.vue";
import SelectNation from "@/processing/SelectNation.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { getProcSearchable, type procNationItem, type procNationList } from "./processingRes";

declare const staticValues: {
  mapName: string;
  commandName: string;
  entryInfo: ["General" | "Nation", unknown];
};
declare const procRes: {
  nationList: procNationList;
  startYear: number;
};

export default defineComponent({
  components: {
    MapLegacyTemplate,
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
    const selectedCityObj = ref(); //mapping용

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
      procEntryMode: (staticValues.entryInfo[0] == "Nation" ? "chief" : "normal") as 'chief'|'normal',
      searchable: getProcSearchable(),
      startYear: procRes.startYear,
      mapName: staticValues.mapName,
      nationList: ref(nationList),
      selectedCityObj,
      selectedNationID,
      commandName: staticValues.commandName,
      selectedNation,
      submit,
    };
  },
  watch: {
    selectedCityObj(city: MapCityParsed) {
      if (!city.nationID) {
        return;
      }
      this.selectedNationID = city.nationID;
    },
  },
});
</script>
