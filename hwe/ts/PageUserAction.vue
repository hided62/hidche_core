<template>
    <BContainer id="container" ref="container" :toast="{ root: true }">
        <TopBackBar :reloadable="true" title="개인 전략" @reload="refresh" />
        <div v-if="asyncReady && gameConstStore && generalInfo && frontInfo && globalInfo && nationStaticInfo" id="pages"
            class="bg0">
            <div id="leftPanel">
                <GeneralBasicCard :general="generalInfo" :nation="nationStaticInfo" :troopInfo="frontInfo.general.troopInfo"
                    :turnTerm="globalInfo.turnterm" :lastExecuted="lastExecuted" />
                <div class="bg1" style="margin-top: 10px;">
                    대기 중인 전략
                </div>
                <div v-for="[command, turn] of frontInfo.general.impossibleUserAction" :key="command">
                    <span>{{ command }}</span>: {{ turn.toLocaleString() }}턴 뒤
                </div>
            </div>
            <div id="actionForm">
                <ReservedCommandForUserAction ref="reservedCommandPanel" />
            </div>
        </div>
    </BContainer>
</template>
<script lang="ts">
declare const staticValues: {
    serverName: string;
    serverNick: string;
    serverID: string;
    mapName: string;
    unitSet: string;
};
</script>
<script setup lang="ts">
import ReservedCommandForUserAction from './ReservedCommandForUserAction.vue';
import { GameConstStore, getGameConstStore } from "./GameConstStore";
import { useToast } from 'bootstrap-vue-next';
import { unwrap } from './util/unwrap';
import { onMounted, provide, ref, watch } from 'vue';
import TopBackBar from './components/TopBackBar.vue';
import { SammoAPI } from './SammoAPI';
import { delay } from './util/delay';
import GeneralBasicCard from './components/GeneralBasicCard.vue';
import { GetFrontInfoResponse } from './defs/API/Global';
import { NationStaticItem } from './defs';
import { parseTime } from './util/parseTime';

const { serverID } = staticValues;

const toasts = unwrap(useToast());

const lastExecuted = ref<Date>(parseTime("2022-08-15 00:00:00"));

const asyncReady = ref(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
    gameConstStore.value = store;
});

const frontInfo = ref<GetFrontInfoResponse>();
const globalInfo = ref<GetFrontInfoResponse["global"]>({} as GetFrontInfoResponse["global"]);
const generalInfo = ref<GetFrontInfoResponse["general"]>();
const nationColorClass = ref('sam-color-000000');
const nationStaticInfo = ref<NationStaticItem>();

watch(nationStaticInfo, (newNation) => {
    if (!newNation) {
        nationColorClass.value = 'sam-color-000000';
        return;
    }
    nationColorClass.value = `sam-color-${newNation.color.substring(1, 7)}`;
});

void Promise.all([storeP]).then(() => {
    asyncReady.value = true;
});

const reservedCommandPanel = ref<InstanceType<typeof ReservedCommandForUserAction> | null>(null);

async function refresh() {
    const serverExecuteP = SammoAPI.Global.ExecuteEngine({ serverID }, true).then((response) => {
        if (response.result) {
            lastExecuted.value = parseTime(response.lastExecuted);
        }
        return response;
    });
    await Promise.race([delay(1000), serverExecuteP]);
    reservedCommandPanel.value?.reloadCommandList();
    reservedCommandPanel.value?.updateCommandTable();
    const frontResponse = await SammoAPI.General.GetFrontInfo({
        lastNationNoticeDate: "9999-12-31 23:59:59",
        lastGeneralRecordID: 99999999,
        lastWorldHistoryID: 99999999,
    });

    frontInfo.value = frontResponse;
    generalInfo.value = frontResponse.general;

    const rawNation = frontResponse.nation;
    nationStaticInfo.value = {
        nation: rawNation.id,
        name: rawNation.name,
        color: rawNation.color,
        type: rawNation.type.raw,
        level: rawNation.level,
        capital: rawNation.capital,
        gennum: rawNation.gennum,
        power: rawNation.power
    };
}

onMounted(() => {
    void refresh();
});

</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

//grid
@include media-1000px {
    #pages {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
}

@include media-500px {
    #pages {
        display: grid;
        grid-template-columns: 1fr;
    }
}
</style>