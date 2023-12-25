<template>
    <BContainer id="container" ref="container" :toast="{ root: true }">
        <TopBackBar :reloadable="true" title="개인 전략" @reload="refresh" />
        <div v-if="asyncReady && gameConstStore" id="pages" class="bg0">
            <div id="commandList">

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
import { provide, ref } from 'vue';
import TopBackBar from './components/TopBackBar.vue';
import { SammoAPI } from './SammoAPI';
import { delay } from './util/delay';

const { serverID } = staticValues;

const toasts = unwrap(useToast());

const asyncReady = ref(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
    gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
    asyncReady.value = true;
});

const reservedCommandPanel = ref<InstanceType<typeof ReservedCommandForUserAction> | null>(null);

async function refresh() {
    const serverExecuteP = SammoAPI.Global.ExecuteEngine({ serverID }, true);
    await Promise.race([delay(1000), serverExecuteP]);
    reservedCommandPanel.value?.reloadCommandList();
    reservedCommandPanel.value?.updateCommandTable();
}

</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

//grid
@include media-1000px {
    #pages {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-gap: 10px;
    }
}

@include media-500px {
    #pages {
        display: grid;
        grid-template-columns: 1fr;
        grid-gap: 10px;
    }
}
</style>