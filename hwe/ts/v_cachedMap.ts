import { createApp } from 'vue'
import PageCachedMap from '@/PageCachedMap.vue';
import { BootstrapVueNext, BToastPlugin } from 'bootstrap-vue-next'
import { auto500px } from "./util/auto500px";

auto500px();

createApp(PageCachedMap).use(BootstrapVueNext).use(BToastPlugin).mount('#app');