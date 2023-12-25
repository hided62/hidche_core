<template>
    <BContainer id="container" ref="container" :toast="{ root: true }">
        <TopBackBar :reloadable="true" title="개인 전략" @reload="refresh" />
        <div v-if="asyncReady && gameConstStore" id="pages" class="bg0">
            <div id="commandList">

            </div>
            <div id="actionForm">
                <ReservedCommandForUserAction />
            </div>
        </div>


    </BContainer>
</template>

<script setup lang="ts">
import ReservedCommandForUserAction from './ReservedCommandForUserAction.vue';
import { GameConstStore, getGameConstStore } from "./GameConstStore";
import { useToast } from 'bootstrap-vue-next';
import { unwrap } from './util/unwrap';
import { provide, ref } from 'vue';
import TopBackBar from './components/TopBackBar.vue';

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

async function refresh() {
    console.log('갱신');
}

</script>

<style lang="scss" scoped>
//grid
#pages {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-gap: 10px;
}
</style>