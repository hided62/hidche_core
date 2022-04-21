import { createApp } from 'vue'
import PageCachedMap from '@/PageCachedMap.vue';
import { BootstrapVue3, BToastPlugin } from 'bootstrap-vue-3'
import { auto500px } from "./util/auto500px";

auto500px();

createApp(PageCachedMap).use(BootstrapVue3).use(BToastPlugin).mount('#app');