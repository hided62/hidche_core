import { createApp } from 'vue'
import PageJoin from '@/PageJoin.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import { auto500px } from './util/auto500px';

auto500px();

createApp(PageJoin).use(BootstrapVue3).mount('#app')